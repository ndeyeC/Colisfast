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
         $table->string('numero_telephone', 20)->nullable()->after('mode_paiement');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commnandes', function (Blueprint $table) {
              $table->dropColumn('numero_telephone');

        });
    }
};
