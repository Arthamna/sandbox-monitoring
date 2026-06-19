<?php

use App\Http\Controllers\Api\CtfMatchController;
use App\Http\Controllers\Api\LoadBalancerNodeController;
use App\Http\Controllers\Api\LoadBalancerRebalanceController;
use App\Http\Controllers\Api\SandboxController;
use App\Http\Controllers\Api\SandboxEventController;
use App\Http\Controllers\Api\SandboxWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

// Admin  
Route::middleware(['auth:sanctum', 'admin'])->prefix('sandboxes')->group(function () {
    
    // create
    Route::post('/',                      [SandboxController::class, 'store']);
    
    // activate
    Route::post('/{sandbox}/activate',    [SandboxController::class, 'activate']);
    Route::post('/{sandbox}/activate/{type}', [SandboxController::class, 'activate'])
    ->whereIn('type', ['qemu', 'lxc']);
    
    // show
    Route::get('/{sandbox}',              [SandboxController::class, 'show']);

    
    Route::post('/{sandbox}/events',      [SandboxEventController::class, 'store']);

    // event-stream, not rest api
    Route::get('/{sandbox}/stream',       [SandboxController::class, 'stream']);
});


// User  
Route::middleware('auth:sanctum')->prefix('load-balancer')->group(function () {
    Route::get('/nodes', [LoadBalancerNodeController::class, 'index']);
    Route::patch('/nodes/{node}', [LoadBalancerNodeController::class, 'update']);
    Route::post('/rebalance', [LoadBalancerRebalanceController::class, 'store']);
});

Route::middleware('auth:sanctum')->prefix('ctf/matches')->group(function () {
    Route::post('/', [CtfMatchController::class, 'store']);
    Route::post('/{match}/join', [CtfMatchController::class, 'join']);
    Route::get('/{match}/scoreboard', [CtfMatchController::class, 'scoreboard']);

    // submit = add-score when flag is true
    Route::post('/{match}/submit', [CtfMatchController::class, 'submit']);
});

Route::prefix('webhooks')->group(function () {
    Route::post('/sandbox/task-complete', [SandboxWebhookController::class, 'taskComplete']);
});

Route::prefix('auth')->middleware('guest')->group(function () {
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});
