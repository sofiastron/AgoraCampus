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

            $table->foreignId('etudiant_id')->constrained('etudiants')->onDelete('cascade');
            $table->foreignId('seance_id')->constrained('seances')->onDelete('cascade');

            $table->enum('statut', ['présent', 'absent']);

            // horodatage avec valeur par défaut timestamp courant
            $table->timestamp('horodatage')->useCurrent();

            // Si tu veux, tu peux aussi garder les timestamps Laravel
            // $table->timestamps();
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
