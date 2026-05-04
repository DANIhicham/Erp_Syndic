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
        Schema::create('budget_annuel_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('residence_id')->constrained('residences')->onDelete('cascade');
            $table->integer('annee');
            $table->decimal('total_cotisations', 12, 2)->default(0);
            $table->decimal('total_depenses', 12, 2)->default(0);
            $table->decimal('solde', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_annuel_resumes');
    }
};
