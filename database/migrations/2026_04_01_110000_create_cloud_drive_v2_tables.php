<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing tables to start fresh with the new architecture
        Schema::dropIfExists('file_shares');
        Schema::dropIfExists('download_tokens');
        Schema::dropIfExists('file_user_shares');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('folders');

        // 1. Folders Table (UUID Based)
        Schema::create('folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('parent_id')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on('folders')
                ->nullOnDelete();
        });

        // 2. Files Table (UUID Based)
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('folder_id')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('storage_provider_id')->nullable()->constrained('storage_providers')->nullOnDelete();
            $table->string('display_name');
            $table->string('storage_path');
            $table->string('mime_type');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('size');
            $table->string('extension');
            $table->string('disk')->default('s3');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('folder_id')
                ->references('id')
                ->on('folders')
                ->nullOnDelete();
        });

        // 3. Shares Table (Polymorphic)
        Schema::create('shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('shareable_type');
            $table->uuid('shareable_id');
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shared_with_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->enum('permission', ['view', 'edit'])->default('view');
            $table->string('access_token')->unique()->nullable();
            $table->string('password')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['shareable_type', 'shareable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shares');
        Schema::dropIfExists('files');
        Schema::dropIfExists('folders');
    }
};
