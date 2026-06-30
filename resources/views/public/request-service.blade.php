@extends('layouts.public')

@section('title', __('Request a Service'))

@section('content')
    @php
        $locale = app()->getLocale();
        $businessName = $settings->shopName();
        $bannerImage = $settings->banner_path ?: $settings->poster_path;
        $serviceName = fn ($s) => $locale === 'ar' ? $s->name_ar : ($s->name_en ?: $s->name_ar);
        $req = fn () => '<span class="text-rose-500">*</span>';
    @endphp

    {{-- البوستر / صورة الغلاف --}}
    @if ($settings->show_banner_on_public_page && $bannerImage)
        <div class="mb-4 overflow-hidden rounded-2xl">
            <img src="{{ asset('storage/'.$bannerImage) }}" alt="" class="h-40 w-full object-cover">
        </div>
    @endif

    {{-- ترويسة الهوية --}}
    <div class="mb-6 flex flex-col items-center text-center">
        @if ($settings->show_logo_on_public_page && $settings->logo_path)
            <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="" class="mb-3 h-16 w-16 rounded-2xl object-cover">
        @else
            <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-teal-600 text-2xl text-white">✦</div>
        @endif

        <h1 class="text-xl font-bold text-slate-900">{{ $businessName }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $settings->shortDescription() ?: __('Book a cleaning service quickly and easily.') }}</p>

        @if ($settings->show_working_hours_on_public_page && ($settings->working_days || $settings->opening_time))
            <div class="mt-3 rounded-lg bg-white px-4 py-2 text-xs text-slate-500 ring-1 ring-slate-200">
                <span class="font-semibold text-slate-600">{{ __('Working hours') }}:</span>
                <span class="mx-1">{{ $settings->working_days }}</span>
                @if ($settings->opening_time)<span class="mx-1">{{ $settings->opening_time }} - {{ $settings->closing_time }}</span>@endif
            </div>
        @endif

        @if ($settings->show_shop_contact_on_public_page && $settings->business_phone)
            <p class="mt-2 text-xs text-slate-500">📞 {{ $settings->business_phone }}</p>
        @endif
    </div>

    {{-- ملخص الأخطاء --}}
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-700 ring-1 ring-rose-200">
            <p class="mb-1 font-semibold">{{ __('Please correct the following:') }}</p>
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('request-service.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- بيانات العميل --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-slate-900">{{ __('Your details') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">{!! __('Full name') !!} {!! $req() !!}</label>
                    <input name="customer_name" type="text" value="{{ old('customer_name') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Phone number') }} {!! $req() !!}</label>
                    <input name="phone" type="tel" value="{{ old('phone') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Customer type') }}</label>
                    <select name="customer_type"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <option value="">{{ __('— Optional —') }}</option>
                        @foreach (['individual' => __('Individual'), 'company' => __('Company'), 'mosque' => __('Mosque'), 'organization' => __('Organization')] as $val => $label)
                            <option value="{{ $val }}" @selected(old('customer_type') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Wilaya') }} {!! $req() !!}</label>
                    <input name="wilaya" type="text" value="{{ old('wilaya') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Area / Village') }} {!! $req() !!}</label>
                    <input name="area" type="text" value="{{ old('area') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Detailed address') }}</label>
                    <input name="address" type="text" value="{{ old('address') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
            </div>
        </section>

        {{-- الموقع على الخريطة --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-1 text-base font-bold text-slate-900">
                {{ __('Map location') }} @if ($locationRequired) {!! $req() !!} @endif
            </h2>
            <p class="mb-4 text-xs text-slate-500">{{ __('Your location helps our team reach your home easily.') }}</p>

            <button type="button" id="use-location"
                    data-locating="{{ __('Locating...') }}"
                    data-done="{{ __('Location captured ✓') }}"
                    data-error="{{ __('Could not get your location. Please paste a Google Maps link instead.') }}"
                    class="mb-3 w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700">
                📍 {{ __('Use my current location') }}
            </button>
            <p id="location-status" class="mb-3 text-xs font-medium text-teal-700"></p>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs text-slate-500">{{ __('Latitude') }}</label>
                    <input id="latitude" name="latitude" type="text" value="{{ old('latitude') }}" readonly
                           class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-500">{{ __('Longitude') }}</label>
                    <input id="longitude" name="longitude" type="text" value="{{ old('longitude') }}" readonly
                           class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                </div>
            </div>

            <div class="mt-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Or paste a Google Maps link') }}</label>
                <input id="location_url" name="location_url" type="text" value="{{ old('location_url') }}"
                       placeholder="https://maps.google.com/..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
            </div>

            <div class="mt-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Location notes') }}</label>
                <input name="location_notes" type="text" value="{{ old('location_notes') }}"
                       placeholder="{{ __('e.g. near the mosque, white gate') }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
            </div>
        </section>

        {{-- الخدمات --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-1 text-base font-bold text-slate-900">{{ __('Choose services') }} {!! $req() !!}</h2>
            <p class="mb-4 text-xs text-slate-500">{{ __('Select one or more services.') }}</p>

            <div class="space-y-3">
                @foreach ($services as $service)
                    @php $checked = (bool) old("items.$service->id.selected"); @endphp
                    <div class="rounded-xl border border-slate-200 p-3">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="items[{{ $service->id }}][selected]" value="1"
                                   class="svc-check h-5 w-5 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                   data-target="svc-{{ $service->id }}" @checked($checked)>
                            <span class="font-medium text-slate-800">{{ $serviceName($service) }}</span>
                        </label>

                        <div id="svc-{{ $service->id }}" class="mt-3 grid gap-3 sm:grid-cols-3 {{ $checked ? '' : 'hidden' }}">
                            <div>
                                <label class="mb-1 block text-xs text-slate-500">{{ __('Quantity') }}</label>
                                <input name="items[{{ $service->id }}][quantity]" type="number" min="1" value="{{ old("items.$service->id.quantity", 1) }}"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-slate-500">{{ __('Size') }}</label>
                                <select name="items[{{ $service->id }}][size]"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    @foreach (['unknown' => __('Unknown'), 'small' => __('Small'), 'medium' => __('Medium'), 'large' => __('Large')] as $val => $label)
                                        <option value="{{ $val }}" @selected(old("items.$service->id.size") === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-slate-500">{{ __('Notes') }}</label>
                                <input name="items[{{ $service->id }}][notes]" type="text" value="{{ old("items.$service->id.notes") }}"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- خدمة أخرى --}}
                @php $otherChecked = (bool) old('items.other.selected'); @endphp
                <div class="rounded-xl border border-dashed border-slate-300 p-3">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="items[other][selected]" value="1"
                               class="svc-check h-5 w-5 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                               data-target="svc-other" @checked($otherChecked)>
                        <span class="font-medium text-slate-800">{{ __('Other service') }}</span>
                    </label>
                    <div id="svc-other" class="mt-3 {{ $otherChecked ? '' : 'hidden' }}">
                        <input name="items[other][description]" type="text" value="{{ old('items.other.description') }}"
                               placeholder="{{ __('Describe the service you need') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>
        </section>

        {{-- طريقة التنفيذ والموعد --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-4 text-base font-bold text-slate-900">{{ __('Service method & timing') }}</h2>

            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Service method') }} {!! $req() !!}</label>
            <select name="service_method" required
                    class="mb-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                <option value="">{{ __('— Select —') }}</option>
                @foreach ([
                    'cleaning_at_customer_location' => __('Cleaning at customer location'),
                    'pickup_from_customer' => __('Pickup from customer'),
                    'customer_will_bring_items' => __('Customer will bring items'),
                    'delivery_after_completion' => __('Delivery after completion'),
                ] as $val => $label)
                    <option value="{{ $val }}" @selected(old('service_method') === $val)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Preferred date') }}</label>
                    <input name="preferred_date" type="date" value="{{ old('preferred_date') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Preferred period') }}</label>
                    <select name="preferred_period"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <option value="">{{ __('— Optional —') }}</option>
                        @foreach (['morning' => __('Morning'), 'afternoon' => __('Afternoon'), 'evening' => __('Evening')] as $val => $label)
                            <option value="{{ $val }}" @selected(old('preferred_period') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        {{-- الصور (اختيارية) — تظهر فقط إذا كان رفع الصور مفعّلًا --}}
        @if ($allowImages)
            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-1 text-base font-bold text-slate-900">{{ __('Photos') }} <span class="text-xs font-normal text-slate-400">({{ __('optional') }})</span></h2>
                <p class="mb-3 text-xs text-slate-500">
                    {{ __('You can attach up to :count photos, max :size MB each.', ['count' => $maxImages, 'size' => round($maxImageKb / 1024, 1)]) }}
                </p>
                <input name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple
                       class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-teal-700 hover:file:bg-teal-100">
            </section>
        @endif

        <button type="submit"
                class="w-full rounded-xl bg-teal-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-teal-700">
            {{ __('Submit request') }}
        </button>
    </form>

    <script>
        // إظهار/إخفاء تفاصيل الخدمة عند الاختيار
        document.querySelectorAll('.svc-check').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var target = document.getElementById(cb.dataset.target);
                if (target) {
                    target.classList.toggle('hidden', !cb.checked);
                }
            });
        });

        // الحصول على الموقع الحالي من المتصفح
        (function () {
            var btn = document.getElementById('use-location');
            if (!btn) return;
            var status = document.getElementById('location-status');
            var latEl = document.getElementById('latitude');
            var lngEl = document.getElementById('longitude');
            var urlEl = document.getElementById('location_url');

            btn.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    status.textContent = btn.dataset.error;
                    return;
                }
                var original = btn.textContent;
                btn.disabled = true;
                status.textContent = btn.dataset.locating;

                navigator.geolocation.getCurrentPosition(function (pos) {
                    var lat = pos.coords.latitude.toFixed(7);
                    var lng = pos.coords.longitude.toFixed(7);
                    latEl.value = lat;
                    lngEl.value = lng;
                    if (urlEl && !urlEl.value) {
                        urlEl.value = 'https://www.google.com/maps?q=' + lat + ',' + lng;
                    }
                    status.textContent = btn.dataset.done;
                    btn.disabled = false;
                    btn.textContent = original;
                }, function () {
                    status.textContent = btn.dataset.error;
                    btn.disabled = false;
                    btn.textContent = original;
                });
            });
        })();
    </script>
@endsection
