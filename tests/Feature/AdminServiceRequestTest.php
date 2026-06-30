<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceRequestTest extends TestCase
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

    private function makeRequest(array $overrides = []): ServiceRequest
    {
        $sr = ServiceRequest::create(array_merge([
            'customer_name' => 'سعيد',
            'customer_phone' => '0500001111',
            'wilaya' => 'مسقط',
            'area' => 'السيب',
            'latitude' => '23.5880000',
            'longitude' => '58.3829000',
            'location_url' => 'https://maps.google.com/?q=23.588,58.382',
            'services_json' => [
                ['service_id' => null, 'name_ar' => 'تنظيف كنبات', 'name_en' => 'Sofa Cleaning', 'quantity' => 2, 'size' => 'large', 'notes' => null],
            ],
            'service_method' => 'cleaning_at_customer_location',
            'status' => ServiceRequest::STATUS_PENDING_REVIEW,
        ], $overrides));

        $sr->update(['request_number' => 'REQ-'.str_pad((string) $sr->id, 4, '0', STR_PAD_LEFT)]);

        return $sr;
    }

    private function convertPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'سعيد',
            'phone' => '0500001111',
            'wilaya' => 'مسقط',
            'area' => 'السيب',
            'items' => [
                ['service_id' => '', 'description' => 'تنظيف كنبات (كبير)', 'quantity' => 2, 'unit_price' => 10],
            ],
            'discount' => 0,
            'payment_method' => 'cash',
            'payment_status' => 'partial',
            'paid_amount' => 5,
        ], $overrides);
    }

    public function test_index_lists_requests(): void
    {
        $req = $this->makeRequest();

        $this->actingAs($this->staff())
            ->get(route('admin.service-requests.index'))
            ->assertOk()
            ->assertSee($req->request_number);
    }

    public function test_show_displays_details(): void
    {
        $req = $this->makeRequest();

        $this->actingAs($this->staff())
            ->get(route('admin.service-requests.show', $req))
            ->assertOk()
            ->assertSee('سعيد');
    }

    public function test_mark_contacted(): void
    {
        $req = $this->makeRequest();

        $this->actingAs($this->staff())
            ->post(route('admin.service-requests.contacted', $req))
            ->assertRedirect();

        $this->assertSame(ServiceRequest::STATUS_CONTACTED, $req->fresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'service_request.contacted']);
    }

    public function test_cancel_does_not_delete(): void
    {
        $req = $this->makeRequest();

        $this->actingAs($this->staff())
            ->post(route('admin.service-requests.cancel', $req))
            ->assertRedirect();

        $this->assertSame(ServiceRequest::STATUS_CANCELLED, $req->fresh()->status);
        $this->assertDatabaseHas('service_requests', ['id' => $req->id]); // لم يُحذف
    }

    public function test_convert_creates_customer_order_items_and_payment(): void
    {
        $req = $this->makeRequest();

        $this->actingAs($this->staff())
            ->post(route('admin.service-requests.convert', $req), $this->convertPayload())
            ->assertRedirect(route('admin.service-requests.show', $req));

        // عميل + طلب + بند + دفعة
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('payments', 1);

        $order = Order::first();
        $this->assertSame('ORD-0001', $order->order_number);
        $this->assertEquals(20.0, (float) $order->total);          // 2 × 10
        $this->assertEquals(5.0, (float) $order->paid_amount);
        $this->assertEquals(15.0, (float) $order->due_amount);
        $this->assertSame(Order::PAYMENT_PARTIAL, $order->payment_status);
        $this->assertSame(Order::STATUS_NEW, $order->status);
        $this->assertSame($req->id, $order->service_request_id);

        // الطلب المبدئي صار مؤكدًا ومربوطًا
        $req->refresh();
        $this->assertSame(ServiceRequest::STATUS_CONFIRMED, $req->status);
        $this->assertSame($order->id, $req->converted_order_id);
    }

    public function test_pay_later_creates_no_payment(): void
    {
        $req = $this->makeRequest();

        $this->actingAs($this->staff())
            ->post(route('admin.service-requests.convert', $req), $this->convertPayload([
                'payment_method' => 'later',
                'paid_amount' => 0,
            ]));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(Order::PAYMENT_UNPAID, Order::first()->payment_status);
    }

    public function test_cannot_convert_twice(): void
    {
        $req = $this->makeRequest();
        $staff = $this->staff();

        $this->actingAs($staff)->post(route('admin.service-requests.convert', $req), $this->convertPayload());
        $this->assertDatabaseCount('orders', 1);

        // محاولة ثانية
        $this->actingAs($staff)
            ->post(route('admin.service-requests.convert', $req->fresh()), $this->convertPayload())
            ->assertRedirect(route('admin.service-requests.show', $req));

        $this->assertDatabaseCount('orders', 1); // لم يُنشأ طلب ثانٍ
    }

    public function test_cancelled_request_cannot_be_converted(): void
    {
        $req = $this->makeRequest(['status' => ServiceRequest::STATUS_CANCELLED]);

        $this->actingAs($this->staff())
            ->post(route('admin.service-requests.convert', $req), $this->convertPayload())
            ->assertRedirect(route('admin.service-requests.show', $req));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_paid_cannot_exceed_total(): void
    {
        $req = $this->makeRequest();

        $this->actingAs($this->staff())
            ->post(route('admin.service-requests.convert', $req), $this->convertPayload(['paid_amount' => 999]))
            ->assertSessionHasErrors('paid_amount');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_guest_cannot_access(): void
    {
        $req = $this->makeRequest();

        $this->get(route('admin.service-requests.index'))->assertRedirect('/login');
        $this->get(route('admin.service-requests.show', $req))->assertRedirect('/login');
    }
}
