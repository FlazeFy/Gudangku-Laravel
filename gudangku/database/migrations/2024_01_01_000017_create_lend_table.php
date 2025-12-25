<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lend', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->text('lend_qr_url')->nullable();
            $table->integer('qr_period')->length(2);
            $table->string('lend_desc', 500)->nullable();
            $table->string('lend_status', 36);
            $table->boolean('is_finished');

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lend');
    }
};
