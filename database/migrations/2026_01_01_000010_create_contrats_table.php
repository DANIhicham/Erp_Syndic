<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appartement_id')->constrained('appartements')->cascadeOnDelete();
            $table->foreignId('proprietaire_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('locataire_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('loyer', 12, 2);
            $table->decimal('caution', 12, 2);
            $table->date('date_debut')->index();
            $table->date('date_fin')->nullable();
            $table->enum('type_contrat', ['bail_residentiel', 'bail_commercial']);
            $table->enum('type_paiement', ['mensuel', 'trimestriel', 'total']);
            $table->enum('statut', ['en_attente', 'actif', 'termine', 'resilie'])
                ->default('en_attente')
                ->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
