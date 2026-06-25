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
        Schema::create('paiements_loyer', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contrat_id')
                ->constrained('contrats')
                ->cascadeOnDelete();

            $table->date('periode_debut');
            $table->date('periode_fin');

            $table->decimal('montant', 12, 2);
            $table->decimal('montant_paye', 12, 2)->default(0);

            $table->date('date_echeance');

            $table->enum('statut', ['en_attente', 'partiel', 'paye', 'en_retard'])
                ->default('en_attente');

            $table->text('commentaire')->nullable();

            $table->timestamps();

            // Index demandés
            $table->index('contrat_id');
            $table->index('date_echeance');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements_loyer');
    }
};