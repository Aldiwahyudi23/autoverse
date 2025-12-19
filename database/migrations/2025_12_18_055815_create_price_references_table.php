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
        Schema::create('price_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            // Klasifikasi
            $table->string('part_category')->nullable(); // Mesin, Body, Interior, dll
            $table->string('part_name'); // Nama part/komponen
            $table->string('repair_type'); // Ganti, Reparasi, Service, dll
            
            // Referensi harga
            $table->decimal('min_price', 12, 2)->default(0);
            $table->decimal('max_price', 12, 2)->default(0);
            $table->decimal('average_price', 12, 2)->default(0);
            $table->string('unit')->nullable(); // pcs, set, jam, dll
            $table->string('currency')->default('IDR');
            
            // Informasi tambahan
            $table->text('description')->nullable();
            $table->string('brand')->nullable(); // Merek spesifik
            $table->string('model')->nullable(); // Model spesifik
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexing
            $table->index('category_id');
            $table->index('part_category');
            $table->index('part_name');
            $table->index('repair_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_references');
    }
};
