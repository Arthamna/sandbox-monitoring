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
        Schema::create('sandbox_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('sandbox_id');
            $table->uuid('actor_user_id')->nullable();
            $table->text('event_type');
            $table->text('payload');
            $table->timestamp('created_at')->useCurrent();
        
            $table->foreign('sandbox_id')->references('id')->on('sandboxes')->cascadeOnDelete();
            $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();
        
            $table->index(['sandbox_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sandbox_events');
    }
};
