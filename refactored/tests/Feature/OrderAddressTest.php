<?php

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\StatusReference;
use App\Models\User;

beforeEach(function (): void {
    StatusReference::create(['status_id' => 1, 'status_name' => 'รอยืนยันคำสั่งซื้อ']);
    StatusReference::create(['status_id' => 2, 'status_name' => 'ยืนยันคำสั่งซื้อ']);

    Product::create([
        'product_number' => 'SKU-001',
        'name'           => 'Test Product',
        'price'          => 100.00,
        'stock_quantity' => 10,
    ]);
});

function makeTestOrder(User $user): Order
{
    return Order::create([
        'order_number' => 'ORD-ADDR-0001',
        'user_id'      => $user->id,
        'status_id'    => 1,
    ]);
}

test('owner can save shipping address and status does not change', function (): void {
    $owner = User::factory()->create();
    $order = makeTestOrder($owner);

    $this->actingAs($owner)
         ->post("/orders/{$order->order_number}/save-address", [
             'shipping_address' => '123 Main St, Bangkok',
         ])
         ->assertRedirect(route('orders.index'));

    $order->refresh();
    expect($order->shipping_address)->toBe('123 Main St, Bangkok');
    // status_id MUST remain 1 (pending), only admin can change it
    expect($order->status_id)->toBe(1);
});

test('non-owner cannot save address (IDOR protection)', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = makeTestOrder($owner);

    $this->actingAs($other)
         ->post("/orders/{$order->order_number}/save-address", [
             'shipping_address' => 'Attacker Address',
         ])
         ->assertStatus(403);

    $order->refresh();
    expect($order->shipping_address)->toBeNull();
});
