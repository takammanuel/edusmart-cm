<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_progressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('content');
            $table->timestamps();

            $table->index(['classroom_id', 'subject_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_progressions');
    }
};
