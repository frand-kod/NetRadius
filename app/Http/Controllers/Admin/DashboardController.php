<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function show(): Response
    {
        $unusedVouchers = Voucher::where('status', '0')->count();
        $usedVouchers = Voucher::where('status', '!=', '0')->count();

        // --- Sesi RADIUS aktif & statistik penggunaan dari rad_acct ---
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

        foreach ($activeUsernames as $username) {
            $row = DB::table('rad_acct')->where('username', $username)->orderByDesc('id')->first();
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

        $onlineUsers = $onlineUsers->sortByDesc('volume')->take(10)->values();

        $onlineCount = $activeUsernames->count();
        $totalVolume = $totalDown + $totalUp;
        $usage = [
            'activeSessions' => $onlineCount,
            'totalDown' => $totalDown,
            'totalUp' => $totalUp,
            'totalVolume' => $totalVolume,
            // Rata-rata kecepatan (bps) seluruh sesi aktif.
            'avgSpeed' => $totalTime > 0 ? (int) ($totalVolume * 8 / $totalTime) : 0,
        ];

        // --- Customer baru per bulan (12 bulan terakhir) ---
        $customerTrend = collect(range(0, 11))->map(function (int $i) {
            $m = now()->subMonths(11 - $i);

            return [
                'label' => $m->format('M'),
                'value' => Customer::whereYear('created_at', $m->year)
                    ->whereMonth('created_at', $m->month)
                    ->count(),
            ];
        });

        // --- Pendapatan per hari (30 hari terakhir) ---
        $incomeTrend = collect(range(0, 29))->map(function (int $i) {
            $d = now()->subDays(29 - $i);

            return [
                'label' => $d->format('d/m'),
                'value' => (float) Transaction::whereDate('recharged_on', $d->toDateString())
                    ->sum('price'),
            ];
        });

        // --- Distribusi status customer ---
        $statuses = ['Active', 'Banned', 'Disabled', 'Inactive', 'Limited', 'Suspended'];
        $customerStatus = collect($statuses)
            ->map(fn (string $s) => ['label' => $s, 'value' => Customer::where('status', $s)->count()])
            ->filter(fn (array $s) => $s['value'] > 0)
            ->values();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalCustomers' => Customer::count(),
                'onlineUsers' => $onlineCount,
            ],
            'customerTrend' => $customerTrend,
            'incomeTrend' => $incomeTrend,
            'customerStatus' => $customerStatus,
            'voucherUsage' => [
                ['label' => 'Belum Dipakai', 'value' => $unusedVouchers, 'color' => '#f59e0b'],
                ['label' => 'Terpakai', 'value' => $usedVouchers, 'color' => '#16a34a'],
            ],
            'onlineUsers' => $onlineUsers,
            'usage' => $usage,
        ]);
    }
}
