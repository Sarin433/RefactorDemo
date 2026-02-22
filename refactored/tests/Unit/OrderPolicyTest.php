<?php

use App\Models\Order;
use App\Models\StatusReference;
use App\Models\User;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    StatusReference::create(['status_id' => 1, 'status_name' => 'รอยืนยันคำสั่งซื้อ']);
});

test('order owner can update their own order', function (): void {
    $owner  = User::factory()->create();
    $order  = Order::create([
        'order_number' => 'ORD-POLICY-001',
        'user_id'      => $owner->id,
        'status_id'    => 1,
    ]);

    $policy = new OrderPolicy();
    expect($policy->update($owner, $order))->toBeTrue();
});

test('non-owner cannot update order (IDOR prevention)', function (): void {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $order  = Order::create([
        'order_number' => 'ORD-POLICY-001',
        'user_id'      => $owner->id,
        'status_id'    => 1,
    ]);

    $policy = new OrderPolicy();
    expect($policy->update($other, $order))->toBeFalse();
});

test('order owner can save address on their own order', function (): void {
    $owner  = User::factory()->create();
    $order  = Order::create([
        'order_number' => 'ORD-POLICY-002',
        'user_id'      => $owner->id,
        'status_id'    => 1,
    ]);

    $policy = new OrderPolicy();
    expect($policy->saveAddress($owner, $order))->toBeTrue();
});

test('non-owner cannot save address (IDOR prevention)', function (): void {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $order  = Order::create([
        'order_number' => 'ORD-POLICY-002',
        'user_id'      => $owner->id,
        'status_id'    => 1,
    ]);

    $policy = new OrderPolicy();
    expect($policy->saveAddress($other, $order))->toBeFalse();
});
