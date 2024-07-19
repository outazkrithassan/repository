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
        Schema::create('vol_freres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("numero_arrivee")->nullable();
            $table->unsignedBigInteger("numero_depart")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vol_freres');
    }
};
