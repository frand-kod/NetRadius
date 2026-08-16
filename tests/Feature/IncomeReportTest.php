<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class IncomeReportTest extends TestCase
{
    use RefreshDatabase;

    private function makeTransaction(string $date, string $price): Transaction
    {
        return Transaction::create([
            'invoice' => 'INV-'.uniqid(),
            'username' => 'user1',
            'user_id' => 1,
            'plan_name' => 'Test Plan',
            'price' => $price,
            'recharged_on' => $date,
            'recharged_time' => '10:00:00',
            'expiration' => now()->addDays(30)->toDateString(),
            'time' => '10:00:00',
            'method' => 'QR Payment - manual',
            'type' => 'Hotspot',
            'admin_id' => 1,
        ]);
    }

    public function test_income_by_day_sums_transactions_within_range_and_excludes_outside(): void
    {
        $this->makeTransaction('2026-01-05', '50000');
        $this->makeTransaction('2026-01-05', '25000');
        $this->makeTransaction('2026-01-10', '100000');
        $this->makeTransaction('2026-02-01', '999999');

        $rows = app(ReportService::class)->incomeByDay(Carbon::parse('2026-01-01'), Carbon::parse('2026-01-31'));

        $byDate = $rows->keyBy('date');
        $this->assertEquals(75000, (float) $byDate['2026-01-05']->total);
        $this->assertEquals(2, $byDate['2026-01-05']->count);
        $this->assertEquals(100000, (float) $byDate['2026-01-10']->total);
        $this->assertFalse($byDate->has('2026-02-01'));
    }

    public function test_total_income_sums_only_within_range(): void
    {
        $this->makeTransaction('2026-01-05', '50000');
        $this->makeTransaction('2026-01-10', '100000');
        $this->makeTransaction('2026-02-01', '999999');

        $total = app(ReportService::class)->totalIncome(Carbon::parse('2026-01-01'), Carbon::parse('2026-01-31'));

        $this->assertEquals(150000.0, $total);
    }
}
