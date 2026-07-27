<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_mark', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reminder_id', 36);
            $table->dateTime('last_execute', $precision = 0);

            // References
            $table->foreign('reminder_id')->references('id')->on('reminder')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_mark');
    }
};
