<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إضافة أعمدة الرسوم
        Schema::table('delivery_tasks', function (Blueprint $table) {
            $table->decimal('customer_fee', 12, 2)->default(0)->after('status'); // رسوم التوصيل على العميل
            $table->decimal('driver_fee', 12, 2)->default(0)->after('customer_fee'); // مستحق السائق
        });

        // تحويل type و status إلى string لدعم قيم جديدة
        // (pickup_and_delivery للنوع، و failed للحالة)
        Schema::table('delivery_tasks', function (Blueprint $table) {
            $table->string('type')->default('delivery')->change();
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_tasks', function (Blueprint $table) {
            $table->dropColumn(['customer_fee', 'driver_fee']);
        });
    }
};
