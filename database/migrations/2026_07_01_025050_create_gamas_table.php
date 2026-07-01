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
        Schema::create('gamas', function (Blueprint $table) {
            $table->id();
            $table->string('gamas_number')->unique();
            $table->string('vendor_ticket_number')->nullable();
            $table->string('case_type');
            $table->dateTime('started_at');
            $table->dateTime('pending_started_at')->nullable();
            $table->dateTime('pending_ended_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->text('rfo_action')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamas');
    }
};
