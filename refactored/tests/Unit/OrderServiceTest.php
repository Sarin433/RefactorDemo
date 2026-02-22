<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\StatusReference;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    StatusReference::create(['status_id' => 1, 'status_name' => 'รอยืนยันคำสั่งซื้อ']);
    StatusReference::create(['status_id' => 2, 'status_name' => 'ยืนยันคำสั่งซื้อ']);
});

test('createOrder snapshots unit_price and decrements stock', function (): void {
    $product = Product::create([
        'product_number' => 'SKU-001',
        'name'           => 'Test',
        'price'          => 250.00,
        'stock_quantity' => 5,
    ]);
    $user    = User::factory()->create();
    $service = new OrderService();

    $order = $service->createOrder($user, [
        ['product_number' => 'SKU-001', 'quantity' => 2],
    ]);

    $detail = $order->orderDetails()->first();
    expect((float) $detail->unit_price)->toBe(250.0);
    expect($detail->quantity)->toBe(2);

    $product->refresh();
    expect($product->stock_quantity)->toBe(3);
});

test('createOrder throws ValidationException when stock insufficient', function (): void {
    Product::create([
        'product_number' => 'SKU-002',
        'name'           => 'Low Stock',
        'price'          => 100.00,
        'stock_quantity' => 1,
    ]);
    $user    = User::factory()->create();
    $service = new OrderService();

    expect(fn () => $service->createOrder($user, [
        ['product_number' => 'SKU-002', 'quantity' => 5],
    ]))->toThrow(ValidationException::class);
});

test('createOrder generates correct order_number format', function (): void {
    Product::create([
        'product_number' => 'SKU-001',
        'name'           => 'Test',
        'price'          => 100.00,
        'stock_quantity' => 10,
    ]);
    $user    = User::factory()->create();
    $service = new OrderService();

    $order = $service->createOrder($user, [
        ['product_number' => 'SKU-001', 'quantity' => 1],
    ]);

    expect($order->order_number)->toMatch('/^ORD-\d{8}-[A-Z0-9]{8}$/');
});

test('bulkApprove sets status to 2', function (): void {
    $user    = User::factory()->create();
    $service = new OrderService();

    $order = Order::create([
        'order_number' => 'ORD-UNIT-001',
        'user_id'      => $user->id,
        'status_id'    => 1,
    ]);

    $affected = $service->bulkApprove(['ORD-UNIT-001']);

    expect($affected)->toBe(1);
    expect($order->fresh()->status_id)->toBe(2);
});
