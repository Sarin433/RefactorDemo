<?php

use App\Models\Order;
use App\Models\StatusReference;
use App\Models\User;

beforeEach(function (): void {
    StatusReference::create(['status_id' => 1, 'status_name' => 'รอยืนยันคำสั่งซื้อ']);
    StatusReference::create(['status_id' => 2, 'status_name' => 'ยืนยันคำสั่งซื้อ']);
});

function createPendingOrder(User $user, string $orderNum): Order
{
    return Order::create([
        'order_number' => $orderNum,
        'user_id'      => $user->id,
        'status_id'    => 1,
    ]);
}

test('admin can bulk approve orders', function (): void {
    $admin = User::factory()->admin()->create();
    $user  = User::factory()->create();

    $o1 = createPendingOrder($user, 'ORD-BULK-0001');
    $o2 = createPendingOrder($user, 'ORD-BULK-0002');

    $this->actingAs($admin)
         ->post('/admin/orders/bulk', [
             'selected_orders' => ['ORD-BULK-0001', 'ORD-BULK-0002'],
         ])
         ->assertRedirect(route('admin.orders.index'));

    expect($o1->fresh()->status_id)->toBe(2);
    expect($o2->fresh()->status_id)->toBe(2);
});

test('non-admin cannot bulk approve (RBAC)', function (): void {
    $user = User::factory()->create();
    $o1   = createPendingOrder($user, 'ORD-BULK-0001');

    $this->actingAs($user)
         ->post('/admin/orders/bulk', [
             'selected_orders' => ['ORD-BULK-0001'],
         ])
         ->assertStatus(403);

    expect($o1->fresh()->status_id)->toBe(1);
});

test('bulk update with invalid order number returns 422', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
         ->post('/admin/orders/bulk', [
             'selected_orders' => ['DOES-NOT-EXIST'],
         ])
         ->assertSessionHasErrors('selected_orders.0');
});

test('bulk update with empty array returns 422', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
         ->post('/admin/orders/bulk', [
             'selected_orders' => [],
         ])
         ->assertSessionHasErrors('selected_orders');
});
