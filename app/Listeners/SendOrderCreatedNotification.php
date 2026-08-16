<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\NotificationService;
use App\Services\NotificationTemplateService;

class SendOrderCreatedNotification
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly NotificationTemplateService $templates,
    ) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order->loadMissing('customer', 'plan');
        $customer = $order->customer;

        $data = [
            'customer_name' => $customer->fullname ?? $customer->username ?? '',
            'username' => $customer->username ?? '',
            'plan' => $order->plan->name_plan ?? '',
            'price' => number_format((float) $order->price, 0, ',', '.'),
            'invoice_url' => route('invoice.show', $order->invoice_token),
        ];

        if ($this->templates->isEnabled('order_created')
            && ! empty($customer->phonenumber)
            && strlen($customer->phonenumber) > 5) {
            $this->notifications->sendWhatsapp(
                $customer->phonenumber,
                $this->templates->render('order_created', $data)
            );
        }

        if ($this->templates->isEnabled('order_created_admin')) {
            $this->notifications->sendTelegram(
                $this->templates->render('order_created_admin', $data)
            );
        }
    }
}
