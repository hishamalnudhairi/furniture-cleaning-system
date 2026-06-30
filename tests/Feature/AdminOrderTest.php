<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => 'password',
            'role' => User::ROLE_WORKER,
            'is_active' => true,
        ]);
    }

    private function makeOrder(array $overrides = []): Order
    {
        $customer = Customer::create([
            'name' => 'سعيد',
            'phone' => '0500001111',
            'address' => 'مسقط - السيب',
        ]);

        $order = Order::create(array_merge([
            'order_number' => 'ORD-0001',
            'customer_id' => $customer->id,
            'status' => Order::STATUS_NEW,
            'subtotal' => 100,
            'discount' => 0,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'total' => 100,
            'paid_amount' => 0,
            'due_amount' => 100,
            'payment_status' => Order::PAYMENT_UNPAID,
        ], $overrides));

        OrderItem::create([
            'order_id' => $order->id,
            'description' => 'تنظيف كنبات',
            'quantity' => 2,
            'unit_price' => 50,
            'line_total' => 100,
        ]);

        return $order;
    }

    public function test_index_lists_orders(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->staff())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_show_displays_details(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->staff())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('سعيد');
    }

    public function test_status_changes_to_cleaning_ready_delivered(): void
    {
        $order = $this->makeOrder();
        $staff = $this->staff();

        $this->actingAs($staff)->post(route('admin.orders.status', $order), ['status' => 'cleaning'])->assertRedirect();
        $this->assertSame(Order::STATUS_CLEANING, $order->fresh()->status);

        $this->actingAs($staff)->post(route('admin.orders.status', $order), ['status' => 'ready'])->assertRedirect();
        $this->assertSame(Order::STATUS_READY, $order->fresh()->status);

        $this->actingAs($staff)->post(route('admin.orders.status', $order), ['status' => 'delivered'])->assertRedirect();
        $fresh = $order->fresh();
        $this->assertSame(Order::STATUS_DELIVERED, $fresh->status);
        $this->assertNotNull($fresh->delivered_at);

        $this->assertDatabaseHas('activity_logs', ['action' => 'order.status_updated']);
    }

    public function test_delivered_with_balance_shows_warning(): void
    {
        $order = $this->makeOrder(); // due = 100

        $this->actingAs($this->staff())
            ->post(route('admin.orders.status', $order), ['status' => 'delivered'])
            ->assertSessionHas('warning');

        $this->assertSame(Order::STATUS_DELIVERED, $order->fresh()->status);
    }

    public function test_cancel_does_not_delete(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->staff())->post(route('admin.orders.status', $order), ['status' => 'cancelled'])->assertRedirect();

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_cancelled_order_cannot_change_status(): void
    {
        $order = $this->makeOrder(['status' => Order::STATUS_CANCELLED]);

        $this->actingAs($this->staff())
            ->post(route('admin.orders.status', $order), ['status' => 'cleaning'])
            ->assertSessionHas('error');

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_record_payment_updates_totals_and_status(): void
    {
        $order = $this->makeOrder();
        $staff = $this->staff();

        // دفعة جزئية
        $this->actingAs($staff)->post(route('admin.orders.payments.store', $order), [
            'amount' => 40, 'payment_method' => 'cash',
        ])->assertRedirect();

        $order->refresh();
        $this->assertEquals(40.0, (float) $order->paid_amount);
        $this->assertEquals(60.0, (float) $order->due_amount);
        $this->assertSame(Order::PAYMENT_PARTIAL, $order->payment_status);
        $this->assertDatabaseCount('payments', 1);

        // إكمال الدفع
        $this->actingAs($staff)->post(route('admin.orders.payments.store', $order), [
            'amount' => 60, 'payment_method' => 'cash',
        ])->assertRedirect();

        $order->refresh();
        $this->assertEquals(100.0, (float) $order->paid_amount);
        $this->assertEquals(0.0, (float) $order->due_amount);
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'order.payment_recorded']);
    }

    public function test_payment_cannot_exceed_total(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->staff())
            ->post(route('admin.orders.payments.store', $order), ['amount' => 999, 'payment_method' => 'cash'])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(Order::PAYMENT_UNPAID, $order->fresh()->payment_status);
    }

    public function test_edit_discount_recalculates_total(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->staff())
            ->put(route('admin.orders.update', $order), ['discount' => 30, 'notes' => 'ملاحظة'])
            ->assertRedirect(route('admin.orders.show', $order));

        $order->refresh();
        $this->assertEquals(30.0, (float) $order->discount);
        $this->assertEquals(70.0, (float) $order->total);
        $this->assertEquals(70.0, (float) $order->due_amount);
    }

    public function test_total_cannot_be_less_than_paid(): void
    {
        $order = $this->makeOrder(['paid_amount' => 80, 'due_amount' => 20, 'payment_status' => Order::PAYMENT_PARTIAL]);

        // خصم 90 يجعل الإجمالي 10 < المدفوع 80
        $this->actingAs($this->staff())
            ->put(route('admin.orders.update', $order), ['discount' => 90])
            ->assertSessionHasErrors('discount');

        $this->assertEquals(0.0, (float) $order->fresh()->discount);
    }

    public function test_guest_cannot_access(): void
    {
        $order = $this->makeOrder();

        $this->get(route('admin.orders.index'))->assertRedirect('/login');
        $this->get(route('admin.orders.show', $order))->assertRedirect('/login');
    }

    public function test_dashboard_links_to_orders(): void
    {
        $this->actingAs($this->staff())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('admin.orders.index'));
    }
}
