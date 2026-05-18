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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('nom_fichier');
            $table->string('chemin_stockage');
            $table->string('type_document'); 
            $table->foreignId('residence_id')->constrained('residences')->onDelete('cascade');
            $table->foreignId('appartement_id')->nullable()->constrained('appartements')->onDelete('cascade');
            $table->dateTime('date_upload')->useCurrent();
            $table->foreignId('paiement_depense_id')
            ->nullable()
            ->constrained('paiement_depenses')
            ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
