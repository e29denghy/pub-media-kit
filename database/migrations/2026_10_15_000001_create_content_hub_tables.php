<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->string('body_format')->default('markdown');
            $table->json('tags');
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('draft_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('alt')->nullable();
            $table->timestamps();
        });

        Schema::create('publish_combinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('platforms');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('publish_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publish_combination_id')->nullable()->constrained()->nullOnDelete();
            $table->json('platforms');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('publish_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publish_job_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('mode');
            $table->string('status');
            $table->text('summary');
            $table->json('plan');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['publish_job_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_targets');
        Schema::dropIfExists('publish_jobs');
        Schema::dropIfExists('publish_combinations');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('drafts');
    }
};
