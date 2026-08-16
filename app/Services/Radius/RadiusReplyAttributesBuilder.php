<?php

namespace App\Services\Radius;

use App\Exceptions\RadiusRejectedException;
use App\Models\Bandwidth;
use App\Models\Plan;
use App\Models\UserRecharge;
use Illuminate\Support\Facades\DB;

class RadiusReplyAttributesBuilder
{
    /**
     * Port of `process_radiust_rest()` from the legacy `../radius.php`.
     *
     * @return array<string, mixed>
     */
    public function build(UserRecharge $recharge, ?string $framedIpAddress): array
    {
        $plan = Plan::findOrFail($recharge->plan_id);
        $bandwidth = Bandwidth::find($plan->id_bw);

        $onlineSessions = DB::table('rad_acct')
            ->where('username', $recharge->username)
            ->where('acctstatustype', 'Start')
            ->get();

        $ips = $onlineSessions->pluck('framedipaddress')->all();
        if ($onlineSessions->count() >= $plan->shared_users && $plan->type === 'Hotspot' && ! in_array($framedIpAddress, $ips, true)) {
            throw new RadiusRejectedException('You are already logged in - access denied', 401, ['control:Auth-Type' => 'Accept']);
        }

        $unitDown = $bandwidth?->rate_down_unit === 'Kbps' ? 'K' : 'M';
        $unitUp = $bandwidth?->rate_up_unit === 'Kbps' ? 'K' : 'M';
        $rateUp = ($bandwidth->rate_up ?? 0).$unitUp;
        $rateDown = ($bandwidth->rate_down ?? 0).$unitDown;
        // Mikrotik-Rate-Limit: rx/tx (rx=download, tx=upload) sesuai dokumentasi Mikrotik.
        $rate = "{$rateDown}/{$rateUp}";
        $burst = trim((string) $bandwidth?->burst);
        $rateLimit = $burst !== '' ? "{$rate} {$burst}" : $rate;

        $expiresAt = strtotime($recharge->expiration->toDateString().' '.$recharge->time);
        if ($expiresAt < time()) {
            throw new RadiusRejectedException(
                "Sorry, your account's active period has expired ({$recharge->expiration->toDateString()})"
            );
        }

        $attrs = [
            'control:Auth-Type' => 'Accept',
            'reply:Reply-Message' => 'success',
            'Simultaneous-Use' => $plan->shared_users,
            'reply:Mikrotik-Wireless-Comment' => "{$plan->name_plan} | {$recharge->expiration->toDateString()} {$recharge->time}",
            'reply:Ascend-Data-Rate' => $this->toBps($rateDown),
            'reply:Ascend-Xmit-Rate' => $this->toBps($rateUp),
            'reply:Mikrotik-Rate-Limit' => $rateLimit,
            'reply:WISPr-Bandwidth-Max-Up' => $this->toBps($rateUp),
            'reply:WISPr-Bandwidth-Max-Down' => $this->toBps($rateDown),
            'reply:expiration' => date('d M Y H:i:s', $expiresAt),
            'reply:WISPr-Session-Terminate-Time' => date('Y-m-d', $expiresAt).'T'.date('H:i:sP', $expiresAt),
        ];

        if ($plan->typebp === 'Limited') {
            $attrs = array_merge($attrs, $this->limitAttributes($plan, $recharge));
        }

        return $attrs;
    }

    /**
     * @return array<string, mixed>
     */
    private function limitAttributes(Plan $plan, UserRecharge $recharge): array
    {
        $attrs = [];

        if (in_array($plan->limit_type, ['Data_Limit', 'Both_Limit'], true)) {
            $active = DB::table('rad_acct')
                ->where('username', $recharge->username)
                ->where('acctstatustype', 'Start')
                ->first();

            $totalUsage = (int) ($active->acctoutputoctets ?? 0) + (int) ($active->acctinputoctets ?? 0);
            $totalLimit = $this->convertDataUnit((int) $plan->data_limit, (string) $plan->data_unit) - $totalUsage;

            if ($totalLimit < 0) {
                throw new RadiusRejectedException('You have exceeded your data limit.', 401, ['control:Auth-Type' => 'Accept']);
            }
        }

        if ($plan->limit_type === 'Time_Limit') {
            $timeLimit = $plan->time_unit === 'Hrs' ? $plan->time_limit * 60 * 60 : $plan->time_limit * 60;
            $attrs['reply:Max-All-Session'] = $timeLimit;
            $attrs['reply:Expire-After'] = $timeLimit;
        } elseif ($plan->limit_type === 'Data_Limit') {
            $dataLimit = $plan->data_unit === 'GB' ? $plan->data_limit.'000000000' : $plan->data_limit.'000000';
            $attrs['reply:Max-Data'] = $dataLimit;
            $attrs['reply:Mikrotik-Recv-Limit-Gigawords'] = $dataLimit;
            $attrs['reply:Mikrotik-Xmit-Limit-Gigawords'] = $dataLimit;
        } elseif ($plan->limit_type === 'Both_Limit') {
            $timeLimit = $plan->time_unit === 'Hrs' ? $plan->time_limit * 60 * 60 : $plan->time_limit * 60;
            $dataLimit = $plan->data_unit === 'GB' ? $plan->data_limit.'000000000' : $plan->data_limit.'000000';
            $attrs['reply:Max-All-Session'] = $timeLimit;
            $attrs['reply:Max-Data'] = $dataLimit;
            $attrs['reply:Mikrotik-Recv-Limit-Gigawords'] = $dataLimit;
            $attrs['reply:Mikrotik-Xmit-Limit-Gigawords'] = $dataLimit;
        }

        return $attrs;
    }

    private function toBps(string $rate): string
    {
        return str_replace('M', '000000', str_replace('K', '000', $rate));
    }

    private function convertDataUnit(int $amount, string $unit): int
    {
        return $unit === 'GB' ? $amount * 1_000_000_000 : $amount * 1_000_000;
    }
}
