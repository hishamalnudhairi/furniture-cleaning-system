<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DeliveryTask;
use App\Models\Driver;
use App\Models\DriverPayment;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create([
            'name' => 'U '.$role, 'email' => $role.'@example.com', 'password' => 'password',
            'role' => $role, 'is_active' => true,
        ]);
    }

    private function order(array $o = []): Order
    {
        $customer = Customer::create(['name' => $o['cname'] ?? 'سعيد', 'phone' => $o['phone'] ?? '0500001111']);
        unset($o['cname'], $o['phone']);

        return Order::create(array_merge([
            'order_number' => 'ORD-'.fake()->unique()->numberBetween(1000, 9999),
            'customer_id' => $customer->id, 'status' => 'new',
            'subtotal' => 100, 'discount' => 0, 'tax_percentage' => 0, 'tax_amount' => 0,
            'total' => 100, 'paid_amount' => 0, 'due_amount' => 100, 'payment_status' => 'unpaid',
        ], $o));
    }

    // ---------- الوصول ----------

    public function test_admin_can_open_reports(): void
    {
        $this->actingAs($this->user('admin'))->get(route('admin.reports.index'))->assertOk()->assertSee(__('Reports'));
    }

    public function test_staff_cannot_open_reports(): void
    {
        $this->actingAs($this->user('worker'))->get(route('admin.reports.index'))->assertForbidden();
    }

    public function test_guest_cannot_open_reports(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect('/login');
    }

    // ---------- المبيعات ----------

    public function test_sales_report_totals(): void
    {
        $this->order(['total' => 100, 'paid_amount' => 40, 'due_amount' => 60, 'payment_status' => 'partial']);
        $this->order(['total' => 50, 'paid_amount' => 50, 'due_amount' => 0, 'payment_status' => 'paid']);
        $this->order(['total' => 999, 'status' => 'cancelled']); // مستبعد

        $this->actingAs($this->user('admin'))->get(route('admin.reports.sales'))
            ->assertOk()
            ->assertSee('150.00')  // إجمالي المبيعات (100 + 50)
            ->assertSee('90.00')   // المدفوع (40 + 50)
            ->assertDontSee('999.00');
    }

    // ---------- حالة الطلبات ----------

    public function test_orders_status_report(): void
    {
        $this->order(['status' => 'cleaning', 'total' => 100]);

        $this->actingAs($this->user('admin'))->get(route('admin.reports.orders-status'))
            ->assertOk()
            ->assertSee(__('Cleaning'))
            ->assertSee('100.00');
    }

    // ---------- متبقي العملاء ----------

    public function test_customer_dues_report(): void
    {
        $this->order(['cname' => 'عميل مدين', 'phone' => '0500009999', 'due_amount' => 60, 'payment_status' => 'partial']);
        $this->order(['cname' => 'عميل مسدد', 'phone' => '0500008888', 'due_amount' => 0, 'payment_status' => 'paid']);

        $this->actingAs($this->user('admin'))->get(route('admin.reports.customer-dues'))
            ->assertOk()
            ->assertSee('عميل مدين')
            ->assertSee('60.00')
            ->assertDontSee('عميل مسدد');
    }

    // ---------- السائقون ----------

    public function test_drivers_report(): void
    {
        $order = $this->order();
        $driver = Driver::create(['name' => 'سالم', 'phone' => '0500002222', 'payment_type' => 'per_task', 'default_delivery_fee' => 10, 'is_active' => true]);
        DeliveryTask::create(['order_id' => $order->id, 'driver_id' => $driver->id, 'type' => 'delivery', 'status' => 'completed', 'driver_fee' => 50]);
        DriverPayment::create(['driver_id' => $driver->id, 'type' => 'settlement', 'amount' => 20, 'paid_at' => now()]);

        $this->actingAs($this->user('admin'))->get(route('admin.reports.drivers'))
            ->assertOk()
            ->assertSee('سالم')
            ->assertSee('30.00'); // المتبقي = 50 - 20
    }

    // ---------- المخزون الناقص ----------

    public function test_inventory_low_report(): void
    {
        InventoryItem::create(['name' => 'مادة ناقصة', 'unit' => 'liter', 'quantity' => 2, 'min_quantity' => 5, 'is_active' => true]);
        InventoryItem::create(['name' => 'مادة كافية', 'unit' => 'liter', 'quantity' => 50, 'min_quantity' => 5, 'is_active' => true]);

        $this->actingAs($this->user('admin'))->get(route('admin.reports.inventory-low'))
            ->assertOk()
            ->assertSee('مادة ناقصة')
            ->assertDontSee('مادة كافية');
    }

    // ---------- لوحة التحكم ----------

    public function test_dashboard_shows_real_metrics(): void
    {
        $this->order(['total' => 100, 'status' => 'cleaning']);

        $this->actingAs($this->user('admin'))->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Overview'))
            ->assertSee('100.00');
    }

    public function test_dashboard_links_to_reports(): void
    {
        $this->actingAs($this->user('admin'))->get(route('dashboard'))->assertOk()
            ->assertSee(route('admin.reports.index'));
    }
}
