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
        Schema::create('echeances_depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depense_id')->constrained('depenses_residences')->onDelete('cascade');
            $table->date('date_echeance');
            $table->decimal('montant', 10, 2);
            $table->string('statut'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('echeance_depenses');
    }
};
