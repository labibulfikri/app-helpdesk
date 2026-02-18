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
        Schema::create('aset', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('nama_aset');
            $table->string('kategori_aset');
            $table->string('lokasi_aset');
            $table->string('kondisi_aset');
            $table->string('nomor_serial')->unique();
            $table->date('tgl_pembelian');
            $table->decimal('nilai_perolehan', 15, 2);
            $table->string('status_aset');
            $table->text('keterangan')->nullable();
            //qr code field
            $table->string('qr_code')->unique();
            $table->string('foto')->nullable();
            //departement id foreign key
            $table->unsignedBigInteger('departement_id');
            //user id foreign key
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('departement_id')->references('id')->on('departements')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset');
    }
};
