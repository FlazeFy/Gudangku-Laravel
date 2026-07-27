<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('inventory_name', 75);
            $table->string('inventory_category', 75);
            $table->string('inventory_desc', 255)->nullable();
            $table->string('inventory_merk', 75)->nullable();
            $table->string('inventory_room', 36);
            $table->string('inventory_storage', 36)->nullable();
            $table->string('inventory_rack', 36)->nullable();
            $table->integer('inventory_price')->length(9);
            $table->string('inventory_image', 500)->nullable();
            $table->string('inventory_unit', 36);
            $table->integer('inventory_vol')->length(6)->nullable();
            $table->string('inventory_capacity_unit', 36)->nullable();
            $table->integer('inventory_capacity_vol')->length(6)->nullable();
            $table->string('inventory_color', 16)->nullable();
            $table->boolean('is_favorite');
            $table->boolean('is_reminder');

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);
            $table->dateTime('updated_at', $precision = 0)->nullable();
            $table->dateTime('deleted_at', $precision = 0)->nullable();

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('inventory_category')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
            $table->foreign('inventory_unit')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
            $table->foreign('inventory_capacity_unit')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
            $table->foreign('inventory_room')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
