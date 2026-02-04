<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departs', function (Blueprint $table) {
            $table->id();
            $table->string('txt_bordereaucd')->nullable();
            $table->string('txt_numdordrecd');
            $table->index('txt_numdordrecd'); 
            $table->string('txt_caracterecd')->nullable();
            $table->date('dt_datecouriercd');
            $table->string('txt_categoriecd');
            $table->string('txt_referencecourierarriveecd')->nullable();
            $table->string('txt_referencecourierdepartcd')->nullable();
            $table->string('txt_nombrepiececd');
            $table->string('txt_referencecd');
            $table->string('txt_objetcd');
            $table->string('txt_nicadcd')->nullable(); 
            $table->string('txt_situationcd')->nullable(); 
            $table->string('txt_prenomcd')->nullable(); 
            $table->string('txt_nomcd')->nullable();  
            $table->decimal('txt_surfacecd', 20, 2)->nullable();
            $table->string('txt_numLotcd')->nullabe();
            $table->string('txt_destinatairecd');
            $table->date('dt_dateenvoicd');
            $table->string('txt_referencereceptioncd')->nullable();
            $table->string('txt_observationcd')->nullable();
            $table->string('txt_dureetraitementcd')->nullable();
            $table->string('fichierPDFcd')->nullable();
  

            $table->timestamps(); 
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departs');
    }
};
