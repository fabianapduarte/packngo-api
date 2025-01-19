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
            $table->dropColumn('deleted_at');
        });

        Schema::table('trip_participants', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('trip_participants', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }
};
