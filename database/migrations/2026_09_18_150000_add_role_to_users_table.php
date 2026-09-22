<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'kasir'])->default('kasir')->after('password');
            $table->boolean('aktif')->default(true)->after('role');
            $table->timestamp('terakhir_login')->nullable()->after('aktif');

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'aktif', 'terakhir_login']);
        });
    }
};
