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
        Schema::create('vol_arrives', function (Blueprint $table) {
            $table->id();
            $table->integer("numero");
            $table->string("depart", 255);
            $table->string("heure_arrive");
            $table->float("distance");
            $table->date("date_vol");
            $table->unsignedBigInteger('companie_id');
            $table->unsignedBigInteger('avion_id');
            $table->unsignedBigInteger('saison_id');
            $table->foreign('companie_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('avion_id')->references('id')->on('avions')->onDelete('cascade');
            $table->foreign('saison_id')->references('id')->on('saisons')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vol_arrives');
    }
};
