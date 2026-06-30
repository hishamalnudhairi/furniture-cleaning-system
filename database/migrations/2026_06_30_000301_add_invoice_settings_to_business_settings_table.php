<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            // اسم الضريبة المعروض (مثل: ضريبة القيمة المضافة / VAT)
            $table->string('tax_name')->nullable()->after('tax_number');
            // هل الأسعار شاملة الضريبة؟
            $table->boolean('prices_include_tax')->default(true)->after('tax_name');
            // نص أسفل الفاتورة بلغتين
            $table->text('invoice_footer_ar')->nullable()->after('invoice_footer_text');
            $table->text('invoice_footer_en')->nullable()->after('invoice_footer_ar');
            // نوع الورق الافتراضي للفاتورة: a4 | thermal
            $table->string('invoice_paper_type')->default('a4')->after('invoice_footer_en');
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn([
                'tax_name',
                'prices_include_tax',
                'invoice_footer_ar',
                'invoice_footer_en',
                'invoice_paper_type',
            ]);
        });
    }
};
