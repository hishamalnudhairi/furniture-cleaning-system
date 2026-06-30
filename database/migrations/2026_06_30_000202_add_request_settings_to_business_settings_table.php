<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            // إلزامية تحديد الموقع على الخريطة في الطلب الخارجي
            $table->boolean('require_customer_map_location')->default(true)->after('inventory_low_stock_threshold');
            // أقصى عدد صور مسموح به في الطلب الخارجي
            $table->unsignedInteger('max_image_count')->default(4)->after('require_customer_map_location');
            // أقصى حجم للصورة بالكيلوبايت (3072 = 3MB)
            $table->unsignedInteger('max_image_size_kb')->default(3072)->after('max_image_count');
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn([
                'require_customer_map_location',
                'max_image_count',
                'max_image_size_kb',
            ]);
        });
    }
};
