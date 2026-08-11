<?php

namespace App\Listeners;

use App\Events\CustomerRecharged;
use App\Models\Customer;
use App\Services\NotificationService;

class SendCustomerRechargedNotification
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(CustomerRecharged $event): void
    {
        $transaction = $event->transaction;
        $userRecharge = $event->userRecharge;
        $price = number_format((float) $transaction->price, 0, ',', '.');
        $expiresAt = "{$userRecharge->expiration->toDateString()} {$userRecharge->time}";
        $verb = $event->isNew ? 'diaktifkan' : 'diperpanjang';

        $customer = Customer::query()->where('username', $transaction->username)->first();
        if ($customer && ! empty($customer->phonenumber) && strlen($customer->phonenumber) > 5) {
            $this->notifications->sendWhatsapp(
                $customer->phonenumber,
                "Paket {$event->plan->name_plan} berhasil {$verb}.\nBerlaku sampai: {$expiresAt}"
            );
        }

        $this->notifications->sendTelegram(
            "#recharge\nUsername: {$transaction->username}\nPaket: {$event->plan->name_plan}\nHarga: Rp{$price}\nMetode: {$transaction->method}\nBerlaku sampai: {$expiresAt}"
        );
    }
}
