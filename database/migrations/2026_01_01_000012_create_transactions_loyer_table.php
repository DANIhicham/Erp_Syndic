<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions_loyer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paiement_loyer_id')->constrained('paiements_loyer')->cascadeOnDelete();
            $table->foreignId('contrat_id')->constrained('contrats')->cascadeOnDelete();
            $table->decimal('montant', 12, 2);
            $table->date('date_paiement')->index();
            $table->enum('mode_paiement', ['espece', 'virement', 'cheque']);
            $table->string('reference')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions_loyer');
    }
};
