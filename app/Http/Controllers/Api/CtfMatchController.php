<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JoinCtfMatchRequest;
use App\Http\Requests\StoreCtfMatchRequest;
use App\Http\Requests\SubmitCtfMatchRequest;
use App\Http\Resources\CtfMatchResource;
use App\Http\Responses\CtfMatchResponse;
use App\Models\CtfMatch;
use App\Services\CtfMatchService;
use Illuminate\Http\Request;

class CtfMatchController extends Controller
{
    public function store(StoreCtfMatchRequest $request, CtfMatchService $service)
    {
        $match = $service->create($request->validated());

        return CtfMatchResponse::created(
            CtfMatchResource::make($match)
        );
    }

    public function join(JoinCtfMatchRequest $request, CtfMatch $match, CtfMatchService $service)
    {
        $match = $service->join($match, $request->validated());

        return CtfMatchResponse::joined(
            CtfMatchResource::make($match)
        );
    }

    public function submit(SubmitCtfMatchRequest $request, CtfMatch $match, CtfMatchService $service)
    {
        
        $match = $service->submit($match, $request->validated());

        return CtfMatchResponse::submitted(
            CtfMatchResource::make($match)
        );
    }

    public function scoreboard(CtfMatch $match, CtfMatchService $service)
    {
        $match = $service->scoreboard($match);

        return CtfMatchResponse::scoreboard(
            CtfMatchResource::make($match)
        );
    }
}
