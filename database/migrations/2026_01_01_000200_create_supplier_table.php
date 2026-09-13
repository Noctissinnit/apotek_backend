<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('telepon', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->string('nama_kontak', 100)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier');
    }
};
