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
        Schema::create('transaction_refunds', function (Blueprint $table) {
            $table->id();
                 // Foreign keys
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Refund details
            $table->decimal('amount', 12, 0); // Amount yang direfund
            $table->decimal('fee', 12, 0)->default(0); // Biaya refund
            $table->enum('method', ['transfer', 'cash'])->default('transfer');
            $table->string('reference')->nullable(); // No referensi refund
            
            // Reason and status
            $table->text('reason')->nullable(); // Alasan refund
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            
            // Notes
            $table->text('notes')->nullable();

            // path ke file bukti
            $table->string('file_path')->nullable(); 
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('created_at');
            $table->index(['transaction_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_refunds');
    }
};
