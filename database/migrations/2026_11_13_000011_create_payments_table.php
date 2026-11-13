<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('method', 30);           // cash | mock | vnpay | momo
            $table->unsignedInteger('amount');
            $table->string('status', 20)->default('pending'); // pending | success | failed
            $table->string('transaction_code')->nullable()->unique();
            $table->json('raw_response')->nullable(); // lưu phản hồi cổng thanh toán (bản mở rộng)
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
