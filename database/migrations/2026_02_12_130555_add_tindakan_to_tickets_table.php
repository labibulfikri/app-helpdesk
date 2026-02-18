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
        Schema::table('tickets', function (Blueprint $table) {
            //tindakan isianya enum 'perbaikan', 'penggantian', 'pembaruan', 'lainnya'
            $table->enum('tindakan', ['pemeliharaan', 'pemeriksaan', 'pembaruan', 'perbaikan'])->nullable()->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            //
            $table->dropColumn('tindakan');
        });
    }
};
