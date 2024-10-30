<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dictionary', function (Blueprint $table) {
            $table->increments('id');
            $table->string('dictionary_type', 36);
            $table->string('dictionary_name', 75)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dictionary');
    }
};
