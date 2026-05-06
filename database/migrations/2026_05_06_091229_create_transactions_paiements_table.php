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
        Schema::create('transactions_paiements', function (Blueprint $table) {
             $table->id();
            $table->foreignId('appartement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('annee');
            $table->decimal('montant', 10, 2);
            $table->date('date_paiement')->nullable();
            $table->string('mode_paiement')->nullable();
            $table->string('reference')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_paiements');
    }
};
