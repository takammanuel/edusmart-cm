<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sequence_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 4, 2);
            $table->uuid('client_uuid')->nullable()->unique();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'sequence_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
