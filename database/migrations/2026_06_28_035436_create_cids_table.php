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
        Schema::create('cids', function (Blueprint $table) {
            $table->id();
            $table->string('cid')->unique();
            $table->string('vendor_name');
            $table->string('customer_name');
            $table->string('service');
            $table->decimal('sla_percentage', 5, 2)->default(99.00);
            $table->timestamps();

            $table->index('vendor_name');
            $table->index('customer_name');
            $table->index('service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cids');
    }
};
