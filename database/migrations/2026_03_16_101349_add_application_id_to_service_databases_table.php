<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds application_id to service_databases so that database services
     * inside Git-based Docker Compose applications can be tracked and
     * backed up, matching the existing behaviour for Service-based composes.
     */
    public function up(): void
    {
        Schema::table('service_databases', function (Blueprint $table) {
            // Nullable so existing rows (owned by a Service) are unaffected.
            $table->unsignedBigInteger('application_id')->nullable()->after('service_id');
            // service_id is now optional – either service_id OR application_id is set.
            $table->unsignedBigInteger('service_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_databases', function (Blueprint $table) {
            $table->dropColumn('application_id');
            $table->unsignedBigInteger('service_id')->nullable(false)->change();
        });
    }
};
