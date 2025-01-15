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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_trip');
            $table->unsignedBigInteger('id_category');
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('code');
            $table->string('destination');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->float('cost');
            $table->boolean('share_cost');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_category')->references('id')->on('categories');
            $table->foreign('id_trip')->references('id')->on('trips');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};