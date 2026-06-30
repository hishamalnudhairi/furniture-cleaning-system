<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvoiceTest extends TestCase
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

    private function makeOrder(array $orderOverrides = [], array $customerOverrides = []): Order
    {
        $customer = Customer::create(array_merge([
            'name' => 'سعيد العامري',
            'phone' => '0500001111',
            'address' => 'مسقط - السيب',
        ], $customerOverrides));

        $order = Order::create(array_merge([
            'order_number' => 'ORD-0001',
            'customer_id' => $customer->id,
            'status' => Order::STATUS_NEW,
            'subtotal' => 100,
            'discount' => 0,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'total' => 100,
            'paid_amount' => 40,
            'due_amount' => 60,
            'payment_status' => Order::PAYMENT_PARTIAL,
            'latitude' => '23.5880000',
            'longitude' => '58.3829000',
            'location_url' => 'https://www.google.com/maps?q=23.588,58.382',
        ], $orderOverrides));

        OrderItem::create([
            'order_id' => $order->id,
            'description' => 'تنظيف كنبات',
            'quantity' => 2,
            'unit_price' => 50,
            'line_total' => 100,
        ]);

        return $order;
    }

    public function test_a4_invoice_loads_with_business_customer_and_totals(): void
    {
        $order = $this->makeOrder();
        \App\Models\BusinessSetting::current()->update(['business_name' => 'مغسلة الاختبار']);

        $this->actingAs($this->staff())
            ->get(route('admin.orders.invoice', $order))
            ->assertOk()
            ->assertSee('مغسلة الاختبار')   // بيانات المحل
            ->assertSee('سعيد العامري')      // العميل
            ->assertSee('تنظيف كنبات')        // الخدمة
            ->assertSee($order->order_number)
            ->assertSee('100.00')            // الإجمالي
            ->assertSee('60.00');            // المتبقي
    }

    public function test_thermal_receipt_loads(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->staff())
            ->get(route('admin.orders.receipt', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee(__('Thank you for choosing us!'));
    }

    public function test_qr_is_shown_when_location_exists(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->staff())
            ->get(route('admin.orders.invoice', $order))
            ->assertOk()
            ->assertSee(__('Scan QR Code for Customer Location'))
            ->assertSee('<svg', false); // SVG مضمّن
    }

    public function test_qr_is_hidden_when_no_location(): void
    {
        $order = $this->makeOrder(
            ['latitude' => null, 'longitude' => null, 'location_url' => null],
            ['latitude' => null, 'longitude' => null, 'location_url' => null]
        );

        $this->actingAs($this->staff())
            ->get(route('admin.orders.invoice', $order))
            ->assertOk()
            ->assertDontSee(__('Scan QR Code for Customer Location'))
            ->assertSee(__('No location registered for the customer.'));
    }

    public function test_guest_cannot_access_invoice_or_receipt(): void
    {
        $order = $this->makeOrder();

        $this->get(route('admin.orders.invoice', $order))->assertRedirect('/login');
        $this->get(route('admin.orders.receipt', $order))->assertRedirect('/login');
    }

    public function test_order_details_page_shows_print_buttons(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->staff())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee(route('admin.orders.invoice', $order))
            ->assertSee(route('admin.orders.receipt', $order));
    }

    public function test_no_standalone_invoices_table_was_created(): void
    {
        $this->assertFalse(Schema::hasTable('invoices'));
    }
}
