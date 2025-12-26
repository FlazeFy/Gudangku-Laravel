<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('report_title', 36   );
            $table->string('report_category', 36);
            $table->string('report_desc', 255)->nullable();
            $table->json('report_image')->nullable();
            $table->boolean('is_reminder');
            $table->dateTime('remind_at', $precision = 0)->nullable();

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);
            $table->dateTime('updated_at', $precision = 0)->nullable();
            $table->dateTime('deleted_at', $precision = 0)->nullable();

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('report_category')->references('dictionary_name')->on('dictionary')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report');
    }
};
