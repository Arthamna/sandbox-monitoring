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
        Schema::create('ctf_match_players', function (Blueprint $table) {
            $table->uuid('ctf_match_id');
            $table->uuid('user_id');
            $table->text('side');
            $table->integer('score')->default(0);
            $table->timestamp('created_at')->useCurrent();
        
            $table->primary(['ctf_match_id', 'user_id']);
        
            $table->foreign('ctf_match_id')->references('id')->on('ctf_matches')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sandbox_events');
        Schema::dropIfExists('sandboxes');
    }
};
