<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create a new order with server-side stock validation.
     * Runs inside a DB transaction with row-level locking to prevent
     * race conditions on stock_quantity.
     *
     * @param  User   $user
     * @param  array  $items  Array of ['product_number' => string, 'quantity' => int]
     * @return Order
     *
     * @throws \Illuminate\Validation\ValidationException  when stock is insufficient
     */
    public function createOrder(User $user, array $items): Order
    {
        return DB::transaction(function () use ($user, $items): Order {
            $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id'      => $user->id,
                'status_id'    => 1,
                'order_date'   => now(),
            ]);

            foreach ($items as $item) {
                /** @var Product $product */
                $product = Product::where('product_number', $item['product_number'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // OWASP A03: server-side stock check (was client-side only in legacy)
                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'quantity' => "สินค้า {$product->name} มีสต็อกไม่เพียงพอ (คงเหลือ {$product->stock_quantity} ชิ้น)",
                    ]);
                }

                // Decrement stock
                $product->decrement('stock_quantity', $item['quantity']);

                // Snapshot unit_price at order time
                OrderDetail::create([
                    'order_number'   => $orderNumber,
                    'product_number' => $product->product_number,
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $product->price,
                ]);
            }

            Log::info('order.created', [
                'order_number' => $orderNumber,
                'user_id'      => $user->id,
                'item_count'   => count($items),
            ]);

            return $order;
        });
    }

    /**
     * Save shipping address only.
     * Does NOT change status_id (per confirmed design decision).
     */
    public function saveShippingAddress(Order $order, string $address): void
    {
        $order->update(['shipping_address' => $address]);
    }

    /**
     * Bulk approve orders by setting status_id = 2.
     *
     * @param  array<string>  $orderNumbers
     * @return int  Number of rows affected
     */
    public function bulkApprove(array $orderNumbers): int
    {
        return Order::whereIn('order_number', $orderNumbers)
            ->update(['status_id' => 2]);
    }
}
