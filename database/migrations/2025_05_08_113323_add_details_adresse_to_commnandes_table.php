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
        Schema::table('commnandes', function (Blueprint $table) {
        $table->string('details_adresse_depart')->nullable();
        $table->string('details_adresse_arrivee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commnandes', function (Blueprint $table) {
         $table->dropColumn('details_adresse_depart');
        $table->dropColumn('details_adresse_arrivee');
        });
    }
};
