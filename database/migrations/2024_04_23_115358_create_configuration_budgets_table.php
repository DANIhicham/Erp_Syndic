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
        Schema::create('configuration_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('residence_id')->constrained('residences')->onDelete('cascade');
            $table->integer('annee');
            $table->decimal('montant_annuel_fixe', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_budgets');
    }
};
