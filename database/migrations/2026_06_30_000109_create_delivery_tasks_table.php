<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            // نوع المهمة: استلام من العميل أو تسليم له
            $table->enum('type', ['pickup', 'delivery'])->default('delivery');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'])
                ->default('pending')
                ->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            // المبلغ المحصّل من العميل أثناء التوصيل (للمحاسبة مع السائق)
            $table->decimal('amount_collected', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_tasks');
    }
};
