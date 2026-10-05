<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses_residences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('residence_id')->constrained('residences')->cascadeOnDelete();
            $table->string('titre');
            $table->string('fournisseur')->nullable();
            $table->string('categorie');
            $table->enum('type_depense', ['mensuel', 'trimestriel', 'unique', 'variable']);
            $table->enum('type', ['mensuel', 'trimestriel', 'unique', 'variable']);
            $table->text('description')->nullable();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->decimal('montant', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses_residences');
    }
};
