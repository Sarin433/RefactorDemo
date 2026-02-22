<?php

use App\Models\Order;
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

test('authenticated user can place order and stock decrements', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/orders', [
        'product_number' => ['SKU-001'],
        'quantity'       => [3],
    ]);

    $response->assertRedirect();

    // Order + detail created
    $order = Order::where('user_id', $user->id)->first();
    expect($order)->not->toBeNull();
    expect($order->orderDetails()->count())->toBe(1);

    $detail = $order->orderDetails()->first();
    // unit_price snapshot
    expect((float) $detail->unit_price)->toBe(100.0);

    // stock decremented
    $product = Product::find('SKU-001');
    expect($product->stock_quantity)->toBe(7);
});

test('order number follows ORD-YYYYMMDD-XXXXXXXX format', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post('/orders', [
        'product_number' => ['SKU-001'],
        'quantity'       => [1],
    ]);

    $order = Order::where('user_id', $user->id)->first();
    expect($order->order_number)->toMatch('/^ORD-\d{8}-[A-Z0-9]{8}$/');
});

test('order fails when quantity exceeds stock', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/orders', [
        'product_number' => ['SKU-001'],
        'quantity'       => [99],
    ]);

    // ValidationException in a web request redirects back with session errors
    $response->assertSessionHasErrors('quantity');
    expect(Order::where('user_id', $user->id)->count())->toBe(0);
});

test('guest cannot place order', function (): void {
    $this->post('/orders', [
        'product_number' => ['SKU-001'],
        'quantity'       => [1],
    ])->assertRedirect(route('login'));
});
