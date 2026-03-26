<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_user_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('shared_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_to')->constrained('users')->onDelete('cascade');
            $table->enum('permission', ['view', 'download'])->default('view');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_user_shares');
    }
};
