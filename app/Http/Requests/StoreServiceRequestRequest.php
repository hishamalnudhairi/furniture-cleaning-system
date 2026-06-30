<?php

namespace App\Http\Requests;

use App\Models\BusinessSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreServiceRequestRequest extends FormRequest
{
    /**
     * صفحة عامة — مسموحة للجميع.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $settings = BusinessSetting::current();

        // الإلزام الافتراضي للموقع = true (إن لم تكن الإعدادات موجودة)
        $locationRequired = (bool) ($settings->require_customer_map_location ?? true);
        $allowImages = (bool) ($settings->allow_customer_image_uploads ?? true);
        $maxImages = (int) ($settings->max_image_count ?? 4);
        $maxImageKb = (int) ($settings->max_image_size_kb ?? 3072);

        $latLngRule = $locationRequired ? 'required' : 'nullable';

        // إذا كان رفع الصور معطّلًا، لا تُقبل أي صور
        $imagesRule = $allowImages
            ? ['nullable', 'array', 'max:'.$maxImages]
            : ['nullable', 'array', 'max:0'];

        return [
            // بيانات العميل
            'customer_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'wilaya' => ['required', 'string', 'max:100'],
            'area' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'customer_type' => ['nullable', 'in:individual,company,mosque,organization'],

            // الموقع على الخريطة
            'latitude' => [$latLngRule, 'numeric', 'between:-90,90'],
            'longitude' => [$latLngRule, 'numeric', 'between:-180,180'],
            'location_url' => ['nullable', 'string', 'max:500'],
            'location_notes' => ['nullable', 'string', 'max:500'],

            // طريقة التنفيذ والموعد
            'service_method' => ['required', 'in:cleaning_at_customer_location,pickup_from_customer,customer_will_bring_items,delivery_after_completion'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_period' => ['nullable', 'in:morning,afternoon,evening'],

            // الخدمات (يُتحقق من الاختيار في withValidator)
            'items' => ['required', 'array'],

            // الصور (اختيارية)
            'images' => $imagesRule,
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.$maxImageKb],
        ];
    }

    /**
     * تحقق إضافي: يجب اختيار خدمة واحدة على الأقل،
     * وإذا اختار "خدمة أخرى" يجب كتابة وصف.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $items = (array) $this->input('items', []);

            $hasSelected = collect($items)->contains(fn ($item) => ! empty($item['selected']));

            if (! $hasSelected) {
                $v->errors()->add('items', __('Please select at least one service.'));
            }

            $other = $items['other'] ?? [];
            if (! empty($other['selected']) && trim((string) ($other['description'] ?? '')) === '') {
                $v->errors()->add('items.other.description', __('Please describe the other service.'));
            }
        });
    }

    public function attributes(): array
    {
        return [
            'customer_name' => __('Full name'),
            'phone' => __('Phone number'),
            'wilaya' => __('Wilaya'),
            'area' => __('Area / Village'),
            'latitude' => __('Location'),
            'longitude' => __('Location'),
            'service_method' => __('Service method'),
        ];
    }
}
