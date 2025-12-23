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
        Schema::table('etudiants', function (Blueprint $table) {
            $table->foreignId('utilisateur_id')
                  ->nullable() // si certains étudiants n'ont pas d'utilisateur pour le moment
                  ->constrained('utilisateurs')
                  ->after('groupe_id') // place la colonne après groupe_id
                  ->onDelete('cascade'); // supprime l'étudiant si l'utilisateur est supprimé
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('etudiants', function (Blueprint $table) {
            $table->dropForeign(['utilisateur_id']);
            $table->dropColumn('utilisateur_id');
        });
    }
};
