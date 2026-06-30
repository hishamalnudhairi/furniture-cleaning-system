<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:pickup,delivery,pickup_and_delivery'],
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'customer_fee' => ['nullable', 'numeric', 'min:0'],
            'driver_fee' => ['nullable', 'numeric', 'min:0'],
            'scheduled_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'driver_id' => __('Driver'),
            'type' => __('Task type'),
        ];
    }
}
