<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            // Kasir yang mencatat transaksi. Nullable supaya transaksi lama
            // tetap valid, dan nullOnDelete supaya riwayat tidak ikut hilang
            // kalau akun kasirnya dihapus.
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
