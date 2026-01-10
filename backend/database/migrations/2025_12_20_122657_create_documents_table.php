<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->string('titre', 100);
    $table->string('chemin_fichier');
    $table->string('type_document', 50);
    $table->dateTime('date_depot');
    $table->dateTime('date_upload');

    $table->foreignId('module_id')->constrained('modules');
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
