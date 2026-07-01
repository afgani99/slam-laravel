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
        Schema::table('gamas', function (Blueprint $table) {
            $table->dropColumn(['pending_started_at', 'pending_ended_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gamas', function (Blueprint $table) {
            $table->dateTime('pending_started_at')->nullable()->after('started_at');
            $table->dateTime('pending_ended_at')->nullable()->after('pending_started_at');
        });
    }
};
