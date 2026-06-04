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
        Schema::create('sandboxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_user_id');
            $table->uuid('proxmox_node_id')->nullable();
            $table->text('kind');
            $table->text('status');
            $table->integer('vmid')->nullable();
            $table->text('ip_address')->nullable();
            $table->jsonb('config')->default(DB::raw("'{}'::jsonb"));

            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();

            $table->foreign('owner_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('proxmox_node_id')->references('id')->on('proxmox_nodes')->nullOnDelete();

            $table->index(['status', 'proxmox_node_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sandboxes');
    }
};
