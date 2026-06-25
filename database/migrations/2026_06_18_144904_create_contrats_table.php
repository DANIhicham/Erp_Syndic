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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appartement_id')
                ->constrained('appartements')
                ->cascadeOnDelete();

            $table->foreignId('proprietaire_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('locataire_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('loyer', 12, 2);
            $table->decimal('caution', 12, 2);

            $table->date('date_debut');
            $table->date('date_fin')->nullable();

            $table->enum('type_contrat', ['bail_residentiel', 'bail_commercial']);
            $table->enum('type_paiement', ['mensuel', 'trimestriel', 'total']);
            $table->enum('statut', ['en_attente', 'actif', 'termine', 'resilie'])
                ->default('en_attente');

            $table->timestamps();

            // Index utiles pour les filtres fréquents
            $table->index('appartement_id');
            $table->index('proprietaire_id');
            $table->index('locataire_id');
            $table->index('statut');
            $table->index('date_debut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};