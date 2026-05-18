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
        Schema::table('depenses_residences', function (Blueprint $table) {



            // nouveaux champs
            $table->enum('type_depense', [
                'mensuel',
                'trimestriel',
                'unique',
                'variable'
            ])->after('categorie');

            $table->decimal('montant', 12, 2)
                ->nullable()
                ->after('date_fin');
;
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
