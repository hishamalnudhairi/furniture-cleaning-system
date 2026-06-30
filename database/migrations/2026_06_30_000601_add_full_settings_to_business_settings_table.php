<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            // --- بيانات المحل ---
            $table->string('shop_name_ar')->nullable()->after('project_name');
            $table->string('shop_name_en')->nullable()->after('shop_name_ar');
            $table->text('short_description_ar')->nullable()->after('shop_name_en');
            $table->text('short_description_en')->nullable()->after('short_description_ar');
            $table->string('whatsapp')->nullable()->after('business_phone');
            $table->string('wilaya')->nullable()->after('business_address');
            $table->string('area')->nullable()->after('wilaya');
            $table->text('address_ar')->nullable()->after('area');
            $table->text('address_en')->nullable()->after('address_ar');
            $table->string('shop_location_url')->nullable()->after('address_en');
            $table->string('commercial_registration_number')->nullable()->after('activity_number');
            $table->string('vat_number')->nullable()->after('tax_number');

            // --- أوقات العمل ---
            $table->string('working_days')->nullable();
            $table->string('day_off')->nullable();
            $table->string('opening_time')->nullable();
            $table->string('closing_time')->nullable();
            $table->string('morning_period_label_ar')->nullable();
            $table->string('morning_period_label_en')->nullable();
            $table->string('afternoon_period_label_ar')->nullable();
            $table->string('afternoon_period_label_en')->nullable();
            $table->string('evening_period_label_ar')->nullable();
            $table->string('evening_period_label_en')->nullable();
            $table->text('out_of_hours_message_ar')->nullable();
            $table->text('out_of_hours_message_en')->nullable();

            // --- الهوية البصرية ---
            $table->string('banner_path')->nullable()->after('poster_path');
            $table->string('button_color')->nullable()->after('accent_color');
            $table->string('background_color')->nullable()->after('button_color');
            $table->string('default_font')->nullable()->after('background_color');
            $table->boolean('show_logo_on_public_page')->default(true);
            $table->boolean('show_banner_on_public_page')->default(true);

            // --- الضريبة ---
            $table->boolean('show_tax_on_invoice')->default(true);

            // --- الفاتورة والطباعة ---
            $table->string('request_prefix')->default('REQ-')->after('invoice_prefix');
            $table->boolean('print_auto_open')->default(false);
            $table->string('invoice_language_mode')->default('system_language');

            // --- صفحة طلب العميل ---
            $table->boolean('public_request_enabled')->default(true);
            $table->boolean('allow_customer_image_uploads')->default(true);
            $table->text('public_success_message_ar')->nullable();
            $table->text('public_success_message_en')->nullable();
            $table->boolean('show_working_hours_on_public_page')->default(true);
            $table->boolean('show_shop_contact_on_public_page')->default(true);

            // --- إعدادات السائقين ---
            $table->string('default_driver_payment_type')->default('per_task');
            $table->decimal('default_delivery_fee', 12, 2)->default(0);
            $table->boolean('count_pickup_as_delivery')->default(true);
            $table->boolean('count_delivery_as_delivery')->default(true);
            $table->boolean('allow_driver_payments')->default(true);

            // --- إعدادات المخزون ---
            $table->boolean('low_stock_alerts')->default(true);
            $table->boolean('allow_manual_inventory_adjustment')->default(true);

            // --- اللغة ---
            $table->boolean('english_enabled')->default(true);
            $table->string('public_page_default_language')->default('ar');
            $table->string('invoice_default_language')->default('ar');

            // --- إعدادات عامة ---
            $table->string('currency_code')->default('OMR');
            $table->string('currency_symbol')->default('ر.ع');
            $table->string('timezone')->default('Asia/Muscat');
            $table->string('date_format')->default('Y-m-d');
            $table->unsignedInteger('decimal_places')->default(2);
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn([
                'shop_name_ar', 'shop_name_en', 'short_description_ar', 'short_description_en',
                'whatsapp', 'wilaya', 'area', 'address_ar', 'address_en', 'shop_location_url',
                'commercial_registration_number', 'vat_number',
                'working_days', 'day_off', 'opening_time', 'closing_time',
                'morning_period_label_ar', 'morning_period_label_en',
                'afternoon_period_label_ar', 'afternoon_period_label_en',
                'evening_period_label_ar', 'evening_period_label_en',
                'out_of_hours_message_ar', 'out_of_hours_message_en',
                'banner_path', 'button_color', 'background_color', 'default_font',
                'show_logo_on_public_page', 'show_banner_on_public_page',
                'show_tax_on_invoice',
                'request_prefix', 'print_auto_open', 'invoice_language_mode',
                'public_request_enabled', 'allow_customer_image_uploads',
                'public_success_message_ar', 'public_success_message_en',
                'show_working_hours_on_public_page', 'show_shop_contact_on_public_page',
                'default_driver_payment_type', 'default_delivery_fee',
                'count_pickup_as_delivery', 'count_delivery_as_delivery', 'allow_driver_payments',
                'low_stock_alerts', 'allow_manual_inventory_adjustment',
                'english_enabled', 'public_page_default_language', 'invoice_default_language',
                'currency_code', 'currency_symbol', 'timezone', 'date_format', 'decimal_places',
            ]);
        });
    }
};
