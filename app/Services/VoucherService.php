<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Voucher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VoucherService
{
    /**
     * @return Collection<int, Voucher>
     */
    public function generate(Plan $plan, int $quantity, int $codeLength = 8, ?int $adminId = null): Collection
    {
        $vouchers = collect(range(1, $quantity))->map(function () use ($plan, $codeLength) {
            return Voucher::create([
                'type' => 'Hotspot',
                'id_plan' => $plan->id,
                'code' => $this->uniqueCode($codeLength),
                'status' => '0',
            ]);
        });

        ActivityLogger::log('generate', "{$vouchers->count()} voucher(s) generated for plan [{$plan->name_plan}]", $adminId);

        return $vouchers;
    }

    private function uniqueCode(int $length): string
    {
        do {
            $code = Str::upper(Str::random($length));
        } while (Voucher::query()->where('code', $code)->exists());

        return $code;
    }
}
