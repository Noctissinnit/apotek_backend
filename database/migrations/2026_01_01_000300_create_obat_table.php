<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obat', function (Blueprint $table) {
            $table->id();
            $table->string('kode_obat', 30)->unique();
            $table->string('nama', 150);
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('supplier')->nullOnDelete();

            // Penggolongan obat sesuai regulasi (bebas, bebas terbatas, keras, dst).
            $table->enum('golongan', [
                'bebas', 'bebas_terbatas', 'keras', 'narkotika', 'psikotropika', 'herbal',
            ])->default('bebas');
            $table->string('bentuk_sediaan', 50)->nullable();   // tablet, sirup, salep, ...
            $table->string('satuan', 20)->default('strip');     // strip, botol, tube, box
            $table->string('kandungan', 191)->nullable();
            $table->string('produsen', 150)->nullable();

            $table->decimal('harga_beli', 12, 2)->default(0);
            $table->decimal('harga_jual', 12, 2)->default(0);
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(10);
            $table->date('tanggal_kadaluarsa')->nullable();

            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('nama');
            $table->index('golongan');
            $table->index('tanggal_kadaluarsa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obat');
    }
};
