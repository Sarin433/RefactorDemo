<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display admin order dashboard with search.
     * Replaces legacy public/admin.php — N+1 fixed, SQLi fixed via bindings.
     */
    public function index(Request $request): View
    {
        $kw = $request->query('q', '');

        $query = Order::with(['user', 'orderDetails.product', 'status'])
            ->orderByDesc('order_date');

        if ($kw !== '') {
            // OWASP A03: parameter binding — no string interpolation
            $query->where(function ($q) use ($kw): void {
                $q->where('order_number', 'LIKE', '%' . $kw . '%')
                  ->orWhereHas('user', function ($q2) use ($kw): void {
                      $q2->where('first_name', 'LIKE', '%' . $kw . '%')
                         ->orWhere('last_name', 'LIKE', '%' . $kw . '%');
                  });
            });
        }

        $orders = $query->get();

        return view('admin.orders.index', compact('orders', 'kw'));
    }

    /**
     * Bulk approve selected orders (set status = 2).
     * Replaces legacy public/actions/bulk_update_action.php — no string concat IN clause.
     */
    public function bulkApprove(BulkUpdateRequest $request): RedirectResponse
    {
        $selected = $request->input('selected_orders', []);
        $affected = $this->orderService->bulkApprove($selected);

        Log::info('order.bulk_approved', [
            'orders'   => $selected,
            'admin_id' => Auth::id(),
            'affected' => $affected,
        ]);

        return redirect()->route('admin.orders.index');
    }
}
