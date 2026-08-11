<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\UserRecharge;
use App\Services\Hotspot\HotspotDeviceResolver;
use App\Services\NotificationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

#[Signature('app:check-expired-plans')]
#[Description('Auto-disable expired plans on the device and send expiry reminders')]
class CheckExpiredPlans extends Command
{
    public function handle(HotspotDeviceResolver $devices, NotificationService $notifications): void
    {
        $this->disableExpired($devices, $notifications);
        $this->sendReminders($notifications);
    }

    private function disableExpired(HotspotDeviceResolver $devices, NotificationService $notifications): void
    {
        $expired = UserRecharge::query()
            ->where('status', 'on')
            ->whereDate('expiration', '<=', Carbon::today())
            ->get();

        foreach ($expired as $recharge) {
            $expiresAt = Carbon::parse($recharge->expiration->toDateString().' '.$recharge->time);
            if ($expiresAt->isFuture()) {
                continue;
            }

            $plan = Plan::find($recharge->plan_id);
            if (! $plan) {
                $this->error("Plan not found for UserRecharge #{$recharge->id}");

                continue;
            }

            $customer = Customer::query()->where('id', $recharge->customer_id)->first()
                ?? new Customer(['username' => $recharge->username, 'fullname' => $recharge->username, 'password' => '']);

            try {
                $devices->resolve($plan)->removeCustomer($customer, $plan);
            } catch (Throwable $e) {
                report($e);
                $this->error("Failed to disable {$recharge->username} on device: {$e->getMessage()}");
            }

            $recharge->update(['status' => 'off']);

            if ($customer->exists && ! empty($customer->phonenumber) && strlen($customer->phonenumber) > 5) {
                $notifications->sendWhatsapp(
                    $customer->phonenumber,
                    "Paket {$recharge->namebp} Anda sudah expired pada {$recharge->expiration->toDateString()}. Silakan hubungi admin untuk perpanjangan."
                );
            }

            $this->info("Disabled {$recharge->username} (plan expired {$recharge->expiration->toDateString()})");
        }
    }

    private function sendReminders(NotificationService $notifications): void
    {
        $day1 = Carbon::today()->addDay()->toDateString();
        $day3 = Carbon::today()->addDays(3)->toDateString();
        $day7 = Carbon::today()->addDays(7)->toDateString();

        $recharges = UserRecharge::query()
            ->where('status', 'on')
            ->where('customer_id', '!=', 0)
            ->whereIn('expiration', [$day1, $day3, $day7])
            ->get();

        foreach ($recharges as $recharge) {
            $customer = Customer::query()->where('id', $recharge->customer_id)->first();
            if (! $customer || empty($customer->phonenumber) || strlen($customer->phonenumber) <= 5) {
                continue;
            }

            $expirationDate = $recharge->expiration->toDateString();
            $daysLeft = match ($expirationDate) {
                $day1 => 1,
                $day3 => 3,
                $day7 => 7,
                default => null,
            };

            if ($daysLeft === null) {
                continue;
            }

            $notifications->sendWhatsapp(
                $customer->phonenumber,
                "Pengingat: paket {$recharge->namebp} Anda akan expired dalam {$daysLeft} hari ({$expirationDate})."
            );
        }
    }
}
