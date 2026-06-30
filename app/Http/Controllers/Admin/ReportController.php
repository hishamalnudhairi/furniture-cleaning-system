<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTask;
use App\Models\Driver;
use App\Models\DriverPayment;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * لوحة المؤشرات الرئيسية.
     */
    public function index(): View
    {
        $today = now()->toDateString();

        return view('admin.reports.index', [
            'todaySales' => (float) Order::whereDate('created_at', $today)->where('status', '!=', Order::STATUS_CANCELLED)->sum('total'),
            'todayOrders' => Order::whereDate('created_at', $today)->count(),
            'todayPaid' => (float) Payment::whereDate('paid_at', $today)->sum('amount'),
            'customerDues' => (float) Order::where('status', '!=', Order::STATUS_CANCELLED)->sum('due_amount'),
            'cleaningCount' => Order::where('status', Order::STATUS_CLEANING)->count(),
            'readyCount' => Order::where('status', Order::STATUS_READY)->count(),
            'driverDues' => $this->driverDuesRemaining(),
            'lowStockCount' => $this->lowStockQuery()->count(),
        ]);
    }

    /**
     * تقرير المبيعات (افتراضيًا: اليوم).
     */
    public function sales(Request $request): View
    {
        [$start, $end] = $this->dateRange($request, now()->toDateString(), now()->toDateString());

        $orders = Order::with('customer')
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->latest()
            ->get();

        return view('admin.reports.sales', [
            'start' => $start,
            'end' => $end,
            'orders' => $orders,
            'count' => $orders->count(),
            'subtotal' => (float) $orders->sum('subtotal'),
            'discount' => (float) $orders->sum('discount'),
            'total' => (float) $orders->sum('total'),
            'paid' => (float) $orders->sum('paid_amount'),
            'due' => (float) $orders->sum('due_amount'),
            'paidCount' => $orders->where('payment_status', Order::PAYMENT_PAID)->count(),
            'partialCount' => $orders->where('payment_status', Order::PAYMENT_PARTIAL)->count(),
            'unpaidCount' => $orders->where('payment_status', Order::PAYMENT_UNPAID)->count(),
        ]);
    }

    /**
     * تقرير الطلبات حسب الحالة.
     */
    public function ordersStatus(): View
    {
        $statuses = [
            Order::STATUS_NEW, Order::STATUS_CLEANING, Order::STATUS_READY,
            Order::STATUS_DELIVERED, Order::STATUS_CANCELLED,
        ];

        $rows = [];
        foreach ($statuses as $status) {
            $rows[$status] = [
                'count' => Order::where('status', $status)->count(),
                'total' => (float) Order::where('status', $status)->sum('total'),
            ];
        }

        return view('admin.reports.orders-status', ['rows' => $rows]);
    }

    /**
     * تقرير المبالغ المتبقية على العملاء.
     */
    public function customerDues(): View
    {
        $rows = Order::where('status', '!=', Order::STATUS_CANCELLED)
            ->where('due_amount', '>', 0)
            ->with('customer')
            ->selectRaw('customer_id, COUNT(*) as orders_count, SUM(due_amount) as total_due, MAX(created_at) as last_order')
            ->groupBy('customer_id')
            ->orderByDesc('total_due')
            ->get();

        return view('admin.reports.customer-dues', ['rows' => $rows]);
    }

    /**
     * تقرير السائقين (افتراضيًا: الشهر الحالي).
     */
    public function drivers(Request $request): View
    {
        [$start, $end] = $this->dateRange($request, now()->startOfMonth()->toDateString(), now()->toDateString());
        $driverId = $request->query('driver_id');

        $drivers = Driver::query()
            ->when($driverId, fn ($q) => $q->where('id', $driverId))
            ->orderBy('name')
            ->get();

        $rows = $drivers->map(function (Driver $driver) use ($start, $end) {
            $tasks = DeliveryTask::where('driver_id', $driver->id)
                ->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);

            $completed = (clone $tasks)->where('status', DeliveryTask::STATUS_COMPLETED);
            $due = (float) (clone $completed)->sum('driver_fee');
            $paid = (float) DriverPayment::where('driver_id', $driver->id)
                ->whereDate('paid_at', '>=', $start)->whereDate('paid_at', '<=', $end)->sum('amount');

            return [
                'driver' => $driver,
                'completed' => (clone $completed)->count(),
                'due' => $due,
                'paid' => $paid,
                'remaining' => round($due - $paid, 2),
                'failed_cancelled' => (clone $tasks)->whereIn('status', [DeliveryTask::STATUS_FAILED, DeliveryTask::STATUS_CANCELLED])->count(),
            ];
        });

        return view('admin.reports.drivers', [
            'start' => $start,
            'end' => $end,
            'driverId' => $driverId,
            'allDrivers' => Driver::orderBy('name')->get(),
            'rows' => $rows,
        ]);
    }

    /**
     * تقرير المخزون الناقص.
     */
    public function inventoryLow(): View
    {
        return view('admin.reports.inventory-low', [
            'items' => $this->lowStockQuery()->orderBy('quantity')->get(),
        ]);
    }

    // ---------------------------------------------------------------------

    private function lowStockQuery()
    {
        return InventoryItem::where(function ($q) {
            $q->whereColumn('quantity', '<=', 'min_quantity')->orWhere('quantity', '<=', 0);
        });
    }

    private function driverDuesRemaining(): float
    {
        $due = (float) DeliveryTask::where('status', DeliveryTask::STATUS_COMPLETED)->sum('driver_fee');
        $paid = (float) DriverPayment::sum('amount');

        return round(max(0, $due - $paid), 2);
    }

    /**
     * يحدد فترة التقرير من المدخلات أو القيم الافتراضية.
     *
     * @return array{0:string,1:string}
     */
    private function dateRange(Request $request, string $defaultStart, string $defaultEnd): array
    {
        $start = $request->query('start_date') ?: $defaultStart;
        $end = $request->query('end_date') ?: $defaultEnd;

        // ضمان أن البداية ليست بعد النهاية
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }
}
