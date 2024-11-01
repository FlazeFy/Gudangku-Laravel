<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type',25);
            $table->string('status',10);
            $table->text('payload');

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('faced_by',36)->nullable();

            // References
            $table->foreign('faced_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};
