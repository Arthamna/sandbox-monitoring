import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,  
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { api } from '@/lib/api-client';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import type { CreateSandboxPayload, Sandbox } from '@/types/sandbox';
import { Head } from '@inertiajs/react';
import { ChevronDown, ChevronRight, Loader2, Plus, RefreshCw } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Sandboxes', href: '/admin/sandboxes' },
];

const statusConfig: Record<Sandbox['status'], { label: string; className: string }> = {
    queued: {
        label: 'Queued',
        className: 'bg-amber-500/15 text-amber-400 border-amber-500/25 hover:bg-amber-500/25',
    },
    active: {
        label: 'Active',
        className: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/25 hover:bg-emerald-500/25',
    },
    failed: {
        label: 'Failed',
        className: 'bg-red-500/15 text-red-400 border-red-500/25 hover:bg-red-500/25',
    },
    stopped: {
        label: 'Stopped',
        className: 'bg-neutral-500/15 text-neutral-400 border-neutral-500/25 hover:bg-neutral-500/25',
    },
};

function StatusBadge({ status }: { status: Sandbox['status'] }) {
    const config = statusConfig[status];
    return (
        <Badge variant="outline" className={config.className}>
            <span className="mr-1.5 inline-block h-1.5 w-1.5 rounded-full bg-current" />
            {config.label}
        </Badge>
    );
}

function TableSkeleton() {
    return (
        <div className="space-y-3 p-6">
            {Array.from({ length: 5 }).map((_, i) => (
                <div key={i} className="flex items-center gap-4">
                    <Skeleton className="h-5 w-16" />
                    <Skeleton className="h-5 w-32" />
                    <Skeleton className="h-5 w-20" />
                    <Skeleton className="h-5 w-16" />
                    <Skeleton className="h-5 w-20" />
                    <Skeleton className="h-5 w-24" />
                    <Skeleton className="h-5 w-24" />
                    <Skeleton className="h-5 w-32" />
                </div>
            ))}
        </div>
    );
}

function SandboxDetailPanel({ sandbox }: { sandbox: Sandbox }) {
    return (
        <tr>
            <td colSpan={9} className="border-sidebar-border/70 dark:border-sidebar-border border-t bg-muted/30 px-6 py-4">
                <div className="grid gap-6 md:grid-cols-2">
                    <div className="space-y-3">
                        <h4 className="text-sm font-semibold text-foreground">Sandbox Details</h4>
                        <dl className="space-y-2 text-sm">
                            <div className="flex gap-2">
                                <dt className="min-w-28 text-muted-foreground">ID:</dt>
                                <dd className="font-mono text-xs text-foreground">{sandbox.id}</dd>
                            </div>
                            {sandbox.ip_address && (
                                <div className="flex gap-2">
                                    <dt className="min-w-28 text-muted-foreground">IP Address:</dt>
                                    <dd className="font-mono text-foreground">{sandbox.ip_address}</dd>
                                </div>
                            )}
                            {sandbox.proxmox_upid && (
                                <div className="flex gap-2">
                                    <dt className="min-w-28 text-muted-foreground">UPID:</dt>
                                    <dd className="font-mono text-xs break-all text-foreground">{sandbox.proxmox_upid}</dd>
                                </div>
                            )}
                            {/* <div className="flex gap-2">
                                <dt className="min-w-28 text-muted-foreground">Created At:</dt>
                                <dd className="text-foreground">{new Date(sandbox.created_at).toLocaleString()}</dd>
                            </div> */}
                            {sandbox.started_at && (
                                <div className="flex gap-2">
                                    <dt className="min-w-28 text-muted-foreground">Started At:</dt>
                                    <dd className="text-foreground">{new Date(sandbox.started_at).toLocaleString()}</dd>
                                </div>
                            )}
                            {/* {sandbox.stopped_at && (
                                <div className="flex gap-2">
                                    <dt className="min-w-28 text-muted-foreground">Stopped At:</dt>
                                    <dd className="text-foreground">{new Date(sandbox.stopped_at).toLocaleString()}</dd>
                                </div>
                            )} */}
                            {sandbox.node && (
                                <div className="flex gap-2">
                                    <dt className="min-w-28 text-muted-foreground">Node:</dt>
                                    <dd className="text-foreground">
                                        {sandbox.node.node_name}
                                        <span className="ml-2 text-xs text-muted-foreground">({sandbox.node.api_url})</span>
                                    </dd>
                                </div>
                            )}
                        </dl>
                    </div>
                    <div className="space-y-3">
                        <h4 className="text-sm font-semibold text-foreground">Configuration</h4>
                        <pre className="max-h-64 overflow-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border bg-background/50 p-4 text-xs font-mono text-muted-foreground">
                            {JSON.stringify(sandbox.config, null, 2)}
                        </pre>
                        {sandbox.proxmox_config && (
                            <>
                                <h4 className="text-sm font-semibold text-foreground">Proxmox Configuration</h4>
                                <pre className="max-h-64 overflow-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border bg-background/50 p-4 text-xs font-mono text-muted-foreground">
                                    {JSON.stringify(sandbox.proxmox_config, null, 2)}
                                </pre>
                            </>
                        )}
                    </div>
                </div>
            </td>
        </tr>
    );
}

