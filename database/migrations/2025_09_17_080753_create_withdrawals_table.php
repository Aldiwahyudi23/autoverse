<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, approved, rejected, processing, completed
            $table->decimal('total_amount', 12, 0)->default(0);
            $table->decimal('admin_fee', 12, 0)->default(0)->nullable(); // biaya admin jika ada
            $table->decimal('final_amount', 12, 0)->default(0)->nullable(); // total_amount - admin_fee
            $table->string('payment_method')->nullable(); // transfer, cash, etc
            $table->string('account_number')->nullable(); // nomor rekening/tujuan transfer
            $table->string('account_name')->nullable(); // nama pemilik rekening
            $table->string('bank_name')->nullable(); // nama bank jika transfer
             $table->string('file_path')->nullable(); // path ke file bukti
            $table->text('rejection_reason')->nullable(); // alasan penolakan
            $table->text('notes')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
