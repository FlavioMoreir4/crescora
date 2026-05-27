<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_resource_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('resource_type', 30);
            $table->unsignedBigInteger('resource_id');
            $table->string('access_level', 20)->default('view');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['team_id', 'user_id', 'resource_type', 'resource_id'], 'team_resource_accesses_unique');
            $table->index(['team_id', 'user_id', 'resource_type'], 'team_resource_accesses_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_resource_accesses');
    }
};
