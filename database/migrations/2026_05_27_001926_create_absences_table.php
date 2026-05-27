<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('hours')->default(1);
            $table->boolean('is_justified')->default(false);
            $table->string('reason')->nullable();
            $table->uuid('client_uuid')->nullable()->unique();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