function CreateSandboxDialog({
    open,
    onOpenChange,
    on  ,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onCreated: () => void;
}) {
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [form, setForm] = useState({
        owner_user_id: '',
        kind: 'training' as 'training' | 'ctf',
        type: 'lxc' as 'lxc' | 'qemu',
        image: '',
        ram: 2048,
        cpu: 2,
        virtualization: true,
    });

    function updateField<K extends keyof typeof form>(key: K, value: (typeof form)[K]) {
        setForm((prev) => ({ ...prev, [key]: value }));
        setError(null);
    }

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        setSubmitting(true);
        setError(null);

        const payload: CreateSandboxPayload = {
            owner_user_id: form.owner_user_id,
            kind: form.kind,
            type: form.type,
            config: {
                image: form.image,
                ram: form.ram,
                cpu: form.cpu,
                virtualization: form.virtualization,
            },
        };

        try {
            await api.post('/sandboxes', payload);
            setForm({ owner_user_id: '', kind: 'training', type: 'lxc', image: '', ram: 2048, cpu: 2, virtualization: true });
            onOpenChange(false);
            // onCreated();
        } catch (err: any) {
            setError(err.message || 'Failed to create sandbox');
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Create New Sandbox</DialogTitle>
                    <DialogDescription>Provision a new sandbox environment for a user.</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="owner_user_id">Owner User ID</Label>
                        <Input
                            id="owner_user_id"
                            placeholder="Enter user ID..."
                            value={form.owner_user_id}
                            onChange={(e) => updateField('owner_user_id', e.target.value)}
                            required
                        />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label>Kind</Label>
                            <Select value={form.kind} onValueChange={(v) => updateField('kind', v as 'training' | 'ctf')}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="training">Training</SelectItem>
                                    <SelectItem value="competition">Competiton</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>Type</Label>
                            <Select value={form.type} onValueChange={(v) => updateField('type', v as 'lxc' | 'qemu')}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="lxc">LXC</SelectItem>
                                    <SelectItem value="qemu">QEMU</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="image">Image</Label>
                        <Input
                            id="image"
                            placeholder="e.g. ubuntu-22.04"
                            value={form.image}
                            onChange={(e) => updateField('image', e.target.value)}
                        />
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="ram">RAM (MB)</Label>
                            <Input
                                id="ram"
                                type="number"
                                min={256}
                                value={form.ram}
                                onChange={(e) => updateField('ram', parseInt(e.target.value) || 0)}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="cpu">CPU Cores</Label>
                            <Input
                                id="cpu"
                                type="number"
                                min={1}
                                value={form.cpu}
                                onChange={(e) => updateField('cpu', parseInt(e.target.value) || 0)}
                            />
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label>Virtualization (Enable Nesting for LXC / KVM for QEMU)</Label>
                        <Select value={form.virtualization ? 'true' : 'false'} onValueChange={(v) => updateField('virtualization', v === 'true')}>
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="true">Enabled</SelectItem>
                                <SelectItem value="false">Disabled</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    {error && (
                        <div className="rounded-lg border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                            {error}
                        </div>
                    )}

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)} disabled={submitting}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={submitting}>
                            {submitting && <Loader2 className="animate-spin" />}
                            {submitting ? 'Creating...' : 'Create Sandbox'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function Sandboxes() {
    const [sandboxes, setSandboxes] = useState<Sandbox[]>([]);
    const [loading, setLoading] = useState(true);
    const [expandedId, setExpandedId] = useState<string | null>(null);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [activatingId, setActivatingId] = useState<string | null>(null);

    const fetchSandboxes = useCallback(async () => {
        setLoading(true);
        try {
            const res = await api.getPaginated<Sandbox>('/sandboxes');
            // console.log('res:', res);
            setSandboxes(res?.data ?? []);
        } catch (err) { 
            console.error('Failed to fetch sandboxes:', err);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchSandboxes();
    }, [fetchSandboxes]);

    async function handleActivate(sandbox: Sandbox) {
        setActivatingId(sandbox.id);
        try {
            await api.post(`/sandboxes/${sandbox.id}/activate`);
            await fetchSandboxes();
        } catch (err) {
            console.error('Failed to activate sandbox:', err);
        } finally {
            setActivatingId(null);
        }
    }

    function toggleExpanded(id: string) {
        setExpandedId((prev) => (prev === id ? null : id));
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sandbox Management" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground">Sandbox Management</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Manage and monitor all sandbox environments.
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button variant="outline" size="sm" onClick={fetchSandboxes} disabled={loading}>
                            <RefreshCw className={loading ? 'animate-spin' : ''} />
                            Refresh
                        </Button>
                        <Button size="sm" onClick={() => setDialogOpen(true)}>
                            <Plus />
                            New Sandbox
                        </Button>
                    </div>
                </div>

                {/* Table Card */}
                <div className="border-sidebar-border/70 dark:border-sidebar-border overflow-hidden rounded-xl border bg-card">
                    {loading ? (
                        <TableSkeleton />
                    ) : sandboxes.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-16 text-center">
                            <div className="mb-4 rounded-full bg-muted/50 p-4">
                                <svg className="h-8 w-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 className="text-sm font-medium text-foreground">No sandboxes found</h3>
                            <p className="mt-1 text-sm text-muted-foreground">Get started by creating a new sandbox.</p>
                            <Button size="sm" className="mt-4" onClick={() => setDialogOpen(true)}>
                                <Plus />
                                Create Sandbox
                            </Button>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-sidebar-border/70 dark:border-sidebar-border border-b bg-muted/40">
                                        <th className="w-8 px-3 py-3" />
                                        <th className="px-4 py-3 text-left font-medium text-muted-foreground">VMID</th>
                                        <th className="px-4 py-3 text-left font-medium text-muted-foreground">Hostname</th>
                                        <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
                                        <th className="px-4 py-3 text-left font-medium text-muted-foreground">Kind</th>
                                        <th className="px-4 py-3 text-left font-medium text-muted-foreground">Type</th>
                                        <th className="px-4 py-3 text-left font-medium text-muted-foreground">Owner</th>
                                        <th className="px-4 py-3 text-left font-medium text-muted-foreground">Node</th>
                                        {/* <th className="px-4 py-3 text-left font-medium text-muted-foreground">Created At</th> */}
                                        <th className="px-4 py-3 text-right font-medium text-muted-foreground">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sidebar-border/50 dark:divide-sidebar-border/50">
                                    {sandboxes.map((sandbox) => (
                                        <>
                                            <tr
                                                key={sandbox.id}
                                                onClick={() => toggleExpanded(sandbox.id)}
                                                className="group cursor-pointer transition-colors duration-150 hover:bg-muted/30"
                                            >
                                                <td className="px-3 py-3 text-muted-foreground">
                                                    {expandedId === sandbox.id ? (
                                                        <ChevronDown className="h-4 w-4 transition-transform" />
                                                    ) : (
                                                        <ChevronRight className="h-4 w-4 transition-transform" />
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 font-mono text-xs font-medium text-foreground">
                                                    {sandbox.vmid ?? '—'}
                                                </td>
                                                <td className="px-4 py-3 text-foreground">
                                                    {sandbox.config?.hostname || `sandbox-${sandbox.vmid || 'pending'}`}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <StatusBadge status={sandbox.status} />
                                                </td>
                                                <td className="px-4 py-3 capitalize text-foreground">{sandbox.kind}</td>
                                                <td className="px-4 py-3 text-foreground">
                                                    <span className="rounded bg-muted/60 px-1.5 py-0.5 font-mono text-xs uppercase">
                                                        {sandbox.type || '—'}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 text-foreground">
                                                    {sandbox.owner?.username || (
                                                        <span className="text-muted-foreground">Unknown</span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-foreground">
                                                    {sandbox.node?.node_name || (
                                                        <span className="text-muted-foreground">—</span>
                                                    )}
                                                </td>
                                                {/* <td className="px-4 py-3 text-muted-foreground">
                                                    {new Date(sandbox.created_at).toLocaleDateString(undefined, {
                                                        month: 'short',
                                                        day: 'numeric',
                                                        year: 'numeric',
                                                    })}
                                                </td> */}
                                                <td className="px-4 py-3 text-right">
                                                    {sandbox.status === 'queued' && (
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                handleActivate(sandbox);
                                                            }}
                                                            disabled={activatingId === sandbox.id}
                                                            className="h-7 text-xs"
                                                        >
                                                            {activatingId === sandbox.id ? (
                                                                <Loader2 className="animate-spin" />
                                                            ) : null}
                                                            Activate
                                                        </Button>
                                                    )}
                                                </td>
                                            </tr>
                                            {expandedId === sandbox.id && (
                                                <SandboxDetailPanel key={`detail-${sandbox.id}`} sandbox={sandbox} />
                                            )}
                                        </>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>

                {/* Create Dialog */}
                <CreateSandboxDialog open={dialogOpen} onOpenChange={setDialogOpen} onCreated={fetchSandboxes} />
            </div>
        </AppLayout>
    );
}
