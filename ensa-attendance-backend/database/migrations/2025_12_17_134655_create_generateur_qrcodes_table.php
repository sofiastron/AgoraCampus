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
     */
    public function down(): void
    {
        Schema::dropIfExists('generateur_qrcodes');
    }
};
