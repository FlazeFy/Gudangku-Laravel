<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validate_request', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('request_type', 36);
            $table->string('request_context', 75);

            // Props
            $table->dateTime('created_at', $precision = 0);
            $table->string('created_by', 36);

            // Note: This `created_by` does not have a direct relation to the users table, because it can store either a user ID or a username.
            // During registration, `created_by` may be filled with a username even when the user record does not yet exist in the users table.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validate_request');
    }
};
