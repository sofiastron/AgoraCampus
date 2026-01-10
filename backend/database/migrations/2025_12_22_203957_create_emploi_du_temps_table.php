<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('emploi_du_temps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filiere_id')->constrained()->onDelete('cascade');
            $table->string('semestre_id', 10); // S1, S2, etc.
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->timestamp('date_generation')->useCurrent();
            $table->timestamp('date_validation')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('data_emploi');
            $table->timestamps();
            
            $table->index(['filiere_id', 'semestre_id']);
            $table->index('statut');
        });
    }

    public function down()
    {
        Schema::dropIfExists('emploi_du_temps');
    }
};