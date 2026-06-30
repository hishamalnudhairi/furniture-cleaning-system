<?php

namespace App\Http\Controllers;

use App\Models\DeliveryTask;
use App\Models\DriverPayment;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * لوحة التحكم الداخلية مع مؤشرات حقيقية مبسّطة.
     */
    public function index(): View
    {
        $today = now()->toDateString();

        $driverDue = (float) DeliveryTask::where('status', DeliveryTask::STATUS_COMPLETED)->sum('driver_fee');
        $driverPaid = (float) DriverPayment::sum('amount');

        $metrics = [
            'todaySales' => (float) Order::whereDate('created_at', $today)->where('status', '!=', Order::STATUS_CANCELLED)->sum('total'),
            'todayOrders' => Order::whereDate('created_at', $today)->count(),
            'cleaningCount' => Order::where('status', Order::STATUS_CLEANING)->count(),
            'readyCount' => Order::where('status', Order::STATUS_READY)->count(),
            'customerDues' => (float) Order::where('status', '!=', Order::STATUS_CANCELLED)->sum('due_amount'),
            'driverDues' => round(max(0, $driverDue - $driverPaid), 2),
            'lowStockCount' => InventoryItem::where(function ($q) {
                $q->whereColumn('quantity', '<=', 'min_quantity')->orWhere('quantity', '<=', 0);
            })->count(),
            'pendingReview' => ServiceRequest::where('status', ServiceRequest::STATUS_PENDING_REVIEW)->count(),
        ];

        return view('dashboard', ['metrics' => $metrics]);
    }
}
