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
        // For MySQL/MariaDB, we can use a raw statement to update the enum
        // If it's SQLite, it might be more complex, but the current DB is mysql
         DB::statement("ALTER TABLE shares MODIFY COLUMN permission ENUM('view', 'download', 'edit') DEFAULT 'view'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE shares MODIFY COLUMN permission ENUM('view', 'edit') DEFAULT 'view'");
    }
};
