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
        Schema::table('cids', function (Blueprint $table) {
            $table->boolean('is_dismantled')->default(false)->after('sla_percentage');
            $table->timestamp('dismantled_at')->nullable()->after('is_dismantled');
            $table->foreignId('dismantled_by')->nullable()->after('dismantled_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cids', function (Blueprint $table) {
            $table->dropForeign(['dismantled_by']);
            $table->dropColumn(['is_dismantled', 'dismantled_at', 'dismantled_by']);
        });
    }
};
