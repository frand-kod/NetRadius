<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @return Collection<int, object{date: string, total: float, count: int}>
     */
    public function incomeByDay(Carbon $from, Carbon $to): Collection
    {
        return Transaction::query()
            ->selectRaw('recharged_on as date, SUM(price) as total, COUNT(*) as count')
            ->whereBetween('recharged_on', [$from->toDateString(), $to->toDateString()])
            ->groupBy('recharged_on')
            ->orderBy('recharged_on')
            ->get();
    }

    public function totalIncome(Carbon $from, Carbon $to): float
    {
        return (float) Transaction::query()
            ->whereBetween('recharged_on', [$from->toDateString(), $to->toDateString()])
            ->sum('price');
    }
}
