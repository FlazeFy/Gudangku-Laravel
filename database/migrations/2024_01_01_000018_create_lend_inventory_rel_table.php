<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lend_inventory_rel', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('borrower_name',36);

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->dateTime('returned_at', $precision = 0)->nullable();
            $table->uuid('lend_id');
            $table->uuid('inventory_id');

            // References
            $table->foreign('lend_id')->references('id')->on('lend')->onDelete('cascade');
            $table->foreign('inventory_id')->references('id')->on('inventory')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lend_inventory_rel');
    }
};
