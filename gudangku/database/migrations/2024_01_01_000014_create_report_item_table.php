<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_item', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('inventory_id', 36);
            $table->string('report_id', 36);
            $table->string('item_name', 75);
            $table->string('item_desc', 144)->nullable();
            $table->integer('item_qty')->length(4);
            $table->integer('item_price')->length(9)->nullable();

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('report_id')->references('id')->on('report')->onDelete('cascade');
            $table->foreign('inventory_id')->references('id')->on('inventory')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_item');
    }
};
