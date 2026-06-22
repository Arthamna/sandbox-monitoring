<?php

namespace App\Services;

use App\Models\Sandbox;
use Illuminate\Support\Facades\DB;

class SandboxProvisionService
{
    public function __construct(
        protected LoadBalancerService $loadBalancerService,
        protected ProxmoxApiService $proxmoxApiService
    ) {
    }

    /**
     * Provision a new sandbox by selecting a node, allocating a VMID,
     * creating the VM/container on Proxmox, and persisting the record.
     *
     * @param  array  $data  Provisioning payload with keys: owner_user_id, kind, config, and optional type.
     * @return Sandbox
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function provision(array $data): Sandbox
    {
        return DB::transaction(function () use ($data) {
            $node = $this->loadBalancerService->bestNode();
            $vmid = $this->proxmoxApiService->getNextVmid();

            $config = $data['config'] ?? [];
            $type = $data['type'] ?? 'lxc';

            // LXC uses 'hostname', QEMU uses 'name' per Proxmox API schema
            $nameKey = $type === 'lxc' ? 'hostname' : 'name';
            $proxmoxParams = [
                $nameKey => "sandbox-{$vmid}",
            ];

            $virtualizationEnabled = isset($config['virtualization']) ? (bool) $config['virtualization'] : true;

            // LXC needs a storage that supports container rootfs (e.g. local-lvm)
            // 'local' only stores templates/ISOs/backups, not container directories
            if ($type === 'lxc') {
                $storage = $config['storage'] ?? 'local-lvm';
                $disk = $config['disk'] ?? 8; // default 8GB
                $proxmoxParams['storage'] = $storage;
                $proxmoxParams['rootfs'] = "{$storage}:{$disk}";
                
                if (isset($config['features'])) {
                    $proxmoxParams['features'] = $config['features'];
                } elseif ($virtualizationEnabled) {
                    $proxmoxParams['features'] = 'nesting=1'; // provide nesting as default ct doesn't enable it
                }
            } else {
                // QEMU uses KVM
                $proxmoxParams['kvm'] = $virtualizationEnabled ? 1 : 0;
            }

            if (isset($config['ram'])) {
                $proxmoxParams['memory'] = $config['ram'];
            }

            if (isset($config['cpu'])) {
                $proxmoxParams['cores'] = $config['cpu'];
            }

            if (isset($config['image'])) {
                if ($type === 'lxc') {
                    // Resolve short name (e.g. 'ubuntu-22.04') to full volume reference
                    // (e.g. 'local:vztmpl/ubuntu-22.04-standard_22.04-1_amd64.tar.zst')
                    $proxmoxParams['ostemplate'] = $this->proxmoxApiService->resolveTemplate(
                        $node->node_name,
                        $config['image']
                    );
                } else {
                    // Resolve short name to full ISO volume reference
                    // (e.g. 'local:iso/ubuntu-22.04.iso')
                    $proxmoxParams['cdrom'] = $this->proxmoxApiService->resolveTemplate(
                        $node->node_name,
                        $config['image'],
                        'local',
                        'iso'
                    );
                }
            }

            $upid = $type === 'qemu'
                ? $this->proxmoxApiService->createQemu($node->node_name, $vmid, $proxmoxParams)
                : $this->proxmoxApiService->createLxc($node->node_name, $vmid, $proxmoxParams);

            return Sandbox::create([
                'owner_user_id'   => $data['owner_user_id'],
                'proxmox_node_id' => $node->id,
                'kind'            => $data['kind'],
                'type'            => $type,
                'status'          => 'queued',
                'vmid'            => $vmid,
                'proxmox_upid'    => $upid,
                'config'          => $config,
            ]);
        });
    }
}