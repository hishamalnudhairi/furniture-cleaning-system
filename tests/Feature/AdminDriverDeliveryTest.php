<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DeliveryTask;
use App\Models\Driver;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDriverDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::create([
            'name' => 'Staff', 'email' => 'staff@example.com', 'password' => 'password',
            'role' => User::ROLE_WORKER, 'is_active' => true,
        ]);
    }

    private function makeDriver(bool $active = true, float $fee = 10): Driver
    {
        return Driver::create([
            'name' => 'سالم', 'phone' => '0500002222',
            'payment_type' => Driver::PAYMENT_PER_TASK,
            'default_delivery_fee' => $fee,
            'is_active' => $active,
        ]);
    }

    private function makeOrder(string $status = Order::STATUS_NEW): Order
    {
        $customer = Customer::create(['name' => 'سعيد', 'phone' => '0500001111']);

        return Order::create([
            'order_number' => 'ORD-0001', 'customer_id' => $customer->id, 'status' => $status,
            'subtotal' => 100, 'discount' => 0, 'tax_percentage' => 0, 'tax_amount' => 0,
            'total' => 100, 'paid_amount' => 0, 'due_amount' => 100, 'payment_status' => Order::PAYMENT_UNPAID,
        ]);
    }

    // ---------- السائقون ----------

    public function test_index_lists_drivers(): void
    {
        $driver = $this->makeDriver();
        $this->actingAs($this->staff())->get(route('admin.drivers.index'))->assertOk()->assertSee('سالم');
    }

    public function test_create_driver(): void
    {
        $this->actingAs($this->staff())->post(route('admin.drivers.store'), [
            'name' => 'خالد', 'phone' => '0500003333',
            'payment_type' => 'per_task', 'default_delivery_fee' => 5, 'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('drivers', ['name' => 'خالد', 'is_active' => 1, 'payment_type' => 'per_task']);
    }

    public function test_update_driver(): void
    {
        $driver = $this->makeDriver();

        $this->actingAs($this->staff())->put(route('admin.drivers.update', $driver), [
            'name' => 'سالم المحدث', 'phone' => '0500002222',
            'payment_type' => 'per_day', 'default_delivery_fee' => 8, 'status' => 'inactive',
        ])->assertRedirect();

        $driver->refresh();
        $this->assertSame('سالم المحدث', $driver->name);
        $this->assertFalse($driver->is_active);
        $this->assertSame('per_day', $driver->payment_type);
    }

    public function test_show_driver(): void
    {
        $driver = $this->makeDriver();
        $this->actingAs($this->staff())->get(route('admin.drivers.show', $driver))->assertOk()->assertSee('سالم');
    }

    // ---------- مهام التوصيل ----------

    public function test_create_delivery_task_from_order(): void
    {
        $order = $this->makeOrder();
        $driver = $this->makeDriver();

        $this->actingAs($this->staff())->post(route('admin.orders.delivery-tasks.store', $order), [
            'type' => 'delivery', 'driver_id' => $driver->id, 'customer_fee' => 2, 'driver_fee' => 15,
        ])->assertRedirect();

        $this->assertDatabaseHas('delivery_tasks', [
            'order_id' => $order->id, 'driver_id' => $driver->id, 'type' => 'delivery',
            'status' => 'pending', 'driver_fee' => 15.00, 'customer_fee' => 2.00,
        ]);
    }

    public function test_delivery_task_uses_default_fee_when_empty(): void
    {
        $order = $this->makeOrder();
        $driver = $this->makeDriver(fee: 12);

        $this->actingAs($this->staff())->post(route('admin.orders.delivery-tasks.store', $order), [
            'type' => 'pickup', 'driver_id' => $driver->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('delivery_tasks', ['order_id' => $order->id, 'driver_fee' => 12.00]);
    }

    public function test_cannot_create_task_for_inactive_driver(): void
    {
        $order = $this->makeOrder();
        $driver = $this->makeDriver(active: false);

        $this->actingAs($this->staff())->post(route('admin.orders.delivery-tasks.store', $order), [
            'type' => 'delivery', 'driver_id' => $driver->id,
        ])->assertSessionHasErrors('driver_id');

        $this->assertDatabaseCount('delivery_tasks', 0);
    }

    public function test_cannot_create_task_for_cancelled_order(): void
    {
        $order = $this->makeOrder(Order::STATUS_CANCELLED);
        $driver = $this->makeDriver();

        $this->actingAs($this->staff())->post(route('admin.orders.delivery-tasks.store', $order), [
            'type' => 'delivery', 'driver_id' => $driver->id,
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('delivery_tasks', 0);
    }

    public function test_update_task_status_completed_failed_cancelled(): void
    {
        $order = $this->makeOrder();
        $driver = $this->makeDriver();
        $staff = $this->staff();

        $task = DeliveryTask::create(['order_id' => $order->id, 'driver_id' => $driver->id, 'type' => 'delivery', 'status' => 'pending', 'driver_fee' => 10]);

        $this->actingAs($staff)->post(route('admin.delivery-tasks.status', $task), ['status' => 'completed'])->assertRedirect();
        $task->refresh();
        $this->assertSame('completed', $task->status);
        $this->assertNotNull($task->completed_at);

        $this->actingAs($staff)->post(route('admin.delivery-tasks.status', $task), ['status' => 'failed'])->assertRedirect();
        $this->assertSame('failed', $task->fresh()->status);

        $this->actingAs($staff)->post(route('admin.delivery-tasks.status', $task), ['status' => 'cancelled'])->assertRedirect();
        $this->assertSame('cancelled', $task->fresh()->status);

        $this->assertDatabaseHas('activity_logs', ['action' => 'delivery_task.status_updated']);
    }

    // ---------- المستحقات والدفعات ----------

    public function test_driver_dues_calculation(): void
    {
        $order = $this->makeOrder();
        $driver = $this->makeDriver();

        DeliveryTask::create(['order_id' => $order->id, 'driver_id' => $driver->id, 'type' => 'delivery', 'status' => 'completed', 'driver_fee' => 10]);
        DeliveryTask::create(['order_id' => $order->id, 'driver_id' => $driver->id, 'type' => 'pickup', 'status' => 'completed', 'driver_fee' => 5]);
        DeliveryTask::create(['order_id' => $order->id, 'driver_id' => $driver->id, 'type' => 'delivery', 'status' => 'cancelled', 'driver_fee' => 100]);
        DeliveryTask::create(['order_id' => $order->id, 'driver_id' => $driver->id, 'type' => 'delivery', 'status' => 'pending', 'driver_fee' => 50]);

        $this->assertEquals(15.0, $driver->totalDue());       // 10 + 5 (المكتملة فقط)
        $this->assertSame(2, $driver->completedTasksCount());
    }

    public function test_record_driver_payment(): void
    {
        $order = $this->makeOrder();
        $driver = $this->makeDriver();
        DeliveryTask::create(['order_id' => $order->id, 'driver_id' => $driver->id, 'type' => 'delivery', 'status' => 'completed', 'driver_fee' => 50]);

        $this->actingAs($this->staff())->post(route('admin.drivers.payments.store', $driver), ['amount' => 20])->assertRedirect();

        $this->assertDatabaseCount('driver_payments', 1);
        $this->assertEquals(20.0, $driver->totalPaid());
        $this->assertEquals(30.0, $driver->remainingDue());
        $this->assertDatabaseHas('activity_logs', ['action' => 'driver.payment_recorded']);
    }

    public function test_cannot_pay_driver_more_than_due(): void
    {
        $order = $this->makeOrder();
        $driver = $this->makeDriver();
        DeliveryTask::create(['order_id' => $order->id, 'driver_id' => $driver->id, 'type' => 'delivery', 'status' => 'completed', 'driver_fee' => 50]);

        $this->actingAs($this->staff())->post(route('admin.drivers.payments.store', $driver), ['amount' => 999])->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('driver_payments', 0);
    }

    // ---------- الحماية والروابط ----------

    public function test_guest_cannot_access(): void
    {
        $this->get(route('admin.drivers.index'))->assertRedirect('/login');
        $this->get(route('admin.delivery-tasks.index'))->assertRedirect('/login');
    }

    public function test_dashboard_links_to_drivers_and_deliveries(): void
    {
        $this->actingAs($this->staff())->get(route('dashboard'))->assertOk()
            ->assertSee(route('admin.drivers.index'))
            ->assertSee(route('admin.delivery-tasks.index'));
    }
}
