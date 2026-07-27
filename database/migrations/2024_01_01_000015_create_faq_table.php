<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('faq_question', 144);
            $table->string('faq_answer', 255)->nullable();
            $table->boolean('is_show');

            // Props
            $table->dateTime('created_at', $precision = 0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq');
    }
};
