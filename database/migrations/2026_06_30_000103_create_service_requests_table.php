<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            // قد يكون العميل موجودًا مسبقًا أو جديدًا (تُملأ الحقول المباشرة دائمًا)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();

            // بيانات العميل كما أدخلها في الصفحة الخارجية
            $table->string('customer_name');
            $table->string('customer_phone');

            // الموقع على الخريطة (متطلب أساسي)
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location_url')->nullable();

            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            // حالة الطلب الخارجي
            $table->enum('status', ['pending_review', 'contacted', 'confirmed', 'cancelled'])
                ->default('pending_review')
                ->index();

            // إذا تحوّل الطلب إلى طلب رسمي (لا قيد FK لتفادي الاعتماد الدائري مع orders)
            $table->unsignedBigInteger('converted_order_id')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
