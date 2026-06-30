@extends('layouts.app')

@section('title', __('Settings'))

@section('content')
    @php $s = $settings; @endphp

    <h1 class="mb-4 text-2xl font-bold text-slate-900">{{ __('Settings') }}</h1>

    @include('partials.flash')

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5 pb-24">
        @csrf
        @method('PUT')

        {{-- ===== بيانات المحل ===== --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-teal-700">🏪 {{ __('Shop information') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="settings-label">{{ __('Shop name (Arabic)') }}</label><input name="shop_name_ar" value="{{ old('shop_name_ar', $s->shop_name_ar) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Shop name (English)') }}</label><input name="shop_name_en" value="{{ old('shop_name_en', $s->shop_name_en) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Short description (Arabic)') }}</label><input name="short_description_ar" value="{{ old('short_description_ar', $s->short_description_ar) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Short description (English)') }}</label><input name="short_description_en" value="{{ old('short_description_en', $s->short_description_en) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Phone number') }}</label><input name="phone" value="{{ old('phone', $s->business_phone) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('WhatsApp') }}</label><input name="whatsapp" value="{{ old('whatsapp', $s->whatsapp) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Email') }}</label><input name="email" type="email" value="{{ old('email', $s->business_email) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Wilaya') }}</label><input name="wilaya" value="{{ old('wilaya', $s->wilaya) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Area / Village') }}</label><input name="area" value="{{ old('area', $s->area) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Shop location URL') }}</label><input name="shop_location_url" value="{{ old('shop_location_url', $s->shop_location_url) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Address (Arabic)') }}</label><input name="address_ar" value="{{ old('address_ar', $s->address_ar) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Address (English)') }}</label><input name="address_en" value="{{ old('address_en', $s->address_en) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Commercial registration') }}</label><input name="commercial_registration_number" value="{{ old('commercial_registration_number', $s->commercial_registration_number) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Activity number') }}</label><input name="activity_number" value="{{ old('activity_number', $s->activity_number) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('VAT number') }}</label><input name="vat_number" value="{{ old('vat_number', $s->vatNumber()) }}" class="settings-input"></div>
            </div>
        </section>

        {{-- ===== أوقات العمل ===== --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-teal-700">🕒 {{ __('Working hours') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="settings-label">{{ __('Working days') }}</label><input name="working_days" value="{{ old('working_days', $s->working_days) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Day off') }}</label><input name="day_off" value="{{ old('day_off', $s->day_off) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Opening time') }}</label><input name="opening_time" type="time" value="{{ old('opening_time', $s->opening_time) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Closing time') }}</label><input name="closing_time" type="time" value="{{ old('closing_time', $s->closing_time) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Morning label (Arabic)') }}</label><input name="morning_period_label_ar" value="{{ old('morning_period_label_ar', $s->morning_period_label_ar) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Morning label (English)') }}</label><input name="morning_period_label_en" value="{{ old('morning_period_label_en', $s->morning_period_label_en) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Afternoon label (Arabic)') }}</label><input name="afternoon_period_label_ar" value="{{ old('afternoon_period_label_ar', $s->afternoon_period_label_ar) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Afternoon label (English)') }}</label><input name="afternoon_period_label_en" value="{{ old('afternoon_period_label_en', $s->afternoon_period_label_en) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Evening label (Arabic)') }}</label><input name="evening_period_label_ar" value="{{ old('evening_period_label_ar', $s->evening_period_label_ar) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Evening label (English)') }}</label><input name="evening_period_label_en" value="{{ old('evening_period_label_en', $s->evening_period_label_en) }}" class="settings-input"></div>
                <div class="sm:col-span-2"><label class="settings-label">{{ __('Out of hours message (Arabic)') }}</label><input name="out_of_hours_message_ar" value="{{ old('out_of_hours_message_ar', $s->out_of_hours_message_ar) }}" class="settings-input"></div>
                <div class="sm:col-span-2"><label class="settings-label">{{ __('Out of hours message (English)') }}</label><input name="out_of_hours_message_en" value="{{ old('out_of_hours_message_en', $s->out_of_hours_message_en) }}" class="settings-input"></div>
            </div>
        </section>

        {{-- ===== الهوية البصرية ===== --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-teal-700">🎨 {{ __('Visual identity') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="settings-label">{{ __('Logo') }}</label>
                    @if ($s->logo_path)<img src="{{ asset('storage/'.$s->logo_path) }}" class="mb-2 h-16 w-16 rounded object-cover ring-1 ring-slate-200">@endif
                    <input name="logo" type="file" accept=".jpg,.jpeg,.png,.webp" class="settings-file">
                </div>
                <div>
                    <label class="settings-label">{{ __('Banner / cover') }}</label>
                    @if ($s->banner_path)<img src="{{ asset('storage/'.$s->banner_path) }}" class="mb-2 h-16 w-full rounded object-cover ring-1 ring-slate-200">@endif
                    <input name="banner" type="file" accept=".jpg,.jpeg,.png,.webp" class="settings-file">
                </div>
                <div><label class="settings-label">{{ __('Primary color') }}</label><input name="primary_color" type="color" value="{{ old('primary_color', $s->primary_color ?: '#0d9488') }}" class="h-10 w-full rounded-lg border border-slate-300"></div>
                <div><label class="settings-label">{{ __('Secondary color') }}</label><input name="secondary_color" type="color" value="{{ old('secondary_color', $s->secondary_color ?: '#0f172a') }}" class="h-10 w-full rounded-lg border border-slate-300"></div>
                <div><label class="settings-label">{{ __('Button color') }}</label><input name="button_color" type="color" value="{{ old('button_color', $s->button_color ?: '#0d9488') }}" class="h-10 w-full rounded-lg border border-slate-300"></div>
                <div><label class="settings-label">{{ __('Background color') }}</label><input name="background_color" type="color" value="{{ old('background_color', $s->background_color ?: '#f8fafc') }}" class="h-10 w-full rounded-lg border border-slate-300"></div>
                <div><label class="settings-label">{{ __('Default font') }}</label><input name="default_font" value="{{ old('default_font', $s->default_font) }}" class="settings-input"></div>
            </div>
            <div class="mt-4 space-y-2">
                <x-settings-toggle name="show_logo_on_public_page" :checked="$s->show_logo_on_public_page" :label="__('Show logo on public page')" />
                <x-settings-toggle name="show_banner_on_public_page" :checked="$s->show_banner_on_public_page" :label="__('Show banner on public page')" />
            </div>
        </section>

        {{-- ===== الضريبة ===== --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-teal-700">🧮 {{ __('Tax') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="settings-label">{{ __('Tax name') }}</label><input name="tax_name" value="{{ old('tax_name', $s->tax_name) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Tax percentage') }} %</label><input name="tax_percentage" type="number" step="0.01" min="0" max="100" value="{{ old('tax_percentage', $s->tax_percentage) }}" class="settings-input"></div>
            </div>
            <div class="mt-4 space-y-2">
                <x-settings-toggle name="tax_enabled" :checked="$s->tax_enabled" :label="__('Tax enabled')" />
                <x-settings-toggle name="prices_include_tax" :checked="$s->prices_include_tax" :label="__('Prices include tax')" />
                <x-settings-toggle name="show_tax_on_invoice" :checked="$s->show_tax_on_invoice" :label="__('Show tax on invoice')" />
            </div>
        </section>

        {{-- ===== الفاتورة والطباعة ===== --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-teal-700">🧾 {{ __('Invoice & printing') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="settings-label">{{ __('Invoice prefix') }}</label><input name="invoice_prefix" value="{{ old('invoice_prefix', $s->invoice_prefix) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Request prefix') }}</label><input name="request_prefix" value="{{ old('request_prefix', $s->request_prefix) }}" class="settings-input"></div>
                <div class="sm:col-span-2"><label class="settings-label">{{ __('Invoice footer (Arabic)') }}</label><input name="invoice_footer_ar" value="{{ old('invoice_footer_ar', $s->invoice_footer_ar) }}" class="settings-input"></div>
                <div class="sm:col-span-2"><label class="settings-label">{{ __('Invoice footer (English)') }}</label><input name="invoice_footer_en" value="{{ old('invoice_footer_en', $s->invoice_footer_en) }}" class="settings-input"></div>
                <div>
                    <label class="settings-label">{{ __('Invoice paper type') }}</label>
                    <select name="invoice_paper_type" class="settings-input">
                        <option value="a4" @selected(old('invoice_paper_type', $s->invoice_paper_type) === 'a4')>A4</option>
                        <option value="thermal" @selected(old('invoice_paper_type', $s->invoice_paper_type) === 'thermal')>{{ __('Thermal') }}</option>
                    </select>
                </div>
                <div>
                    <label class="settings-label">{{ __('Thermal paper width') }}</label>
                    <select name="thermal_paper_width" class="settings-input">
                        <option value="58" @selected((string) old('thermal_paper_width', $s->thermal_paper_width) === '58')>58mm</option>
                        <option value="80" @selected((string) old('thermal_paper_width', $s->thermal_paper_width) === '80')>80mm</option>
                    </select>
                </div>
                <div>
                    <label class="settings-label">{{ __('Invoice language mode') }}</label>
                    <select name="invoice_language_mode" class="settings-input">
                        @foreach (['system_language' => __('System language'), 'customer_language' => __('Customer language'), 'ar' => __('Arabic'), 'en' => __('English')] as $val => $lbl)
                            <option value="{{ $val }}" @selected(old('invoice_language_mode', $s->invoice_language_mode) === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 space-y-2">
                <x-settings-toggle name="invoice_show_qr" :checked="$s->invoice_show_qr" :label="__('Show QR on invoice')" />
                <x-settings-toggle name="invoice_show_logo" :checked="$s->invoice_show_logo" :label="__('Show logo on invoice')" />
                <x-settings-toggle name="print_auto_open" :checked="$s->print_auto_open" :label="__('Auto open print dialog')" />
            </div>
        </section>

        {{-- ===== صفحة طلب العميل ===== --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-teal-700">📥 {{ __('Public request page') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="settings-label">{{ __('Max image count') }}</label><input name="max_image_count" type="number" min="1" max="20" value="{{ old('max_image_count', $s->max_image_count) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Max image size (MB)') }}</label><input name="max_image_size_mb" type="number" step="0.1" min="0.1" max="20" value="{{ old('max_image_size_mb', round(($s->max_image_size_kb ?? 3072) / 1024, 1)) }}" class="settings-input"></div>
                <div class="sm:col-span-2"><label class="settings-label">{{ __('Success message (Arabic)') }}</label><input name="public_success_message_ar" value="{{ old('public_success_message_ar', $s->public_success_message_ar) }}" class="settings-input"></div>
                <div class="sm:col-span-2"><label class="settings-label">{{ __('Success message (English)') }}</label><input name="public_success_message_en" value="{{ old('public_success_message_en', $s->public_success_message_en) }}" class="settings-input"></div>
            </div>
            <div class="mt-4 space-y-2">
                <x-settings-toggle name="public_request_enabled" :checked="$s->public_request_enabled" :label="__('Public request enabled')" />
                <x-settings-toggle name="require_customer_map_location" :checked="$s->require_customer_map_location" :label="__('Require map location')" />
                <x-settings-toggle name="allow_customer_image_uploads" :checked="$s->allow_customer_image_uploads" :label="__('Allow image uploads')" />
                <x-settings-toggle name="show_working_hours_on_public_page" :checked="$s->show_working_hours_on_public_page" :label="__('Show working hours on public page')" />
                <x-settings-toggle name="show_shop_contact_on_public_page" :checked="$s->show_shop_contact_on_public_page" :label="__('Show shop contact on public page')" />
            </div>
        </section>

        {{-- ===== السائقون ===== --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-teal-700">🚚 {{ __('Drivers settings') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="settings-label">{{ __('Default payment type') }}</label>
                    <select name="default_driver_payment_type" class="settings-input">
                        <option value="per_task" @selected(old('default_driver_payment_type', $s->default_driver_payment_type) === 'per_task')>{{ __('Per task') }}</option>
                        <option value="per_day" @selected(old('default_driver_payment_type', $s->default_driver_payment_type) === 'per_day')>{{ __('Per day') }}</option>
                    </select>
                </div>
                <div><label class="settings-label">{{ __('Default delivery fee') }}</label><input name="default_delivery_fee" type="number" step="0.01" min="0" value="{{ old('default_delivery_fee', $s->default_delivery_fee) }}" class="settings-input"></div>
            </div>
            <div class="mt-4 space-y-2">
                <x-settings-toggle name="count_pickup_as_delivery" :checked="$s->count_pickup_as_delivery" :label="__('Count pickup as delivery')" />
                <x-settings-toggle name="count_delivery_as_delivery" :checked="$s->count_delivery_as_delivery" :label="__('Count delivery as delivery')" />
                <x-settings-toggle name="allow_driver_payments" :checked="$s->allow_driver_payments" :label="__('Allow driver payments')" />
            </div>
        </section>

        {{-- ===== المخزون ===== --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-teal-700">📦 {{ __('Inventory settings') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="settings-label">{{ __('Default low stock quantity') }}</label><input name="default_low_stock_quantity" type="number" step="0.01" min="0" value="{{ old('default_low_stock_quantity', $s->inventory_low_stock_threshold) }}" class="settings-input"></div>
            </div>
            <div class="mt-4 space-y-2">
                <x-settings-toggle name="inventory_enabled" :checked="$s->inventory_enabled" :label="__('Inventory enabled')" />
                <x-settings-toggle name="low_stock_alerts" :checked="$s->low_stock_alerts" :label="__('Low stock alerts')" />
                <x-settings-toggle name="allow_manual_inventory_adjustment" :checked="$s->allow_manual_inventory_adjustment" :label="__('Allow manual adjustment')" />
            </div>
        </section>

        {{-- ===== اللغة ===== --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-teal-700">🌐 {{ __('Language') }}</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="settings-label">{{ __('Default language') }}</label>
                    <select name="default_language" class="settings-input">
                        <option value="ar" @selected(old('default_language', $s->default_locale) === 'ar')>{{ __('Arabic') }}</option>
                        <option value="en" @selected(old('default_language', $s->default_locale) === 'en')>{{ __('English') }}</option>
                    </select>
                </div>
                <div>
                    <label class="settings-label">{{ __('Public page default language') }}</label>
                    <select name="public_page_default_language" class="settings-input">
                        <option value="ar" @selected(old('public_page_default_language', $s->public_page_default_language) === 'ar')>{{ __('Arabic') }}</option>
                        <option value="en" @selected(old('public_page_default_language', $s->public_page_default_language) === 'en')>{{ __('English') }}</option>
                    </select>
                </div>
                <div>
                    <label class="settings-label">{{ __('Invoice default language') }}</label>
                    <select name="invoice_default_language" class="settings-input">
                        <option value="ar" @selected(old('invoice_default_language', $s->invoice_default_language) === 'ar')>{{ __('Arabic') }}</option>
                        <option value="en" @selected(old('invoice_default_language', $s->invoice_default_language) === 'en')>{{ __('English') }}</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 space-y-2">
                <x-settings-toggle name="english_enabled" :checked="$s->english_enabled" :label="__('English enabled')" />
            </div>
        </section>

        {{-- ===== عام ===== --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-teal-700">⚙️ {{ __('General settings') }}</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div><label class="settings-label">{{ __('Currency code') }}</label><input name="currency_code" value="{{ old('currency_code', $s->currency_code) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Currency symbol') }}</label><input name="currency_symbol" value="{{ old('currency_symbol', $s->currency_symbol) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Timezone') }}</label><input name="timezone" value="{{ old('timezone', $s->timezone) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Date format') }}</label><input name="date_format" value="{{ old('date_format', $s->date_format) }}" class="settings-input"></div>
                <div><label class="settings-label">{{ __('Decimal places') }}</label><input name="decimal_places" type="number" min="0" max="4" value="{{ old('decimal_places', $s->decimal_places) }}" class="settings-input"></div>
            </div>
        </section>

        {{-- شريط الحفظ الثابت --}}
        <div class="fixed inset-x-0 bottom-0 border-t border-slate-200 bg-white/95 p-3 backdrop-blur">
            <div class="mx-auto max-w-5xl">
                <button type="submit" class="w-full rounded-xl bg-teal-600 px-4 py-3 text-base font-semibold text-white hover:bg-teal-700">💾 {{ __('Save settings') }}</button>
            </div>
        </div>
    </form>

    <style>
        .settings-label { display:block; margin-bottom:.25rem; font-size:.875rem; font-weight:500; color:#334155; }
        .settings-input { width:100%; border-radius:.5rem; border:1px solid #cbd5e1; padding:.5rem .75rem; font-size:.875rem; }
        .settings-input:focus { outline:none; border-color:#14b8a6; box-shadow:0 0 0 1px #14b8a6; }
        .settings-file { display:block; width:100%; font-size:.8rem; }
    </style>
@endsection
