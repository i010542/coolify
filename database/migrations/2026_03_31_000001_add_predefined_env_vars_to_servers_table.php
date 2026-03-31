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
        if (! Schema::hasColumn('servers', 'predefined_env_vars')) {
            Schema::table('servers', function (Blueprint $table) {
                $table->json('predefined_env_vars')->nullable()->after('server_metadata');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('servers', 'predefined_env_vars')) {
            Schema::table('servers', function (Blueprint $table) {
                $table->dropColumn('predefined_env_vars');
            });
        }
    }
};
