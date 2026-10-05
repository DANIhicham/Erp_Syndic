<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('nom_fichier');
            $table->string('chemin_stockage');
            $table->string('type_document');
            $table->foreignId('residence_id')->nullable()->constrained('residences')->cascadeOnDelete();
            $table->foreignId('appartement_id')->nullable()->constrained('appartements')->cascadeOnDelete();
            $table->foreignId('transaction_paiement_id')->nullable()->constrained('transactions_paiements')->nullOnDelete();
            $table->foreignId('paiement_loyer_id')->nullable()->constrained('paiements_loyer')->nullOnDelete();
            $table->foreignId('paiement_depense_id')->nullable()->constrained('paiements_depenses')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->dateTime('date_upload')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
