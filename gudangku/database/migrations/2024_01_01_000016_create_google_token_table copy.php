<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->text('access_token');

            // Props
            $table->timestamp('created_at');
            $table->timestamp('expiry');
            $table->uuid('created_by');

            // References
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_tokens');
    }
};
