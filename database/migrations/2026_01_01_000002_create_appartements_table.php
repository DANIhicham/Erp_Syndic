<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appartements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('residence_id')->constrained('residences')->cascadeOnDelete();
            $table->foreignId('proprietaire_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('numero');
            $table->integer('etage');
            $table->string('statut_occupation');
            $table->date('date_signature_contrat')->nullable();
            $table->string('statut_location', 50)->default('Disponible');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appartements');
    }
};
