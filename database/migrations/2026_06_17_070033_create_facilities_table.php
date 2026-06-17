<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('icon', 100)->nullable();
            $table->timestamps();

            $table->unique(['facility_category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
