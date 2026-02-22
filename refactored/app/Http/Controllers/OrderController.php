<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderDetailRequest;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display user's order history.
     * Replaces legacy public/my_orders.php — N+1 fixed via eager loading.
     */
    public function index(): View
    {
        $orders = Order::with(['orderDetails.product', 'status'])
            ->where('user_id', Auth::id())
            ->orderByDesc('order_date')
            ->get();

        $created = request()->query('created');

        return view('orders.index', compact('orders', 'created'));
    }

    /**
     * Place a new order.
     * Replaces legacy public/actions/add_order_action.php
     */
    public function store(CreateOrderRequest $request): RedirectResponse
    {
        $productNumbers = $request->input('product_number');
        $quantities     = $request->input('quantity');

        // Build items array
        $items = [];
        foreach ($productNumbers as $idx => $sku) {
            $items[] = [
                'product_number' => $sku,
                'quantity'       => (int) $quantities[$idx],
            ];
        }

        $order = $this->orderService->createOrder(Auth::user(), $items);

        return redirect()->route('orders.index', ['created' => $order->order_number]);
    }

    /**
     * Update quantity of an order detail line.
     * Replaces legacy public/actions/edit_order_action.php
     */
    public function updateDetail(UpdateOrderDetailRequest $request, Order $order): RedirectResponse
    {
        $sku      = $request->input('product_number');
        $quantity = (int) $request->input('quantity');

        if ($quantity <= 0) {
            OrderDetail::where('order_number', $order->order_number)
                ->where('product_number', $sku)
                ->delete();
        } else {
            OrderDetail::where('order_number', $order->order_number)
                ->where('product_number', $sku)
                ->update(['quantity' => $quantity]);
        }

        return redirect()->route('orders.index');
    }

    /**
     * Save shipping address only — does NOT change status.
     * Replaces legacy public/actions/confirm_order_action.php
     * Per design decision: status change is admin-only via bulk update.
     */
    public function saveAddress(ConfirmOrderRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->saveShippingAddress($order, $request->input('shipping_address'));

        return redirect()->route('orders.index');
    }
}
