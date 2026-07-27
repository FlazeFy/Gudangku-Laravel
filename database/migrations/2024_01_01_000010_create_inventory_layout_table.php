<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_layout', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('inventory_room', 36);
            $table->string('inventory_storage', 36);
            $table->string('storage_desc', 255)->nullable();
            $table->string('layout', 36)->nullable();

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('inventory_room')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_layout');
    }
};
