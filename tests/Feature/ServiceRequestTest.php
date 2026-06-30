<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    private function activeService(): Service
    {
        return Service::create([
            'name_ar' => 'تنظيف كنبات',
            'name_en' => 'Sofa Cleaning',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    /**
     * بيانات طلب صحيحة قابلة لإعادة الاستخدام.
     */
    private function validPayload(Service $service, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'أحمد',
            'phone' => '0500000000',
            'wilaya' => 'مسقط',
            'area' => 'السيب',
            'customer_type' => 'individual',
            'latitude' => '23.5880000',
            'longitude' => '58.3829000',
            'service_method' => 'cleaning_at_customer_location',
            'items' => [
                (string) $service->id => [
                    'selected' => '1',
                    'quantity' => 2,
                    'size' => 'large',
                    'notes' => 'كنب جلد',
                ],
            ],
        ], $overrides);
    }

    public function test_request_page_loads(): void
    {
        $this->get('/request-service')->assertOk();
    }

    public function test_valid_request_creates_only_service_request(): void
    {
        $service = $this->activeService();

        $response = $this->post('/request-service', $this->validPayload($service));

        $response->assertRedirect(route('request-service.thanks'));

        // أنشأ service_request واحد فقط
        $this->assertDatabaseCount('service_requests', 1);

        $sr = ServiceRequest::first();
        $this->assertSame(ServiceRequest::STATUS_PENDING_REVIEW, $sr->status);
        $this->assertSame('REQ-0001', $sr->request_number);
        $this->assertIsArray($sr->services_json);
        $this->assertCount(1, $sr->services_json);

        // لم يُنشئ order ولا payment
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_thanks_page_shows_request_number(): void
    {
        $service = $this->activeService();

        $this->post('/request-service', $this->validPayload($service));

        $this->get('/request-service/thanks')
            ->assertOk()
            ->assertSee('REQ-0001');
    }

    public function test_location_is_required_by_default(): void
    {
        $service = $this->activeService();

        $payload = $this->validPayload($service, ['latitude' => '', 'longitude' => '']);

        $this->post('/request-service', $payload)
            ->assertSessionHasErrors(['latitude', 'longitude']);

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_at_least_one_service_is_required(): void
    {
        $service = $this->activeService();

        $payload = $this->validPayload($service);
        $payload['items'] = []; // لا خدمة مختارة

        $this->post('/request-service', $payload)
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_non_image_files_are_rejected(): void
    {
        Storage::fake('public');
        $service = $this->activeService();

        $payload = $this->validPayload($service, [
            'images' => [UploadedFile::fake()->create('malware.php', 10, 'application/x-php')],
        ]);

        $this->post('/request-service', $payload)
            ->assertSessionHasErrors('images.0');

        $this->assertDatabaseCount('service_requests', 0);
    }
}
