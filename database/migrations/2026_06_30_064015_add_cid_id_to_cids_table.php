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
            $table->string('cid_is')->nullable()->after('cid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cids', function (Blueprint $table) {
            $table->dropColumn('cid_is');
        });
    }
};
