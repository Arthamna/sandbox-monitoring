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
        Schema::table('proxmox_nodes', function (Blueprint $table) {
            $table->jsonb('capacity')->default(DB::raw("'{}'::jsonb"));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proxmox_nodes', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });
    }
};
