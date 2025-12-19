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
        Schema::create('repair_estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->onDelete('cascade');
            
            // Informasi bagian yang perlu diperbaiki
            $table->string('part_name'); // Nama part/komponen
            $table->text('repair_description'); // Deskripsi perbaikan yang diperlukan
            
            // Status dan urgensi
            $table->enum('urgency', ['segera', 'jangka_panjang'])->default('segera');
            $table->enum('status', ['perlu', 'disarankan', 'opsional'])->default('perlu');
            
            // Estimasi biaya
            $table->decimal('estimated_cost', 12, 2)->default(0);
            
            // Catatan tambahan
            $table->text('notes')->nullable();
            
            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexing
            $table->index('inspection_id');
            $table->index('urgency');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_estimations');
    }
};
