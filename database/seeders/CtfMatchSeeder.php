<?php

namespace Database\Seeders;

use App\Models\CtfMatch;
use App\Models\User;
use App\Services\CtfLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CtfMatchSeeder extends Seeder
{
    public function run(): void
    {
        $players = User::where('role', 'player')->get();

        if ($players->count() < 2) {
            CtfLogger::warning('seeder:CtfMatchSeeder', 'Skipped – need at least 2 players.');
            return;
        }

        // Match 1: waiting (belum ada player join)
        $match1 = CtfMatch::create([
            'id' => Str::uuid()->toString(),
            'challenge_key' => 'web-sql-injection-101',
            'mode' => 'vs',
            'status' => 'waiting',
            'winner_user_id' => null,
            'started_at' => null,
            'ended_at' => null,
        ]);

        // Match 2: running (2 player sudah join, ada skor)
        $match2 = CtfMatch::create([
            'id' => Str::uuid()->toString(),
            'challenge_key' => 'pwn-buffer-overflow-201',
            'mode' => 'vs',
            'status' => 'running',
            'winner_user_id' => null,
            'started_at' => now()->subMinutes(15),
            'ended_at' => null,
        ]);

        // Player alpha join side A
        DB::table('ctf_match_players')->insert([
            'ctf_match_id' => $match2->id,
            'user_id' => $players[0]->id,
            'side' => 'A',
            'score' => 150,
            'created_at' => now()->subMinutes(14),
        ]);

        // Player bravo join side B
        DB::table('ctf_match_players')->insert([
            'ctf_match_id' => $match2->id,
            'user_id' => $players[1]->id,
            'side' => 'B',
            'score' => 100,
            'created_at' => now()->subMinutes(13),
        ]);

        CtfLogger::info('seeder:CtfMatchSeeder', 'Seeded 2 CTF matches.', [
            'match_waiting' => $match1->id,
            'match_running' => $match2->id,
            'players_in_running' => [$players[0]->username, $players[1]->username],
        ]);
    }
}
