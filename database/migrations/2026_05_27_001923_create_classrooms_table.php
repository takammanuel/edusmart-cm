<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('specialty', 100);
            $table->string('level', 100);
            $table->timestamps();
            $table->unique(['name', 'level', 'specialty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
