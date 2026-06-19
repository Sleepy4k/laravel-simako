<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_threads', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropUnique(['booking_id']);
            $table->unsignedBigInteger('booking_id')->nullable()->change();
            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();

            $table->foreignId('kost_id')->nullable()->after('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->after('kost_id')->constrained()->cascadeOnDelete();

            $table->unique(['kost_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('message_threads', function (Blueprint $table) {
            $table->dropUnique(['message_threads_kost_id_user_id_unique']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropForeign(['kost_id']);
            $table->dropColumn('kost_id');

            $table->dropForeign(['booking_id']);
            $table->unsignedBigInteger('booking_id')->nullable(false)->change();
            $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
            $table->unique('booking_id');
        });
    }
};
