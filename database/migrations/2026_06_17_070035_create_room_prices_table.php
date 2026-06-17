<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->enum('period', ['monthly', 'quarterly', 'semi_annual', 'annual']);
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('deposit')->default(0);
            $table->timestamps();

            $table->unique(['room_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_prices');
    }
};
