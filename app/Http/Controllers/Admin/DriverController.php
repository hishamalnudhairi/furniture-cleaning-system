<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverRequest;
use App\Http\Requests\StoreDriverPaymentRequest;
use App\Models\ActivityLog;
use App\Models\Driver;
use App\Models\DriverPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function index(): View
    {
        $drivers = Driver::withCount('deliveryTasks')->latest()->paginate(15);

        return view('admin.drivers.index', ['drivers' => $drivers]);
    }

    public function create(): View
    {
        return view('admin.drivers.create', ['settings' => \App\Models\BusinessSetting::current()]);
    }

    public function store(DriverRequest $request): RedirectResponse
    {
        $driver = Driver::create($this->mapData($request->validated()));

        $this->log('driver.created', $driver, __('Created driver :name', ['name' => $driver->name]));

        return redirect()->route('admin.drivers.show', $driver)
            ->with('success', __('Driver created successfully.'));
    }

    public function show(Driver $driver): View
    {
        $driver->load(['deliveryTasks.order.customer', 'driverPayments']);

        return view('admin.drivers.show', [
            'driver' => $driver,
            'totalDue' => $driver->totalDue(),
            'totalPaid' => $driver->totalPaid(),
            'remaining' => $driver->remainingDue(),
            'completedCount' => $driver->completedTasksCount(),
        ]);
    }

    public function edit(Driver $driver): View
    {
        return view('admin.drivers.edit', ['driver' => $driver]);
    }

    public function update(DriverRequest $request, Driver $driver): RedirectResponse
    {
        $driver->update($this->mapData($request->validated()));

        $this->log('driver.updated', $driver, __('Updated driver :name', ['name' => $driver->name]));

        return redirect()->route('admin.drivers.show', $driver)
            ->with('success', __('Driver updated successfully.'));
    }

    /**
     * تسجيل دفعة للسائق.
     */
    public function storePayment(StoreDriverPaymentRequest $request, Driver $driver): RedirectResponse
    {
        $data = $request->validated();
        $amount = round((float) $data['amount'], 2);

        // لا يتجاوز المدفوع إجمالي المستحقات المكتملة
        if (round($driver->totalPaid() + $amount, 2) > $driver->totalDue()) {
            return back()->withInput()->withErrors([
                'amount' => __('Total paid to the driver cannot exceed the total due.'),
            ]);
        }

        DriverPayment::create([
            'driver_id' => $driver->id,
            'type' => 'settlement',
            'amount' => $amount,
            'paid_at' => $data['paid_at'] ?? now(),
            'notes' => $data['notes'] ?? null,
        ]);

        $this->log('driver.payment_recorded', $driver,
            __('Recorded payment of :amount for driver :name', ['amount' => $amount, 'name' => $driver->name]));

        return back()->with('success', __('Driver payment recorded successfully.'));
    }

    /**
     * يحوّل بيانات النموذج (status → is_active).
     */
    private function mapData(array $data): array
    {
        return [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'payment_type' => $data['payment_type'],
            'default_delivery_fee' => $data['default_delivery_fee'] ?? 0,
            'is_active' => ($data['status'] ?? 'active') === 'active',
            'notes' => $data['notes'] ?? null,
        ];
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
