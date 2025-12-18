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
        Schema::create('etudiants', function (Blueprint $table) {
    $table->id();
    $table->string('cne', 50)->unique();
    $table->string('niveau', 50);

    $table->foreignId('groupe_id')->constrained('groupes');
    $table->foreign('id')->references('id')->on('utilisateurs')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etudiants');
    }
};
