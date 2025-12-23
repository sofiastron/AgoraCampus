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
        Schema::create('enseignants', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers la table utilisateurs
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');

            // Infos spécifiques à l'enseignant
            $table->string('grade', 100)->nullable();
            $table->string('departement', 100)->nullable();
            $table->string('specialite', 150)->nullable();

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignants');
    }
};
