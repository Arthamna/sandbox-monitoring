export interface SandboxOwner {
    id: string;
    username: string;
    role: string;
}

export interface SandboxNode {
    id: string;
    node_name: string;
    api_url: string;
    status: string;
    weight: number;
    capacity: number;
}

// export interface SandboxResponse {
//   success: boolean;
//   message: string;
//   data: Sandbox[];
// }

export interface Sandbox {
    id: string;
    owner_user_id: string;
    proxmox_node_id: string;
    kind: 'training' | 'ctf';
    type: string;
    status: 'queued' | 'active' | 'failed' | 'stopped';
    vmid: number | null;
    ip_address: string | null;
    config: Record<string, any>;
    proxmox_upid: string | null;
    proxmox_config?: Record<string, any> | null;
    created_at: string;
    started_at: string | null;
    stopped_at: string | null;
    owner?: SandboxOwner;
    node?: SandboxNode;
}

export interface CtfLog {
    id: number;
    level: 'info' | 'warning' | 'error' | 'debug';
    source: string;
    message: string;
    context: Record<string, any>;
    created_at: string;
}

export interface CreateSandboxPayload {
    owner_user_id: string;
    kind: 'training' | 'ctf';
    type: 'lxc' | 'qemu';
    config: {
        image: string;
        ram?: number;
        cpu?: number;
        storage?: string;
        disk?: number;
        features?: string;
        virtualization?: boolean;
    };
}
