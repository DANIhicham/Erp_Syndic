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
        Schema::table('echeances_depenses', function (Blueprint $table) {

            // supprimer ancien champ
            $table->dropColumn('date_echeance');

            // nouvelles colonnes
            $table->date('periode_debut')
                ->after('depense_id');

            $table->date('periode_fin')
                ->after('periode_debut');

            $table->date('date_paiement')
                ->nullable()
                ->after('periode_fin');

            $table->string('mode_paiement')
                ->nullable()
                ->after('montant');

            $table->string('reference')
                ->nullable()
                ->after('mode_paiement');

            $table->text('commentaire')
                ->nullable()
                ->after('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
