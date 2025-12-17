<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnoncesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('annonces', function (Blueprint $table) {
    $table->id();
    $table->string('titre', 150);
    $table->text('contenu');
    $table->dateTime('date_creation');

    $table->foreignId('module_id')->constrained('modules');
    $table->foreignId('enseignant_id')->constrained('enseignants');
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('annonces');
    }
}
