<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:150'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'unit' => ['nullable', 'string', 'max:50'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            // is_price_editable و is_active منطقية تُعالَج في المتحكم
        ];
    }

    public function attributes(): array
    {
        return [
            'name_ar' => __('Service name (Arabic)'),
        ];
    }
}
