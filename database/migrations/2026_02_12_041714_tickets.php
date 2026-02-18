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
        //
       // Migration: create_tickets_table
Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->string('ticket_number')->unique();

    // Pengirim (User)
    $table->foreignId('user_id')->constrained('users');
    $table->enum('category', ['Mesin', 'Peralatan', 'Lainnya']);
    $table->string('resource_type');
    $table->text('problem_detail');
    $table->text('emergency_action')->nullable();

    // Distribusi (HRD)
    $table->foreignId('target_departement_id')->nullable()->constrained('departements');
    $table->foreignId('hrd_id')->nullable()->constrained('users');

    // Penanganan (Dept Tujuan)
    $table->foreignId('technician_id')->nullable()->constrained('users');
    $table->text('action_plan')->nullable();
    $table->datetime('schedule_date')->nullable();

    // Analisa & Perbaikan (Teknisi)
    $table->text('damage_analysis')->nullable(); // JSON/Text untuk 4M+1E & 5WHY
    $table->text('temp_action')->nullable();
    $table->text('perm_action')->nullable();
    $table->text('preventive_action')->nullable();

    // Hasil Akhir
    $table->datetime('completion_date')->nullable();
    $table->integer('total_down_time_minutes')->default(0);
    $table->enum('result', ['Berhasil', 'Gunakan Sementara', 'Gagal'])->nullable();

    $table->string('status')->default('pending_hrd');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('tickets');
    }
};
