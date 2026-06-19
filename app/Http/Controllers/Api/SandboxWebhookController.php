<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Sandbox;
use Illuminate\Http\Request;

class SandboxWebhookController extends Controller
{
    /**
     * Handle Proxmox task completion webhook.
     *
     * Expected payload:
     * {
     *   "sandbox_id": "uuid",
     *   "status": "active" | "failed",
     *   "ip_address": "10.0.0.x" (optional),
     *   "message": "..."
     * }
     */
    public function taskComplete(Request $request)
    {
        // Validate webhook secret
        $secret = config('services.proxmox.webhook_secret');
        if ($secret && $request->header('X-Webhook-Secret') !== $secret) {
            return ApiResponse::error('Unauthorized webhook request.', 401);
        }

        $validated = $request->validate([
            'sandbox_id' => ['required', 'uuid', 'exists:sandboxes,id'],
            'status'     => ['required', 'in:active,failed'],
            'ip_address' => ['sometimes', 'nullable', 'string'],
            'message'    => ['sometimes', 'nullable', 'string'],
        ]);

        $sandbox = Sandbox::findOrFail($validated['sandbox_id']);

        if ($validated['status'] === 'active') {
            $sandbox->update([
                'status'     => 'active',
                'ip_address' => $validated['ip_address'] ?? $sandbox->ip_address,
                'started_at' => $sandbox->started_at ?? now(),
            ]);
        } else {
            $sandbox->update([
                'status' => 'failed',
            ]);
        }

        return ApiResponse::success(
            'Webhook processed successfully.',
            ['sandbox_id' => $sandbox->id, 'status' => $sandbox->status]
        );
    }
}
