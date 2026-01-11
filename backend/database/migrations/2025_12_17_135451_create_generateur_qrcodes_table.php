<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenerateurQrcodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     
Schema::create('generateur_qrcodes', function (Blueprint $table) {
    $table->id();
    $table->string('contenu');
    $table->dateTime('date_generation');
    $table->dateTime('expiration');

    $table->foreignId('seance_id')->constrained('seances');
});

    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generateur_qrcodes');
    }
}
