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
        Schema::create('presences', function (Blueprint $table) {
    $table->id();

    $table->foreignId('etudiant_id')->constrained('etudiants');
    $table->foreignId('seance_id')->constrained('seances');

    $table->enum('statut', ['présent', 'absent']);
    $table->dateTime('horodatage');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
