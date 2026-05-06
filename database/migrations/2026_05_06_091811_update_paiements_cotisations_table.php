<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiement_cotisations', function (Blueprint $table) {

            // supprimer anciennes colonnes
            $table->dropColumn([
                'date_paiement',
                'mode_paiement',
                'reference_paiement'
            ]);

            // ajouter montant attendu
            $table->decimal('montant_attendu', 10, 2)->default(0)->after('annee_concernee');
        });
    }

    public function down(): void
    {
        Schema::table('paiement_cotisations', function (Blueprint $table) {

            $table->date('date_paiement')->nullable();
            $table->string('mode_paiement')->nullable();
            $table->string('reference_paiement')->nullable();

            $table->dropColumn('montant_attendu');
        });
    }
};