<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('PT. Puskomedia Indonesia Kreatif');
            $table->text('company_subtitle')->nullable();
            $table->string('company_logo')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'company_name' => 'PT. Puskomedia Indonesia Kreatif',
            'company_subtitle' => "Internet Service Provider & Pengembangan Aplikasi\nTeknologi untuk masa depan bisnis Indonesia",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
