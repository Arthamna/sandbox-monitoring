<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProxmoxApiService
{
    private const CACHE_KEY_TICKET = 'proxmox_api_ticket';
    private const CACHE_KEY_CSRF = 'proxmox_api_csrf';
    private const CACHE_TTL = 6900; // 1 hour 55 minutes in seconds

    /**
     * Authenticate against Proxmox API and cache the ticket and CSRF token.
     * 
     * @return void
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Exception
     */
    public function authenticate(): void
    {
        $baseUrl = config('services.proxmox.base_url');
        $username = config('services.proxmox.username');
        $password = config('services.proxmox.password');

        $response = Http::withoutVerifying()
            ->asForm()
            ->post("{$baseUrl}/api2/json/access/ticket", [
                'username' => $username,
                'password' => $password,
            ])->throw()->json();

        $ticket = $response['data']['ticket'] ?? null;
        $csrfToken = $response['data']['CSRFPreventionToken'] ?? null;

        if (!$ticket || !$csrfToken) {
            throw new \Exception('Failed to retrieve Proxmox access ticket or CSRF token.');
        }

        Cache::put(self::CACHE_KEY_TICKET, $ticket, self::CACHE_TTL);
        Cache::put(self::CACHE_KEY_CSRF, $csrfToken, self::CACHE_TTL);
    }

    /**
     * Get a pre-configured HTTP client for Proxmox API requests.
     *
     * @return PendingRequest
     */
    public function client(): PendingRequest
    {
        if (!Cache::has(self::CACHE_KEY_TICKET) || !Cache::has(self::CACHE_KEY_CSRF)) {
            $this->authenticate();
        }

        $ticket = Cache::get(self::CACHE_KEY_TICKET);
        $csrfToken = Cache::get(self::CACHE_KEY_CSRF);

        $baseUrl = config('services.proxmox.base_url');
        $host = parse_url($baseUrl, PHP_URL_HOST) ?? '127.0.0.1';

        return Http::withoutVerifying()
            ->baseUrl($baseUrl)
            ->withHeaders([
                'CSRFPreventionToken' => $csrfToken,
            ])
            ->withCookies([
                'PVEAuthCookie' => $ticket,
            ], $host);
    }

    /**
     * Get the next available VMID from the Proxmox cluster.
     *
     * @return int
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getNextVmid(): int
    {
        $response = $this->client()
            ->get('/api2/json/cluster/nextid')
            ->throw()
            ->json();

        return (int) $response['data'];
    }

    /**
     * Create a QEMU virtual machine on the specified node.
     *
     * @param  string  $node   The target node name.
     * @param  int     $vmid   The VMID to assign.
     * @param  array   $params Additional creation parameters.
     * @return string  The UPID of the creation task.
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createQemu(string $node, int $vmid, array $params = []): string
    {
        $response = $this->client()
            ->post("/api2/json/nodes/{$node}/qemu", array_merge(['vmid' => $vmid], $params))
            ->throw()
            ->json();

        return $response['data'];
    }

    /**
     * Create an LXC container on the specified node.
     *
     * @param  string  $node   The target node name.
     * @param  int     $vmid   The VMID to assign.
     * @param  array   $params Additional creation parameters.
     * @return string  The UPID of the creation task.
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createLxc(string $node, int $vmid, array $params = []): string
    {
        $response = $this->client()
            ->post("/api2/json/nodes/{$node}/lxc", array_merge(['vmid' => $vmid], $params))
            ->throw()
            ->json();

        return $response['data'];
    }

    /**
     * Start a VM or container on the specified node.
     *
     * @param  string  $node  The target node name.
     * @param  int     $vmid  The VMID of the VM/container.
     * @param  string  $type  The virtualization type ('qemu' or 'lxc').
     * @return string  The UPID of the start task.
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function startVm(string $node, int $vmid, string $type = 'qemu'): string
    {
        $response = $this->client()
            ->asForm()
            ->post("/api2/json/nodes/{$node}/{$type}/{$vmid}/status/start", [])
            ->throw()
            ->json();

        return $response['data'];
    }

    /**
     * Get the configuration of a VM or container.
     *
     * @param  string  $node  The target node name.
     * @param  int     $vmid  The VMID of the VM/container.
     * @param  string  $type  The virtualization type ('qemu' or 'lxc').
     * @return array   The VM/container configuration data.
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getVmConfig(string $node, int $vmid, string $type = 'qemu'): array
    {
        $response = $this->client()
            ->get("/api2/json/nodes/{$node}/{$type}/{$vmid}/config")
            ->throw()
            ->json();

        return $response['data'];
    }

    /**
     * List available CT/VM templates from a node's storage.
     *
     * @param  string  $node        The target node name.
     * @param  string  $storage     The storage ID (default: 'local').
     * @param  string  $contentType The content type to search ('vztmpl' or 'iso').
     * @return array   List of available templates with their volid.
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getAvailableTemplates(string $node, string $storage = 'local', string $contentType = 'vztmpl'): array
    {
        $response = $this->client()
            ->get("/api2/json/nodes/{$node}/storage/{$storage}/content", [
                'content' => $contentType,
            ])
            ->throw()
            ->json();

        return $response['data'] ?? [];
    }

    /**
     * Resolve a short image name (e.g. 'ubuntu-22.04') to a full Proxmox
     * volume reference (e.g. 'local:vztmpl/ubuntu-22.04-standard_22.04-1_amd64.tar.zst' or 'local:iso/ubuntu.iso').
     *
     * If the image already contains a colon (volume reference format), it is returned as-is.
     *
     * @param  string  $node        The target node name.
     * @param  string  $image       Short image name or full volume reference.
     * @param  string  $storage     The storage ID to search in (default: 'local').
     * @param  string  $contentType The content type to search ('vztmpl' or 'iso').
     * @return string  The full volume reference.
     * @throws \Exception If no matching template is found.
     */
    public function resolveTemplate(string $node, string $image, string $storage = 'local', string $contentType = 'vztmpl'): string
    {
        // Already a full volume reference (e.g. "local:vztmpl/..." or "local:iso/...")
        if (str_contains($image, ':')) {
            return $image;
        }

        $templates = $this->getAvailableTemplates($node, $storage, $contentType);

        // Normalize: split input into keywords by spaces, dots, dashes, underscores
        $keywords = preg_split('/[\s.\-_]+/', strtolower(trim($image)));
        $keywords = array_filter($keywords); // remove empties

        $bestMatch = null;
        $bestScore = 0;

        foreach ($templates as $template) {
            $volid = $template['volid'] ?? '';
            $volLower = strtolower($volid);

            // Count how many keywords match
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($volLower, $keyword)) {
                    $score++;
                }
            }

            // All keywords must match, then pick the one with highest score
            // (or shortest name as tiebreaker for specificity)
            if ($score === count($keywords) && ($score > $bestScore || ($score === $bestScore && $bestMatch && strlen($volid) < strlen($bestMatch)))) {
                $bestMatch = $volid;
                $bestScore = $score;
            }
        }

        if ($bestMatch) {
            return $bestMatch;
        }

        $available = implode(', ', array_column($templates, 'volid'));
        throw new \Exception(
            "Template/ISO '{$image}' not found on node '{$node}' storage '{$storage}' with content type '{$contentType}'. "
            . "Available files: [{$available}]"
        );
    }
}
