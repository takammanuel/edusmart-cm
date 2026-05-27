<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number');
            $table->string('name');
            $table->string('school_year');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['number', 'school_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
