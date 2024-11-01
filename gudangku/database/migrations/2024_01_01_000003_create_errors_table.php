<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('errors', function (Blueprint $table) {
            $table->bigIncrements('id')->length(20);
            $table->text('message');
            $table->text('stack_trace');
            $table->string('file', 255);
            $table->integer('line')->length(11)->unsigned();
            $table->string('faced_by', 36)->nullable();
            $table->boolean('is_fixed');

            // Props
            $table->timestamp('created_at', $precision = 0);

            // References
            $table->foreign('faced_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('errors');
    }
};
