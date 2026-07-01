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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('cid_id')->constrained('cids')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('vendor_ticket_number')->nullable();
            $table->string('case_type');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('rfo_action')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('vendor_ticket_number');
            $table->index('case_type');
            $table->index('status');
            $table->index('started_at');
            $table->index('finished_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
