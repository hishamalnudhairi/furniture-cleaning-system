<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->nullOnDelete();
            // المحاسب الذي سعّر/أصدر الطلب
            $table->foreignId('accountant_id')->nullable()->constrained('users')->nullOnDelete();

            // حالة الطلب الرسمي
            $table->enum('status', ['new', 'cleaning', 'ready', 'delivered', 'cancelled'])
                ->default('new')
                ->index();

            // الموقع (منسوخ من العميل وقت الإنشاء — يُستخدم في QR الفاتورة)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_url')->nullable();

            // المبالغ المالية
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);

            // حالة السداد
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])
                ->default('unpaid')
                ->index();

            $table->text('notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
