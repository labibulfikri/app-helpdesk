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
        Schema::create('ticket_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained(); // Siapa yang melakukan aksi
        $table->string('status_from'); // Status sebelum
        $table->string('status_to');   // Status sesudah
        $table->text('comment')->nullable(); // Alasan perubahan (misal: alasan penolakan HRD)
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('ticket_histories');
    }
};
