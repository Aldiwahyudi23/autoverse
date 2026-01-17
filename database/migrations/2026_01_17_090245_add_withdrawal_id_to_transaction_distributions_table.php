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
        Schema::table('transaction_distributions', function (Blueprint $table) {
               // Tambahkan kolom withdrawal_id
            $table->unsignedBigInteger('withdrawal_id')->nullable()->after('id');
            
            // Tambahkan foreign key constraint jika diperlukan
            $table->foreign('withdrawal_id')
                  ->references('id')
                  ->on('withdrawals')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_distributions', function (Blueprint $table) {
              // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['withdrawal_id']);
            
            // Hapus kolom withdrawal_id
            $table->dropColumn('withdrawal_id');
        });
    }
};
