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
            // tambahkan foregkey ke  id aset di tabel aset
            $table->unsignedBigInteger('aset_id')->nullable()->after('id');
            $table->foreign('aset_id')->references('id')->on('aset')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            //
            $table->dropForeign(['aset_id']);
            $table->dropColumn('aset_id');
        });
    }
};
