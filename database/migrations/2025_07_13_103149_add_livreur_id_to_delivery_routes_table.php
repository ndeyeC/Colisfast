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
        Schema::table('delivery_routes', function (Blueprint $table) {
        $table->unsignedBigInteger('livreur_id')->after('id'); // ou after un autre champ
        $table->foreign('livreur_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_routes', function (Blueprint $table) {
        $table->dropForeign(['livreur_id']);
        $table->dropColumn('livreur_id');
        });
    }
};
