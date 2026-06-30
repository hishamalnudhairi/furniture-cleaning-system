<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // نوع المحاسبة: per_task | per_day
            $table->string('payment_type')->default('per_task')->after('phone');
            // قيمة التوصيلة الافتراضية (مستحق السائق المقترح لكل مهمة)
            $table->decimal('default_delivery_fee', 12, 2)->default(0)->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'default_delivery_fee']);
        });
    }
};
