<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RebalanceNodesRequest;
use App\Http\Responses\LoadBalancerResponse;
use App\Services\LoadBalancerService;

class LoadBalancerRebalanceController extends Controller
{
    public function store(
        RebalanceNodesRequest $request, 
        LoadBalancerService $service
    ) {

        return LoadBalancerResponse::rebalance($service->rebalanceQueuedSandboxes((bool) $request->validated()['dry_run'] ?? false));
    }
}