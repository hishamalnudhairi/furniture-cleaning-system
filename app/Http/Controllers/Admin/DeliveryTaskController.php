<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeliveryTaskRequest;
use App\Models\ActivityLog;
use App\Models\DeliveryTask;
use App\Models\Driver;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryTaskController extends Controller
{
    /**
     * قائمة مهام التوصيل مع الفلاتر.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $driverId = $request->query('driver_id');
        $date = $request->query('date');
        $search = trim((string) $request->query('q', ''));

        $query = DeliveryTask::query()->with(['order.customer', 'driver'])->latest();

        if (in_array($status, [
            DeliveryTask::STATUS_PENDING, DeliveryTask::STATUS_COMPLETED,
            DeliveryTask::STATUS_FAILED, DeliveryTask::STATUS_CANCELLED,
        ], true)) {
            $query->where('status', $status);
        }

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        if ($date) {
            $query->whereDate('scheduled_at', $date);
        }

        if ($search !== '') {
            $query->whereHas('order', function ($o) use ($search) {
                $o->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        return view('admin.delivery-tasks.index', [
            'tasks' => $query->paginate(15)->withQueryString(),
            'drivers' => Driver::where('is_active', true)->orderBy('name')->get(),
            'status' => $status,
            'driverId' => $driverId,
            'date' => $date,
            'search' => $search,
        ]);
    }

    /**
     * إنشاء مهمة توصيل من طلب رسمي.
     */
    public function store(StoreDeliveryTaskRequest $request, Order $order): RedirectResponse
    {
        // لا مهمة لطلب ملغي
        if ($order->isCancelled()) {
            return back()->with('error', __('Cannot create a delivery task for a cancelled order.'));
        }

        $data = $request->validated();

        // السائق يجب أن يكون نشطًا
        $driver = Driver::find($data['driver_id']);
        if (! $driver || ! $driver->is_active) {
            return back()->withInput()->withErrors([
                'driver_id' => __('The selected driver is not active.'),
            ]);
        }

        $driverFee = isset($data['driver_fee']) && $data['driver_fee'] !== null
            ? round((float) $data['driver_fee'], 2)
            : (float) $driver->default_delivery_fee;

        $task = DeliveryTask::create([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'type' => $data['type'],
            'status' => DeliveryTask::STATUS_PENDING,
            'customer_fee' => round((float) ($data['customer_fee'] ?? 0), 2),
            'driver_fee' => $driverFee,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->log('delivery_task.created', $task,
            __('Created :type task for order :no', ['type' => $data['type'], 'no' => $order->order_number]));

        return back()->with('success', __('Delivery task created successfully.'));
    }

    /**
     * تحديث حالة المهمة (تم / فشل / ملغي).
     */
    public function updateStatus(Request $request, DeliveryTask $deliveryTask): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:completed,failed,cancelled'],
        ]);

        $deliveryTask->status = $validated['status'];
        $deliveryTask->completed_at = $validated['status'] === DeliveryTask::STATUS_COMPLETED ? now() : null;
        $deliveryTask->save();

        $this->log('delivery_task.status_updated', $deliveryTask,
            __('Delivery task #:id status changed to :status', ['id' => $deliveryTask->id, 'status' => $validated['status']]));

        return back()->with('success', __('The task status has been updated.'));
    }

    private function log(string $action, object $subject, string $description): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
