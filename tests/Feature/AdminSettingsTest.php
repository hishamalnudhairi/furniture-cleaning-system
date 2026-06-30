<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create([
            'name' => 'U '.$role, 'email' => $role.'@example.com', 'password' => 'password',
            'role' => $role, 'is_active' => true,
        ]);
    }

    /**
     * بيانات صحيحة كاملة لتمرير التحقق (selects مطلوبة بقيم محددة).
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'shop_name_ar' => 'مغسلتي',
            'shop_name_en' => 'My Laundry',
            'invoice_paper_type' => 'a4',
            'thermal_paper_width' => '80',
            'invoice_language_mode' => 'system_language',
            'default_driver_payment_type' => 'per_task',
            'default_language' => 'ar',
            'public_page_default_language' => 'ar',
            'invoice_default_language' => 'ar',
        ], $overrides);
    }

    private function makeOrder(): Order
    {
        $customer = Customer::create(['name' => 'سعيد', 'phone' => '0500001111']);
        $order = Order::create([
            'order_number' => 'ORD-0001', 'customer_id' => $customer->id, 'status' => 'new',
            'subtotal' => 115, 'discount' => 0, 'tax_percentage' => 0, 'tax_amount' => 0,
            'total' => 115, 'paid_amount' => 0, 'due_amount' => 115, 'payment_status' => 'unpaid',
            'latitude' => '23.5', 'longitude' => '58.3',
        ]);
        OrderItem::create(['order_id' => $order->id, 'description' => 'كنب', 'quantity' => 1, 'unit_price' => 115, 'line_total' => 115]);

        return $order;
    }

    // ---------- الوصول ----------

    public function test_admin_can_open_settings(): void
    {
        $this->actingAs($this->user('admin'))->get(route('admin.settings.edit'))->assertOk()->assertSee(__('Settings'));
    }

    public function test_staff_cannot_open_settings(): void
    {
        $this->actingAs($this->user('worker'))->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_guest_cannot_open_settings(): void
    {
        $this->get(route('admin.settings.edit'))->assertRedirect('/login');
    }

    // ---------- التحديث ----------

    public function test_update_shop_data(): void
    {
        $this->actingAs($this->user('admin'))->put(route('admin.settings.update'), $this->payload([
            'shop_name_ar' => 'مغسلة النخبة', 'phone' => '0501234567', 'email' => 'shop@example.com',
        ]))->assertRedirect();

        $s = BusinessSetting::current()->fresh();
        $this->assertSame('مغسلة النخبة', $s->shop_name_ar);
        $this->assertSame('0501234567', $s->business_phone);
        $this->assertSame('shop@example.com', $s->business_email);
    }

    public function test_upload_logo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user('admin'))->put(route('admin.settings.update'), $this->payload([
            'logo' => UploadedFile::fake()->image('logo.png', 100, 100),
        ]))->assertRedirect();

        $path = BusinessSetting::current()->fresh()->logo_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_upload_banner(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user('admin'))->put(route('admin.settings.update'), $this->payload([
            'banner' => UploadedFile::fake()->image('banner.jpg', 400, 200),
        ]))->assertRedirect();

        $path = BusinessSetting::current()->fresh()->banner_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_update_colors(): void
    {
        $this->actingAs($this->user('admin'))->put(route('admin.settings.update'), $this->payload([
            'primary_color' => '#123456', 'button_color' => '#abcdef',
        ]))->assertRedirect();

        $s = BusinessSetting::current()->fresh();
        $this->assertSame('#123456', $s->primary_color);
        $this->assertSame('#abcdef', $s->button_color);
    }

    public function test_tax_settings_reflect_on_invoice(): void
    {
        $order = $this->makeOrder();
        $admin = $this->user('admin');

        // تفعيل الضريبة وإظهارها
        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload([
            'tax_enabled' => '1', 'show_tax_on_invoice' => '1', 'tax_name' => 'VAT', 'tax_percentage' => '5',
        ]));

        $this->actingAs($admin)->get(route('admin.orders.invoice', $order))->assertOk()->assertSee('VAT');

        // إخفاء الضريبة من الفاتورة
        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload([
            'tax_enabled' => '1', 'tax_name' => 'VAT', 'tax_percentage' => '5',
            // show_tax_on_invoice غير مؤشّر => false
        ]));

        $this->actingAs($admin)->get(route('admin.orders.invoice', $order))->assertOk()->assertDontSee('VAT');
    }

    public function test_disabling_qr_hides_it_from_invoice(): void
    {
        $order = $this->makeOrder();
        $admin = $this->user('admin');

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload([
            'invoice_show_logo' => '1',
            // invoice_show_qr غير مؤشّر => false
        ]));

        $this->actingAs($admin)->get(route('admin.orders.invoice', $order))
            ->assertOk()
            ->assertDontSee(__('Scan QR Code for Customer Location'));
    }

    public function test_thermal_width_reflects_in_receipt(): void
    {
        $order = $this->makeOrder();
        $admin = $this->user('admin');

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->payload([
            'thermal_paper_width' => '58',
        ]));

        $this->actingAs($admin)->get(route('admin.orders.receipt', $order))->assertOk()->assertSee('58mm');
    }

    // ---------- صفحة طلب العميل ----------

    public function test_disabling_public_request_shows_message(): void
    {
        BusinessSetting::current()->update(['public_request_enabled' => false]);

        $this->get(route('request-service.create'))->assertOk()->assertSee(__('Service unavailable'));
    }

    public function test_disabling_image_uploads_hides_photos_section(): void
    {
        BusinessSetting::current()->update(['allow_customer_image_uploads' => false]);

        $this->get(route('request-service.create'))->assertOk()->assertDontSee(__('Photos'));
    }

    public function test_max_image_count_reflected_on_public_page(): void
    {
        BusinessSetting::current()->update(['allow_customer_image_uploads' => true, 'max_image_count' => 7]);

        $this->get(route('request-service.create'))->assertOk()->assertSee('7');
    }

    // ---------- المخزون / الروابط ----------

    public function test_disabling_inventory_does_not_break_links(): void
    {
        BusinessSetting::current()->update(['inventory_enabled' => false]);
        $admin = $this->user('admin');

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.inventory.index'))->assertOk(); // الرابط لا يزال يعمل
    }

    public function test_dashboard_links_to_settings_for_admin(): void
    {
        $this->actingAs($this->user('admin'))->get(route('dashboard'))->assertOk()
            ->assertSee(route('admin.settings.edit'));
    }
}
