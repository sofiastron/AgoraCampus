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
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
