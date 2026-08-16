<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\UserRecharge;
use App\Services\Hotspot\HotspotDeviceResolver;
use App\Services\NotificationService;
use App\Services\NotificationTemplateService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

#[Signature('app:check-expired-plans')]
#[Description('Auto-disable expired plans on the device and send expiry reminders')]
class CheckExpiredPlans extends Command
{
    public function handle(
        HotspotDeviceResolver $devices,
        NotificationService $notifications,
        NotificationTemplateService $templates,
    ): void {
        $this->disableExpired($devices, $notifications, $templates);
        $this->sendReminders($notifications, $templates);
    }

    private function disableExpired(
        HotspotDeviceResolver $devices,
        NotificationService $notifications,
        NotificationTemplateService $templates,
    ): void {
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

            if ($templates->isEnabled('expired')
                && $customer->exists
                && ! empty($customer->phonenumber)
                && strlen($customer->phonenumber) > 5) {
                $notifications->sendWhatsapp(
                    $customer->phonenumber,
                    $templates->render('expired', [
                        'customer_name' => $customer->fullname ?? $customer->username,
                        'username' => $customer->username,
                        'plan' => $recharge->namebp,
                        'expired_at' => $recharge->expiration->toDateString(),
                    ])
                );
            }

            $this->info("Disabled {$recharge->username} (plan expired {$recharge->expiration->toDateString()})");
        }
    }

    private function sendReminders(NotificationService $notifications, NotificationTemplateService $templates): void
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

            if (! $templates->isEnabled('reminder')) {
                continue;
            }

            $notifications->sendWhatsapp(
                $customer->phonenumber,
                $templates->render('reminder', [
                    'customer_name' => $customer->fullname ?? $customer->username,
                    'username' => $customer->username,
                    'plan' => $recharge->namebp,
                    'days_left' => (string) $daysLeft,
                    'expired_at' => $expirationDate,
                ])
            );
        }
    }
}
