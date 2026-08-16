<?php

namespace App\Listeners;

use App\Events\CustomerRecharged;
use App\Models\Customer;
use App\Services\NotificationService;
use App\Services\NotificationTemplateService;

class SendCustomerRechargedNotification
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly NotificationTemplateService $templates,
    ) {}

    public function handle(CustomerRecharged $event): void
    {
        $transaction = $event->transaction;
        $userRecharge = $event->userRecharge;
        $expiresAt = "{$userRecharge->expiration->toDateString()} {$userRecharge->time}";

        $customer = Customer::query()->where('username', $transaction->username)->first();

        $data = [
            'customer_name' => $customer->fullname ?? $transaction->username,
            'username' => $transaction->username,
            'plan' => $event->plan->name_plan ?? '',
            'action' => $event->isNew ? 'diaktifkan' : 'diperpanjang',
            'expires_at' => $expiresAt,
            'price' => number_format((float) $transaction->price, 0, ',', '.'),
            'method' => $transaction->method ?? '',
        ];

        if ($this->templates->isEnabled('recharge_success')
            && $customer
            && ! empty($customer->phonenumber)
            && strlen($customer->phonenumber) > 5) {
            $this->notifications->sendWhatsapp(
                $customer->phonenumber,
                $this->templates->render('recharge_success', $data)
            );
        }

        if ($this->templates->isEnabled('recharge_success_admin')) {
            $this->notifications->sendTelegram(
                $this->templates->render('recharge_success_admin', $data)
            );
        }
    }
}
