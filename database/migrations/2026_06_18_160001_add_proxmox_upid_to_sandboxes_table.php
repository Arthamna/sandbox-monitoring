<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add a nullable 'proxmox_upid' column to store the Proxmox UPID string
     * used for tracking asynchronous tasks.
     */
    public function up(): void
    {
        Schema::table('sandboxes', function (Blueprint $table) {
            $table->text('proxmox_upid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sandboxes', function (Blueprint $table) {
            $table->dropColumn('proxmox_upid');
        });
    }
};
