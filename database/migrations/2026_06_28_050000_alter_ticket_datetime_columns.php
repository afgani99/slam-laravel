<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dateTime('started_at')->change();
            $table->dateTime('finished_at')->nullable()->change();
            $table->dateTime('closed_at')->nullable()->change();
        });

        Schema::table('ticket_pending_intervals', function (Blueprint $table): void {
            $table->dateTime('started_at')->change();
            $table->dateTime('ended_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->timestamp('started_at')->change();
            $table->timestamp('finished_at')->nullable()->change();
            $table->timestamp('closed_at')->nullable()->change();
        });

        Schema::table('ticket_pending_intervals', function (Blueprint $table): void {
            $table->timestamp('started_at')->change();
            $table->timestamp('ended_at')->nullable()->change();
        });
    }
};
