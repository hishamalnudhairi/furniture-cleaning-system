<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertServiceRequestRequest extends FormRequest
{
    /**
     * صفحة داخلية محمية — يكفي تسجيل الدخول (تُطبّق عبر middleware في المسار).
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // بيانات العميل (قابلة للتعديل)
            'customer_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'wilaya' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],

            // بنود الخدمات (يُفلتر الفارغ منها في المتحكم)
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.service_id' => ['nullable', 'integer'],

            // المالية
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,card,transfer,later'],
            'payment_status' => ['nullable', 'in:paid,partial,unpaid'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],

            // ملاحظات داخلية
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => __('Full name'),
            'phone' => __('Phone number'),
            'payment_method' => __('Payment method'),
        ];
    }
}
