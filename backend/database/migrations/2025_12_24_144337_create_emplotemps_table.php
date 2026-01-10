// database/migrations/xxxx_xx_xx_create_emplotemps_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('emplotemps', function (Blueprint $table) {
            $table->id();
            $table->string('titre')->nullable();
            $table->foreignId('filiere_id')->constrained('filieres')->onDelete('cascade');
            $table->string('semestre', 10); // S1, S2, etc.
            $table->string('niveau')->nullable(); // Licence 1, Master 2, etc.
            $table->enum('statut', ['en_attente', 'valide', 'rejete', 'brouillon'])->default('en_attente');
            
            // Données de l'emploi du temps en JSON
            $table->json('donnees_json')->nullable();
            
            // Pour stocker les données séparément si préféré
            $table->json('horaires')->nullable(); // Les horaires par groupe
            $table->json('affectations')->nullable(); // Enseignants affectés
            $table->json('salles')->nullable(); // Salles affectées
            $table->json('semaines')->nullable(); // Planification par semaines
            
            // Métadonnées
            $table->text('description')->nullable();
            $table->integer('version')->default(1);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            
            // Gestion des créations/modifications
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Index pour les recherches
            $table->index(['filiere_id', 'semestre']);
            $table->index('statut');
            $table->index(['date_debut', 'date_fin']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('emplotemps');
    }
};