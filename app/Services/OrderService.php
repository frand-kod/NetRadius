<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Exceptions\ActivePlanStillActiveException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(private readonly RechargeService $rechargeService) {}

    public function create(Customer $customer, Plan $plan, ?int $adminId = null): Order
    {
        $order = Order::create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'price' => $plan->price,
            'status' => 'pending',
            'invoice_token' => Str::random(32),
            'admin_id' => $adminId ?? 0,
        ]);

        OrderCreated::dispatch($order);

        return $order;
    }

    /**
     * @throws ActivePlanStillActiveException
     */
    public function markAsPaid(Order $order, ?int $adminId = null): Order
    {
        $this->rechargeService->recharge(
            $order->customer,
            $order->plan,
            'QR Payment',
            $order->invoice_token,
            $adminId,
        );

        $order->update(['status' => 'paid', 'paid_at' => now()]);

        ActivityLogger::log('order-paid', "Order #{$order->id} marked as paid", $adminId);

        return $order;
    }

    public function cancel(Order $order, ?int $adminId = null): Order
    {
        $order->update(['status' => 'cancelled']);

        ActivityLogger::log('order-cancel', "Order #{$order->id} cancelled", $adminId);

        return $order;
    }
}
