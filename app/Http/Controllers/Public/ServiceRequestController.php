<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Models\BusinessSetting;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    /**
     * عرض صفحة طلب الخدمة العامة.
     */
    public function create(): View
    {
        $settings = BusinessSetting::current();

        // الصفحة الخارجية يمكن تعطيلها من الإعدادات
        if (! ($settings->public_request_enabled ?? true)) {
            return view('public.request-disabled', ['settings' => $settings]);
        }

        $services = Service::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('public.request-service', [
            'settings' => $settings,
            'services' => $services,
            'locationRequired' => (bool) ($settings->require_customer_map_location ?? true),
            'allowImages' => (bool) ($settings->allow_customer_image_uploads ?? true),
            'maxImages' => (int) ($settings->max_image_count ?? 4),
            'maxImageKb' => (int) ($settings->max_image_size_kb ?? 3072),
        ]);
    }

    /**
     * استقبال الطلب الخارجي وإنشاء service_request فقط (بدون order/invoice/payment).
     */
    public function store(StoreServiceRequestRequest $request): RedirectResponse
    {
        // إن كانت الصفحة معطّلة، لا تستقبل أي طلب
        if (! (BusinessSetting::current()->public_request_enabled ?? true)) {
            return redirect()->route('request-service.create');
        }

        $data = $request->validated();

        // بناء قائمة الخدمات المختارة بصيغة منظمة
        $services = $this->buildSelectedServices($request);

        // بناء رابط الموقع تلقائيًا إن لم يُدخله العميل
        $locationUrl = $data['location_url'] ?? null;
        if (empty($locationUrl) && ! empty($data['latitude']) && ! empty($data['longitude'])) {
            $locationUrl = 'https://www.google.com/maps?q='.$data['latitude'].','.$data['longitude'];
        }

        $serviceRequest = ServiceRequest::create([
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['phone'],
            'wilaya' => $data['wilaya'],
            'area' => $data['area'],
            'address' => $data['address'] ?? null,
            'customer_type' => $data['customer_type'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'location_url' => $locationUrl,
            'location_notes' => $data['location_notes'] ?? null,
            'services_json' => $services,
            'service_method' => $data['service_method'],
            'preferred_date' => $data['preferred_date'] ?? null,
            'preferred_period' => $data['preferred_period'] ?? null,
            'status' => ServiceRequest::STATUS_PENDING_REVIEW,
        ]);

        // رقم الطلب المبدئي مثل REQ-0001
        $serviceRequest->update([
            'request_number' => 'REQ-'.str_pad((string) $serviceRequest->id, 4, '0', STR_PAD_LEFT),
        ]);

        // تخزين الصور (اختيارية وفقط إذا كان رفع الصور مفعّلًا)
        if (BusinessSetting::current()->allow_customer_image_uploads ?? true) {
            $this->storeImages($request, $serviceRequest);
        }

        return redirect()
            ->route('request-service.thanks')
            ->with('request_number', $serviceRequest->request_number);
    }

    /**
     * صفحة شكر/تأكيد بعد الإرسال.
     */
    public function thanks(Request $request): View|RedirectResponse
    {
        $requestNumber = $request->session()->get('request_number');

        if (! $requestNumber) {
            return redirect()->route('request-service.create');
        }

        return view('public.request-thanks', [
            'requestNumber' => $requestNumber,
            'successMessage' => BusinessSetting::current()->publicSuccessMessage(),
        ]);
    }

    /**
     * تحويل مدخلات الخدمات إلى مصفوفة منظمة آمنة.
     */
    private function buildSelectedServices(Request $request): array
    {
        $items = (array) $request->input('items', []);
        $allowedSizes = ['small', 'medium', 'large', 'unknown'];
        $result = [];

        foreach ($items as $key => $item) {
            if (empty($item['selected'])) {
                continue;
            }

            // خدمة أخرى (نصية)
            if ($key === 'other') {
                $result[] = [
                    'service_id' => null,
                    'name' => 'other',
                    'description' => trim((string) ($item['description'] ?? '')),
                ];

                continue;
            }

            $service = Service::where('id', $key)->where('is_active', true)->first();
            if (! $service) {
                continue;
            }

            $size = in_array($item['size'] ?? null, $allowedSizes, true) ? $item['size'] : 'unknown';
            $quantity = max(1, (int) ($item['quantity'] ?? 1));

            $result[] = [
                'service_id' => $service->id,
                'name_ar' => $service->name_ar,
                'name_en' => $service->name_en,
                'quantity' => $quantity,
                'size' => $size,
                'notes' => trim((string) ($item['notes'] ?? '')) ?: null,
            ];
        }

        return $result;
    }

    /**
     * تخزين الصور المرفوعة (إن وُجدت) في القرص العام.
     */
    private function storeImages(Request $request, ServiceRequest $serviceRequest): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $image) {
            if (! $image->isValid()) {
                continue;
            }

            $path = $image->store('service-requests/'.$serviceRequest->id, 'public');

            ServiceRequestImage::create([
                'service_request_id' => $serviceRequest->id,
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getClientMimeType(),
                'size' => $image->getSize(),
            ]);
        }
    }
}
