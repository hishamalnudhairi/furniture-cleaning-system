<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();

            // --- الهوية وبيانات المحل ---
            $table->string('project_name')->default('Furniture Cleaning System');
            $table->string('business_name')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_email')->nullable();
            $table->text('business_address')->nullable();
            $table->string('activity_number')->nullable(); // رقم النشاط / السجل
            $table->json('working_hours')->nullable();      // أوقات العمل

            // --- الشعار والبوستر والألوان ---
            $table->string('logo_path')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('primary_color')->default('#0d9488');
            $table->string('secondary_color')->default('#0f172a');
            $table->string('accent_color')->default('#f59e0b');

            // --- إعدادات الضريبة ---
            $table->boolean('tax_enabled')->default(false);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->string('tax_number')->nullable();

            // --- إعدادات الفاتورة ---
            $table->string('invoice_prefix')->default('INV-');
            $table->unsignedBigInteger('invoice_next_number')->default(1);
            $table->boolean('invoice_show_logo')->default(true);
            $table->boolean('invoice_show_qr')->default(true); // QR لموقع العميل
            $table->text('invoice_footer_text')->nullable();

            // --- إعدادات الطباعة الحرارية ---
            $table->boolean('thermal_enabled')->default(true);
            $table->unsignedInteger('thermal_paper_width')->default(80); // مم
            $table->unsignedInteger('thermal_font_size')->default(12);

            // --- إعدادات اللغة ---
            $table->string('default_locale')->default('ar');
            $table->json('available_locales')->nullable(); // ["ar","en"]

            // --- إعدادات السائقين ---
            $table->enum('driver_default_commission_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('driver_default_commission_value', 10, 2)->default(0);

            // --- إعدادات المخزون ---
            $table->boolean('inventory_enabled')->default(true);
            $table->decimal('inventory_low_stock_threshold', 12, 2)->default(5);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
