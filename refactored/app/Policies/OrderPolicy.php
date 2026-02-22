<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Check if user can update order details (edit quantity).
     * Fixes IDOR vulnerability from legacy edit_order_action.php
     */
    public function update(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    /**
     * Check if user can save the shipping address.
     * Fixes IDOR vulnerability from legacy confirm_order_action.php
     */
    public function saveAddress(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
}
