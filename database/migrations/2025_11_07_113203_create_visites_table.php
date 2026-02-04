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
        Schema::create('visites', function (Blueprint $table) {
            
            $table->id();
            $table->string('dt_visite');
            $table->string('txt_traitementVisite');
            $table->string('txt_objetVisite');
            $table->string('txt_telVisite');
            $table->string('txt_prenomVisite');
            $table->string('txt_numdordreVisite');

            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visites');
    }
};
