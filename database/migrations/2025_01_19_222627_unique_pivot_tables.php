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
        Schema::table('event_participants', function (Blueprint $table) {
            $table->unique(['id_user', 'id_event']);
        });

        Schema::table('trip_participants', function (Blueprint $table) {
            $table->unique(['id_user', 'id_trip']);
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->unique(['id_user', 'id_poll', 'id_option']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropUnique(['id_user', 'id_event']);
        });

        Schema::table('trip_participants', function (Blueprint $table) {
            $table->dropUnique(['id_user', 'id_trip']);
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropUnique(['id_user', 'id_poll', 'id_option']);
        });
    }
};
