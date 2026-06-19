<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add a 'node_name' column to store the Proxmox node identifier
     * (e.g. 'pve', 'pve2').
     */
    public function up(): void
    {
        Schema::table('proxmox_nodes', function (Blueprint $table) {
            $table->text('node_name')->after('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proxmox_nodes', function (Blueprint $table) {
            $table->dropColumn('node_name');
        });
    }
};
