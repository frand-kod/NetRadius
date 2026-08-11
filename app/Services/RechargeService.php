<?php

namespace App\Services;

use App\Events\CustomerRecharged;
use App\Exceptions\ActivePlanStillActiveException;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\UserRecharge;
use App\Services\Hotspot\HotspotDeviceResolver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class RechargeService
{
    public function recharge(
        ?Customer $customer,
        Plan $plan,
        string $routerName,
        string $gateway,
        string $channel,
        ?int $adminId = null,
    ): Transaction {
        $isVoucher = $customer === null;

        $identityUsername = $isVoucher ? $channel : $customer->username;
        $identityFullname = $isVoucher ? $gateway : $customer->fullname;
        $customerId = $isVoucher ? 0 : $customer->id;

        $deviceCustomer = $isVoucher
            ? new Customer(['username' => $channel, 'password' => $channel, 'fullname' => $gateway, 'email' => ''])
            : $customer;

        $expiresAt = $this->calculateExpiry($plan);
        $expirationDate = $expiresAt->toDateString();
        $expirationTime = $expiresAt->toTimeString();
        $now = Carbon::now();

        $existingQuery = UserRecharge::query()
            ->where('routers', $routerName)
            ->where('type', $plan->type);

        $existing = $isVoucher
            ? $existingQuery->where('username', $identityUsername)->first()
            : $existingQuery->where('customer_id', $customerId)->first();

        if ($existing && ! $existing->isExpired()) {
            throw new ActivePlanStillActiveException($existing);
        }

        $recharge = $existing ?? new UserRecharge();
        $recharge->fill([
            'customer_id' => $customerId,
            'username' => $identityUsername,
            'plan_id' => $plan->id,
            'namebp' => $plan->name_plan,
            'recharged_on' => $now->toDateString(),
            'recharged_time' => $now->toTimeString(),
            'expiration' => $expirationDate,
            'time' => $expirationTime,
            'status' => 'on',
            'method' => "{$gateway} - {$channel}",
            'routers' => $routerName,
            'type' => $plan->type,
            'admin_id' => $adminId ?? 0,
        ]);
        $recharge->save();

        try {
            HotspotDeviceResolver::resolve($plan)->addCustomer($deviceCustomer, $plan);
        } catch (Throwable $e) {
            Log::error("Failed to sync customer [{$identityUsername}] to device for plan [{$plan->name_plan}]: {$e->getMessage()}", [
                'exception' => $e,
            ]);
        }

        $transaction = Transaction::create([
            'invoice' => 'INV-'.(Transaction::max('id') + 1),
            'username' => $identityUsername,
            'user_id' => $customerId,
            'plan_name' => $plan->name_plan,
            'price' => $gateway === 'Voucher' ? 0 : $plan->price,
            'recharged_on' => $now->toDateString(),
            'recharged_time' => $now->toTimeString(),
            'expiration' => $expirationDate,
            'time' => $expirationTime,
            'method' => "{$gateway} - {$channel}",
            'routers' => $routerName,
            'type' => $plan->type,
            'note' => '',
            'admin_id' => $adminId ?? 0,
        ]);

        CustomerRecharged::dispatch($transaction, $recharge, $plan, $existing === null);

        return $transaction;
    }

    private function calculateExpiry(Plan $plan): Carbon
    {
        return match ($plan->validity_unit) {
            'Months' => Carbon::now()->addMonths($plan->validity),
            'Days' => Carbon::now()->addDays($plan->validity),
            'Hrs' => Carbon::now()->addHours($plan->validity),
            'Mins' => Carbon::now()->addMinutes($plan->validity),
            default => throw new RuntimeException("Unsupported validity_unit [{$plan->validity_unit}] for plan [{$plan->id}]."),
        };
    }
}
