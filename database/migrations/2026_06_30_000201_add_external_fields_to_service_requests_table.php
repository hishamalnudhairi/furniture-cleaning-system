<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('request_number')->nullable()->unique()->after('id');

            // العنوان التفصيلي
            $table->string('wilaya')->nullable()->after('customer_phone');
            $table->string('area')->nullable()->after('wilaya');
            $table->text('address')->nullable()->after('area');
            // نوع العميل: individual | company | mosque | organization
            $table->string('customer_type')->nullable()->after('address');

            // ملاحظات الموقع
            $table->text('location_notes')->nullable()->after('location_url');

            // الخدمات المختارة (متعددة) بصيغة JSON
            $table->json('services_json')->nullable()->after('service_id');

            // طريقة التنفيذ والموعد
            $table->string('service_method')->nullable()->after('services_json');
            $table->date('preferred_date')->nullable()->after('service_method');
            $table->string('preferred_period')->nullable()->after('preferred_date');
        });

        // جعل إحداثيات الموقع اختيارية على مستوى قاعدة البيانات
        // (الإلزام يُدار في التحقق حسب إعداد require_customer_map_location)
        Schema::table('service_requests', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->change();
            $table->decimal('longitude', 10, 7)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'request_number',
                'wilaya',
                'area',
                'address',
                'customer_type',
                'location_notes',
                'services_json',
                'service_method',
                'preferred_date',
                'preferred_period',
            ]);
        });
    }
};
