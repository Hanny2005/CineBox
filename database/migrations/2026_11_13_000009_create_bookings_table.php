<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique(); // mã vé điện tử, vd: CB7K2M9QXA
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('showtime_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('total_amount');
            // pending | paid | used | cancelled | expired
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('expires_at')->nullable(); // hết hạn giữ ghế (vd: 10 phút)
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'expires_at']); // job dọn đơn quá hạn
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
