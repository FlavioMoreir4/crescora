<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'slug']);
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('label');
            $table->string('placeholder')->nullable();
            $table->json('options')->nullable();
            $table->json('rules')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->index(['form_id', 'order']);
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->json('data');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'created_at']);
        });

        Schema::create('form_submission_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('form_fields')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->string('value_type', 50)->nullable();
            $table->timestamps();

            $table->index('form_submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submission_values');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
