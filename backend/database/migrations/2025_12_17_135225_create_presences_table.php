<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('presences');
    }
}
