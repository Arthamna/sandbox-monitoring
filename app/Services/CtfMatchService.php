<?php

namespace App\Services;

use App\Models\CtfMatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CtfMatchService
{
    public function create(array $data): CtfMatch
    {
        return CtfMatch::create([
            'challenge_key' => $data['challenge_key'],
            'mode' => $data['mode'] ?? 'vs',
            'status' => 'waiting',
            'winner_user_id' => null,
            'started_at' => null,
            'ended_at' => null,
        ]);
    }

    public function join(CtfMatch $match, array $data): CtfMatch
    {
        return DB::transaction(function () use ($match, $data) {
            $this->ensureMatchIsOpen($match);

            $userId = $data['user_id'];

            $alreadyJoined = DB::table('ctf_match_players')
                ->where('ctf_match_id', $match->id)
                ->where('user_id', $userId)
                ->exists();

            if (! $alreadyJoined) {
                $side = $data['side'] ?? $this->nextAvailableSide($match->id);

                $sideTaken = DB::table('ctf_match_players')
                    ->where('ctf_match_id', $match->id)
                    ->where('side', $side)
                    ->exists();

                if ($sideTaken) {
                    throw ValidationException::withMessages([
                        'side' => 'Side tersebut sudah dipakai.',
                    ]);
                }

                DB::table('ctf_match_players')->insert([
                    'ctf_match_id' => $match->id,
                    'user_id' => $userId,
                    'side' => $side,
                    'score' => 0,
                    'created_at' => now(),
                ]);
            }

            if ($match->status === 'waiting') {
                $match->update([
                    'status' => 'running',
                    'started_at' => $match->started_at ?? now(),
                ]);
            }

            return $this->hydrate($match);
        });
    }

    public function submit(CtfMatch $match, array $data): CtfMatch
    {
        return DB::transaction(function () use ($match, $data) {
            $this->ensureMatchIsOpen($match);

            $updated = DB::table('ctf_match_players')
                ->where('ctf_match_id', $match->id)
                ->where('user_id', $data['user_id'])
                ->update([
                    'score' => DB::raw('score + ' . (int) $data['score_delta']),
                ]);

            //  trace if the user already join match or not
            if ($updated === 0) {
                throw ValidationException::withMessages([
                    'user_id' => 'User belum join match ini.',
                ]);
            }

            // dd($data);

            if ($match->status === 'waiting') {
                $match->update([
                    'status' => 'running',
                    'started_at' => $match->started_at ?? now(),
                ]);
            }

            return $this->hydrate($match);
        });
    }

    public function scoreboard(CtfMatch $match): CtfMatch
    {
        return $this->hydrate($match);
    }

    private function hydrate(CtfMatch $match): CtfMatch
    {
        $match->load(['winner', 'players']);

        $match->setRelation(
            'players',
            $match->players->sort(function ($a, $b) {
                $scoreCompare = (int) ($b->pivot->score ?? 0) <=> (int) ($a->pivot->score ?? 0);

                if ($scoreCompare !== 0) {
                    return $scoreCompare;
                }

                return strcmp((string) $a->username, (string) $b->username);
            })->values()
        );

        return $match;
    }

    private function ensureMatchIsOpen(CtfMatch $match): void
    {
        if ($match->status === 'finished') {
            throw ValidationException::withMessages([
                'match' => 'Match sudah selesai.',
            ]);
        }
    }

    private function nextAvailableSide(string $matchId): string
    {
        $used = DB::table('ctf_match_players')
            ->where('ctf_match_id', $matchId)
            ->pluck('side')
            ->all();

        if (! in_array('A', $used, true)) {
            return 'A';
        }

        if (! in_array('B', $used, true)) {
            return 'B';
        }

        throw ValidationException::withMessages([
            'side' => 'Kedua side sudah terisi.',
        ]);
    }
}