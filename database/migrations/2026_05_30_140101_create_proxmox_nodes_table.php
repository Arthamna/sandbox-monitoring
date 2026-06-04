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
        Schema::create('proxmox_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('username');
            $table->text('api_url');
            $table->text('status');
            $table->integer('weight')->default(100);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proxmox_nodes');
    }
};
