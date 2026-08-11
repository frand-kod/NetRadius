<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\NotificationService;

class SendOrderCreatedNotification
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order->loadMissing('customer', 'plan');
        $invoiceUrl = route('invoice.show', $order->invoice_token);
        $price = number_format((float) $order->price, 0, ',', '.');

        if (! empty($order->customer->phonenumber) && strlen($order->customer->phonenumber) > 5) {
            $this->notifications->sendWhatsapp(
                $order->customer->phonenumber,
                "Order baru dibuat untuk paket {$order->plan->name_plan} (Rp{$price}).\nSilakan lakukan pembayaran dan lihat invoice di:\n{$invoiceUrl}"
            );
        }

        $this->notifications->sendTelegram(
            "#order_baru\nCustomer: {$order->customer->fullname} ({$order->customer->username})\nPaket: {$order->plan->name_plan}\nHarga: Rp{$price}\nInvoice: {$invoiceUrl}"
        );
    }
}
