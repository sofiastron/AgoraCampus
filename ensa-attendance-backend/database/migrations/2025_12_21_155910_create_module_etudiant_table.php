<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('module_etudiant', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('module_id');
        $table->unsignedBigInteger('etudiant_id');
        $table->timestamps();

        $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');

        $table->unique(['module_id', 'etudiant_id']);
    });
}



};
