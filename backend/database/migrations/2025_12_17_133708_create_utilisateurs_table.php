<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUtilisateursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('utilisateurs', function (Blueprint $table) {
    $table->id();
    $table->string('nom', 100);
    $table->string('email', 150)->unique();
    $table->string('password');
    $table->enum('role', ['etudiant', 'enseignant', 'administrateur']);
    $table->string('photo')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('utilisateurs');
    }
}
