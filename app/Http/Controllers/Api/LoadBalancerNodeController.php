<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProxmoxNodeRequest;
use App\Http\Responses\LoadBalancerResponse;
use App\Models\ProxmoxNode;
use App\Services\LoadBalancerService;

class LoadBalancerNodeController extends Controller
{
    public function index(LoadBalancerService $service)
    {
        return LoadBalancerResponse::nodes($service->nodes());
    }

    public function update(UpdateProxmoxNodeRequest $request, ProxmoxNode $node)
    {
        $node->fill($request->validated())->save();

        return LoadBalancerResponse::nodeUpdated($node->refresh());
    }
}