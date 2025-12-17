<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEtudiantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('etudiants');
    }
}
