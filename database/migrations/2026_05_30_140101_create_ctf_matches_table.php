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
        Schema::create('ctf_matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('challenge_key');
            $table->text('mode')->default('vs');
            $table->text('status');
            $table->uuid('winner_user_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
        
            $table->foreign('winner_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctf_matches');
    }
};
