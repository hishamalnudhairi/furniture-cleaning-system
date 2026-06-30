<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create([
            'name' => 'U '.$role, 'email' => $role.'@example.com', 'password' => 'password',
            'role' => $role, 'is_active' => true,
        ]);
    }

    // ---------- العملاء ----------

    public function test_index_lists_customers(): void
    {
        Customer::create(['name' => 'سعيد', 'phone' => '0500001111']);

        $this->actingAs($this->user('worker'))->get(route('admin.customers.index'))->assertOk()->assertSee('سعيد');
    }

    public function test_show_customer(): void
    {
        $c = Customer::create(['name' => 'سعيد', 'phone' => '0500001111']);

        $this->actingAs($this->user('worker'))->get(route('admin.customers.show', $c))->assertOk()->assertSee('سعيد');
    }

    public function test_admin_can_create_customer(): void
    {
        $this->actingAs($this->user('admin'))->post(route('admin.customers.store'), [
            'name' => 'خالد', 'phone' => '0509999999', 'customer_type' => 'individual', 'wilaya' => 'مسقط', 'area' => 'السيب',
        ])->assertRedirect();

        $this->assertDatabaseHas('customers', ['name' => 'خالد', 'wilaya' => 'مسقط', 'customer_type' => 'individual']);
    }

    public function test_admin_can_update_customer(): void
    {
        $c = Customer::create(['name' => 'سعيد', 'phone' => '0500001111']);

        $this->actingAs($this->user('admin'))->put(route('admin.customers.update', $c), [
            'name' => 'سعيد المحدث', 'phone' => '0500001111', 'wilaya' => 'صحار',
        ])->assertRedirect();

        $this->assertSame('سعيد المحدث', $c->fresh()->name);
        $this->assertSame('صحار', $c->fresh()->wilaya);
    }

    public function test_staff_cannot_create_customer(): void
    {
        $worker = $this->user('worker');
        $this->actingAs($worker)->get(route('admin.customers.create'))->assertForbidden();
        $this->actingAs($worker)->post(route('admin.customers.store'), ['name' => 'x', 'phone' => '1'])->assertForbidden();
    }

    // ---------- الخدمات ----------

    public function test_index_lists_services(): void
    {
        Service::create(['name_ar' => 'تنظيف كنب', 'name_en' => 'Sofa', 'is_active' => true]);

        $this->actingAs($this->user('worker'))->get(route('admin.services.index'))->assertOk()->assertSee('تنظيف كنب');
    }

    public function test_admin_can_create_service(): void
    {
        $this->actingAs($this->user('admin'))->post(route('admin.services.store'), [
            'name_ar' => 'تنظيف سجاد', 'name_en' => 'Carpet', 'unit' => 'م²',
            'base_price' => 5, 'is_price_editable' => '1', 'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('services', ['name_ar' => 'تنظيف سجاد', 'default_price' => 5.00, 'is_price_editable' => 1, 'is_active' => 1]);
    }

    public function test_admin_can_update_service(): void
    {
        $service = Service::create(['name_ar' => 'تنظيف', 'is_active' => true, 'default_price' => 10]);

        $this->actingAs($this->user('admin'))->put(route('admin.services.update', $service), [
            'name_ar' => 'تنظيف محدث', 'base_price' => 20, 'is_active' => '1', 'is_price_editable' => '1',
        ])->assertRedirect();

        $service->refresh();
        $this->assertSame('تنظيف محدث', $service->name_ar);
        $this->assertEquals(20.0, (float) $service->default_price);
    }

    public function test_admin_can_disable_service(): void
    {
        $service = Service::create(['name_ar' => 'تنظيف', 'is_active' => true]);

        // is_active غير مرسل => false
        $this->actingAs($this->user('admin'))->put(route('admin.services.update', $service), [
            'name_ar' => 'تنظيف',
        ])->assertRedirect();

        $this->assertFalse($service->fresh()->is_active);
    }

    public function test_staff_cannot_create_service(): void
    {
        $this->actingAs($this->user('worker'))->get(route('admin.services.create'))->assertForbidden();
    }

    // ---------- الحماية والروابط ----------

    public function test_guest_cannot_access(): void
    {
        $this->get(route('admin.customers.index'))->assertRedirect('/login');
        $this->get(route('admin.services.index'))->assertRedirect('/login');
    }

    public function test_dashboard_links_to_customers_and_services(): void
    {
        $this->actingAs($this->user('admin'))->get(route('dashboard'))->assertOk()
            ->assertSee(route('admin.customers.index'))
            ->assertSee(route('admin.services.index'));
    }
}
