<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\UserRecharge;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Persentase perubahan (current vs previous). Mengembalikan null bila
     * `previous` nol/negatif (tidak bisa dijadikan pembanding) — bukan 0/error.
     */
    private function change(int $current, int $previous): ?int
    {
        if ($previous <= 0) {
            return null;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }

    public function show(): Response
    {
        $unusedVouchers = Voucher::where('status', '0')->count();
        $usedVouchers = Voucher::where('status', '!=', '0')->count();

        ['onlineUsers' => $onlineUsers, 'usage' => $usage] = $this->usageStats();

        // --- Customer baru per bulan (12 bulan terakhir) — 1 query GROUP BY ---
        $monthlyCustomers = Customer::where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("strftime('%Y-%m', created_at) as ym")
            ->selectRaw('count(*) as total')
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $customerTrend = collect(range(0, 11))->map(function (int $i) use ($monthlyCustomers) {
            $m = now()->subMonths(11 - $i);

            return [
                'label' => $m->format('M'),
                'value' => (int) ($monthlyCustomers[$m->format('Y-m')] ?? 0),
            ];
        });

        // --- Pendapatan per hari (30 hari terakhir) — 1 query GROUP BY ---
        $dailyIncome = Transaction::where('recharged_on', '>=', now()->subDays(29)->toDateString())
            ->selectRaw("strftime('%Y-%m-%d', recharged_on) as d")
            ->selectRaw('coalesce(sum(price), 0) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $incomeTrend = collect(range(0, 29))->map(function (int $i) use ($dailyIncome) {
            $d = now()->subDays(29 - $i);

            return [
                'label' => $d->format('d/m'),
                'value' => (float) ($dailyIncome[$d->toDateString()] ?? 0),
            ];
        });

        // --- Distribusi status customer — 1 query GROUP BY ---
        $statuses = ['Active', 'Banned', 'Disabled', 'Inactive', 'Limited', 'Suspended'];
        $statusCounts = Customer::select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $customerStatus = collect($statuses)
            ->map(fn (string $s) => ['label' => $s, 'value' => (int) ($statusCounts[$s] ?? 0)])
            ->filter(fn (array $s) => $s['value'] > 0)
            ->values();

        // --- Perbandingan: customer (hari/minggu/bulan ini vs periode sebelumnya) ---
        $now = now();
        $customerIntervals = [
            'today' => ['label' => 'Hari Ini', 'start' => $now->copy()->startOfDay(), 'prevStart' => $now->copy()->subDay()->startOfDay(), 'prevEnd' => $now->copy()->startOfDay()],
            'week' => ['label' => 'Minggu Ini', 'start' => $now->copy()->startOfWeek(), 'prevStart' => $now->copy()->subWeek()->startOfWeek(), 'prevEnd' => $now->copy()->startOfWeek()],
            'month' => ['label' => 'Bulan Ini', 'start' => $now->copy()->startOfMonth(), 'prevStart' => $now->copy()->subMonth()->startOfMonth(), 'prevEnd' => $now->copy()->startOfMonth()],
        ];

        $customerComparison = collect($customerIntervals)->map(function (array $int) use ($now) {
            $current = Customer::whereBetween('created_at', [$int['start'], $now])->count();
            $previous = Customer::whereBetween('created_at', [$int['prevStart'], $int['prevEnd']])->count();

            return [
                'key' => $int['label'],
                'pct' => $this->change($current, $previous),
            ];
        });

        // --- Perbandingan: aktivitas rad_acct (5/10/15/60 menit lalu vs sebelumnya) ---
        // Satu query agregat untuk 120 menit terakhir, diagregasi di PHP per interval
        // sehingga tidak ada N+1.
        $usageRows = DB::table('rad_acct')
            ->where('dateAdded', '>=', $now->copy()->subMinutes(120))
            ->get(['dateAdded', 'acctoutputoctets', 'acctinputoctets']);

        $usageComparison = collect([5, 10, 15, 60])->map(function (int $minutes) use ($usageRows, $now) {
            $curStart = $now->copy()->subMinutes($minutes);
            $prevStart = $now->copy()->subMinutes($minutes * 2);

            $cur = $usageRows->filter(fn ($r) => Carbon::parse($r->dateAdded) >= $curStart);
            $prev = $usageRows->filter(fn ($r) => Carbon::parse($r->dateAdded) >= $prevStart && Carbon::parse($r->dateAdded) < $curStart);

            return [
                'key' => $minutes,
                'label' => $minutes.' menit',
                'sessions' => $this->change($cur->count(), $prev->count()),
                'down' => $this->change((int) $cur->sum('acctoutputoctets'), (int) $prev->sum('acctoutputoctets')),
                'up' => $this->change((int) $cur->sum('acctinputoctets'), (int) $prev->sum('acctinputoctets')),
            ];
        })->values();

        // --- Top paket terlaris (30 hari terakhir) ---
        $topPlans = Transaction::select('plan_name')
            ->whereDate('recharged_on', '>=', now()->subDays(30)->toDateString())
            ->selectRaw('COUNT(*) as total')
            ->groupBy('plan_name')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn ($t) => ['name' => $t->plan_name, 'count' => (int) $t->total])
            ->values();

        // --- Aktivitas terbaru: gabungan customer baru, voucher dipakai, pembayaran ---
        // 3 query agregat kecil (masing-masing take 10), digabung & disort di PHP.
        $recentActivities = collect()
            ->merge(Customer::latest('created_at')->take(10)->get(['username', 'created_at'])
                ->map(fn ($c) => ['type' => 'customer', 'text' => "Customer baru: {$c->username}", 'ts' => Carbon::parse($c->created_at)->timestamp]))
            ->merge(Voucher::whereNotNull('used_date')->latest('used_date')->take(10)->get(['code', 'used_date'])
                ->map(fn ($v) => ['type' => 'voucher', 'text' => "Voucher dipakai: {$v->code}", 'ts' => Carbon::parse($v->used_date)->timestamp]))
            ->merge(Transaction::latest('recharged_on')->take(10)->get(['plan_name', 'recharged_on'])
                ->map(fn ($t) => ['type' => 'payment', 'text' => "Pembayaran: {$t->plan_name}", 'ts' => Carbon::parse($t->recharged_on)->timestamp]))
            ->filter(fn (array $a) => $a['ts'] !== null)
            ->sortByDesc('ts')
            ->take(5)
            ->values()
            ->map(fn (array $a) => ['type' => $a['type'], 'text' => $a['text'], 'at' => Carbon::createFromTimestamp($a['ts'])->diffForHumans()]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalCustomers' => Customer::count(),
                'onlineUsers' => $usage['activeSessions'],
                'comparison' => [
                    'customers' => $customerComparison,
                    'usage' => $usageComparison,
                ],
            ],
            'customerTrend' => $customerTrend,
            'incomeTrend' => $incomeTrend,
            'customerStatus' => $customerStatus,
            'topPlans' => $topPlans,
            'voucherUsage' => [
                ['label' => 'Belum Dipakai', 'value' => $unusedVouchers, 'color' => '#f59e0b'],
                ['label' => 'Terpakai', 'value' => $usedVouchers, 'color' => '#16a34a'],
            ],
            'onlineUsers' => $onlineUsers,
            'usage' => $usage,
            'expiring' => $this->expiringUsers(),
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Data real-time ringan untuk polling dashboard (tanpa memuat ulang seluruh
     * halaman). Hanya usage, online users, dan daftar akan-expired.
     *
     * @return array{usage: array, onlineUsers: Collection, expiring: Collection}
     */
    public function realtime(): JsonResponse
    {
        ['onlineUsers' => $onlineUsers, 'usage' => $usage] = $this->usageStats();

        return response()->json([
            'usage' => $usage,
            'onlineUsers' => $onlineUsers,
            'expiring' => $this->expiringUsers(),
        ]);
    }

    /**
     * Statistik sesi aktif & pemakaian dari rad_acct. Satu query untuk username
     * aktif + satu query per sesi (di-loop), dibatasi hanya sesi aktif.
     *
     * @return array{onlineUsers: Collection, usage: array}
     */
    private function usageStats(): array
    {
        $activeUsernames = DB::table('rad_acct as r1')
            ->where('r1.acctstatustype', 'Start')
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')->from('rad_acct as r2')
                    ->whereColumn('r2.username', 'r1.username')
                    ->where('r2.acctstatustype', 'Stop');
            })
            ->distinct()
            ->pluck('r1.username');

        $onlineUsers = collect();
        $totalDown = 0;
        $totalUp = 0;
        $totalTime = 0;

        if ($activeUsernames->isNotEmpty()) {
            // Satu query ambil semua baris sesi aktif, lalu ambil baris terbaru per
            // user di PHP — menghindari N+1 (`per username → orderByDesc id`).
            $latestByUser = DB::table('rad_acct')
                ->whereIn('username', $activeUsernames)
                ->orderByDesc('id')
                ->get()
                ->groupBy('username')
                ->map(fn ($group) => $group->first());

            foreach ($latestByUser as $row) {
                $down = (int) ($row->acctinputoctets ?? 0);
                $up = (int) ($row->acctoutputoctets ?? 0);
                $time = (int) ($row->acctsessiontime ?? 0);
                $totalDown += $down;
                $totalUp += $up;
                $totalTime += $time;

                $onlineUsers->push((object) [
                    'username' => $row->username,
                    'framedipaddress' => $row->framedipaddress,
                    'macaddr' => $row->macaddr,
                    'dateAdded' => $row->dateAdded,
                    'down' => $down,
                    'up' => $up,
                    'volume' => $down + $up,
                    'time' => $time,
                ]);
            }
        }

        $onlineUsers = $onlineUsers->sortByDesc('volume')->take(10)->values();
        $onlineCount = $activeUsernames->count();
        $totalVolume = $totalDown + $totalUp;

        return [
            'onlineUsers' => $onlineUsers,
            'usage' => [
                'activeSessions' => $onlineCount,
                'totalDown' => $totalDown,
                'totalUp' => $totalUp,
                'totalVolume' => $totalVolume,
                // Rata-rata kecepatan (bps) seluruh sesi aktif.
                'avgSpeed' => $totalTime > 0 ? (int) ($totalVolume * 8 / $totalTime) : 0,
            ],
        ];
    }

    /**
     * Paket aktif (UserRecharge) yang masa berlakunya habis dalam 7 hari ke
     * depan, diurutkan dari yang paling dekat. Menggunakan eager load `plan`.
     */
    private function expiringUsers(): Collection
    {
        return UserRecharge::with('plan')
            ->where('status', 'on')
            ->whereBetween('expiration', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('expiration')
            ->orderBy('time')
            ->take(10)
            ->get()
            ->map(fn (UserRecharge $r) => [
                'username' => $r->username,
                'plan' => $r->namebp ?: $r->plan?->name_plan,
                'expires_at' => $r->expiration->toDateString(),
                'days_left' => now()->startOfDay()->diffInDays(Carbon::parse($r->expiration)),
            ])
            ->values();
    }
}
