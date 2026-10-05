<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements_depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depense_id')->constrained('depenses_residences')->cascadeOnDelete();
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->date('date_paiement')->nullable();
            $table->decimal('montant', 10, 2);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->string('mode_paiement')->nullable();
            $table->string('reference')->nullable();
            $table->string('statut');
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements_depenses');
    }
};
