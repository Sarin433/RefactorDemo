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

function createOrderForUser(User $user, int $qty = 2): Order
{
    $order = Order::create([
        'order_number' => 'ORD-TEST-0001',
        'user_id'      => $user->id,
        'status_id'    => 1,
    ]);
    OrderDetail::create([
        'order_number'   => 'ORD-TEST-0001',
        'product_number' => 'SKU-001',
        'quantity'       => $qty,
        'unit_price'     => 100.00,
    ]);
    return $order;
}

test('order owner can update quantity', function (): void {
    $owner = User::factory()->create();
    $order = createOrderForUser($owner);

    $this->actingAs($owner)
         ->post("/orders/{$order->order_number}/details", [
             'product_number' => 'SKU-001',
             'quantity'       => 5,
         ])
         ->assertRedirect(route('orders.index'));

    expect(OrderDetail::where('order_number', $order->order_number)->first()->quantity)->toBe(5);
});

test('non-owner cannot update order detail', function (): void {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $order  = createOrderForUser($owner);

    $this->actingAs($other)
         ->post("/orders/{$order->order_number}/details", [
             'product_number' => 'SKU-001',
             'quantity'       => 99,
         ])
         ->assertStatus(403); // IDOR prevention
});

test('setting quantity to 0 deletes the order detail', function (): void {
    $owner = User::factory()->create();
    $order = createOrderForUser($owner);

    $this->actingAs($owner)->post("/orders/{$order->order_number}/details", [
        'product_number' => 'SKU-001',
        'quantity'       => 0,
    ]);

    expect(OrderDetail::where('order_number', $order->order_number)->count())->toBe(0);
});
