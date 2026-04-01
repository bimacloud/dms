<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('document_id');
            $table->string('token')->unique();
            $table->timestamp('expired_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('files')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_tokens');
    }
};
