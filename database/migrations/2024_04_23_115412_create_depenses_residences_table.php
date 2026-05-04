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
        Schema::create('depenses_residences', function (Blueprint $table) {
           $table->id();
            $table->foreignId('residence_id')->constrained('residences')->onDelete('cascade');
            $table->integer('annee');
            $table->string('titre');
            $table->string('fournisseur')->nullable();
            $table->string('categorie');
            $table->decimal('montant_reel', 10, 2);
            $table->string('frequence'); 
            $table->text('description')->nullable();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depense_residences');
    }
};
