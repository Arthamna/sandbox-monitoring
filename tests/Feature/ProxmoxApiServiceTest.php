<?php

namespace Tests\Feature;

use App\Services\ProxmoxApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProxmoxApiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        config([
            'services.proxmox.base_url' => 'https://192.168.83.129:8006',
            'services.proxmox.username' => 'root@pam',
            'services.proxmox.password' => 'secret',
        ]);
        
        Cache::clear();
    }

    public function test_authenticate_fetches_and_caches_tokens(): void
    {
        Http::fake([
            'https://127.0.0.1:8006/api2/json/access/ticket' => Http::response([
                'data' => [
                    'ticket' => 'fake-ticket-123',
                    'CSRFPreventionToken' => 'fake-csrf-456',
                ]
            ], 200),
        ]);

        $service = app(ProxmoxApiService::class);
        $service->authenticate();

        $this->assertEquals('fake-ticket-123', Cache::get('proxmox_api_ticket'));
        $this->assertEquals('fake-csrf-456', Cache::get('proxmox_api_csrf'));
    }

    public function test_client_returns_configured_pending_request(): void
    {
        // Pre-fill cache so it doesn't try to authenticate
        Cache::put('proxmox_api_ticket', 'cached-ticket');
        Cache::put('proxmox_api_csrf', 'cached-csrf');

        $service = app(ProxmoxApiService::class);
        $client = $service->client();

        // PendingRequest options can be inspected via reflection or by making a fake request
        Http::fake([
            'https://127.0.0.1:8006/api2/json/nodes' => Http::response([], 200),
        ]);

        $response = $client->get('/api2/json/nodes');
        
        // Assert the request was made with the correct headers and base URL
        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            return $request->hasHeader('CSRFPreventionToken', 'cached-csrf') &&
                   $request->url() === 'https://127.0.0.1:8006/api2/json/nodes';
        });
    }

    public function test_client_authenticates_if_cache_missing(): void
    {
        Http::fake([
            'https://127.0.0.1:8006/api2/json/access/ticket' => Http::response([
                'data' => [
                    'ticket' => 'new-ticket',
                    'CSRFPreventionToken' => 'new-csrf',
                ]
            ], 200),
            'https://127.0.0.1:8006/api2/json/nodes' => Http::response([], 200),
        ]);

        $this->assertNull(Cache::get('proxmox_api_ticket'));

        $service = app(ProxmoxApiService::class);
        $client = $service->client();
        $client->get('/api2/json/nodes');

        $this->assertEquals('new-ticket', Cache::get('proxmox_api_ticket'));
        
        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            if ($request->url() === 'https://127.0.0.1:8006/api2/json/nodes') {
                return $request->hasHeader('CSRFPreventionToken', 'new-csrf');
            }
            return true;
        });
    }
}
