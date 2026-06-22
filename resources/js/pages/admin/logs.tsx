import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { api } from '@/lib/api-client';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import type { CtfLog } from '@/types/sandbox';
import { Head } from '@inertiajs/react';
import { ChevronDown, ChevronLeft, ChevronRight, RefreshCw, Search } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Logs', href: '/admin/logs' },
];

const levelConfig: Record<CtfLog['level'], { label: string; className: string }> = {
    info: {
        label: 'Info',
        className: 'bg-blue-500/15 text-blue-400 border-blue-500/25 hover:bg-blue-500/25',
    },
    warning: {
        label: 'Warning',
        className: 'bg-amber-500/15 text-amber-400 border-amber-500/25 hover:bg-amber-500/25',
    },
    error: {
        label: 'Error',
        className: 'bg-red-500/15 text-red-400 border-red-500/25 hover:bg-red-500/25',
    },
    debug: {
        label: 'Debug',
        className: 'bg-neutral-500/15 text-neutral-400 border-neutral-500/25 hover:bg-neutral-500/25',
    },
};

function LevelBadge({ level }: { level: CtfLog['level'] }) {
    const config = levelConfig[level];
    return (
        <Badge variant="outline" className={config.className}>
            {config.label}
        </Badge>
    );
}

function TableSkeleton() {
    return (
        <div className="space-y-3 p-6">
            {Array.from({ length: 8 }).map((_, i) => (
                <div key={i} className="flex items-center gap-4">
                    <Skeleton className="h-5 w-16" />
                    <Skeleton className="h-5 w-24" />
                    <Skeleton className="h-5 flex-1" />
                    <Skeleton className="h-5 w-36" />
                </div>
            ))}
        </div>
    );
}

export default function Logs() {
    const [logs, setLogs] = useState<CtfLog[]>([]);
    const [loading, setLoading] = useState(true);
    const [expandedId, setExpandedId] = useState<number | null>(null);
    const [levelFilter, setLevelFilter] = useState<string>('all');
    const [sourceFilter, setSourceFilter] = useState('');
    const [pagination, setPagination] = useState({
        currentPage: 1,
        lastPage: 1,
        perPage: 15,
        total: 0,
    });

    const fetchLogs = useCallback(
        async (page = 1) => {
            setLoading(true);
            try {
                const params = new URLSearchParams();
                params.set('page', String(page));
                if (levelFilter && levelFilter !== 'all') {
                    params.set('level', levelFilter);
                }
                if (sourceFilter.trim()) {
                    params.set('source', sourceFilter.trim());
                }

                const res = await api.getPaginated<CtfLog>(`/logs?${params.toString()}`);
                setLogs(res.data.data);
                setPagination({
                    currentPage: res.data.current_page,
                    lastPage: res.data.last_page,
                    perPage: res.data.per_page,
                    total: res.data.total,
                });
            } catch (err) {
                console.error('Failed to fetch logs:', err);
            } finally {
                setLoading(false);
            }
        },
        [levelFilter, sourceFilter],
    );

    useEffect(() => {
        fetchLogs(1);
    }, [fetchLogs]);

    function toggleExpanded(id: number) {
        setExpandedId((prev) => (prev === id ? null : id));
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Log History" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground">Log History</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Browse and filter system log entries.
                        </p>
                    </div>
                    <Button variant="outline" size="sm" onClick={() => fetchLogs(pagination.currentPage)} disabled={loading}>
                        <RefreshCw className={loading ? 'animate-spin' : ''} />
                        Refresh
                    </Button>
                </div>

                {/* Filters */}
                <div className="border-sidebar-border/70 dark:border-sidebar-border flex flex-wrap items-end gap-3 rounded-xl border bg-card p-4">
                    <div className="space-y-1.5">
                        <label className="text-xs font-medium text-muted-foreground">Level</label>
                        <Select value={levelFilter} onValueChange={setLevelFilter}>
                            <SelectTrigger className="w-36">
                                <SelectValue placeholder="All levels" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Levels</SelectItem>
                                <SelectItem value="info">Info</SelectItem>
                                <SelectItem value="warning">Warning</SelectItem>
                                <SelectItem value="error">Error</SelectItem>
                                <SelectItem value="debug">Debug</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-1.5 flex-1 min-w-48">
                        <label className="text-xs font-medium text-muted-foreground">Source</label>
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Filter by source..."
                                value={sourceFilter}
                                onChange={(e) => setSourceFilter(e.target.value)}
                                className="pl-9"
                            />
                        </div>
                    </div>
                </div>

                {/* Table Card */}
                <div className="border-sidebar-border/70 dark:border-sidebar-border overflow-hidden rounded-xl border bg-card">
                    {loading ? (
                        <TableSkeleton />
                    ) : logs.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-16 text-center">
                            <div className="mb-4 rounded-full bg-muted/50 p-4">
                                <Search className="h-8 w-8 text-muted-foreground" />
                            </div>
                            <h3 className="text-sm font-medium text-foreground">No logs found</h3>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Try adjusting your filters or check back later.
                            </p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-sidebar-border/70 dark:border-sidebar-border border-b bg-muted/40">
                                        <th className="px-4 py-3 text-left font-medium text-muted-foreground">Level</th>
                                        <th className="px-4 py-3 text-left font-medium text-muted-foreground">Source</th>
                                        <th className="px-4 py-3 text-left font-medium text-muted-foreground">Message</th>
                                        <th className="px-4 py-3 text-right font-medium text-muted-foreground">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-sidebar-border/50 dark:divide-sidebar-border/50">
                                    {logs.map((log) => (
                                        <>
                                            <tr
                                                key={log.id}
                                                onClick={() => toggleExpanded(log.id)}
                                                className="group cursor-pointer transition-colors duration-150 hover:bg-muted/30"
                                            >
                                                <td className="px-4 py-3">
                                                    <LevelBadge level={log.level} />
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="rounded bg-muted/60 px-2 py-0.5 font-mono text-xs text-foreground">
                                                        {log.source}
                                                    </span>
                                                </td>
                                                <td className="max-w-md truncate px-4 py-3 text-foreground">
                                                    <div className="flex items-center gap-2">
                                                        <ChevronDown
                                                            className={`h-3.5 w-3.5 shrink-0 text-muted-foreground transition-transform duration-200 ${
                                                                expandedId === log.id ? 'rotate-0' : '-rotate-90'
                                                            }`}
                                                        />
                                                        <span className="truncate">{log.message}</span>
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-4 py-3 text-right text-muted-foreground">
                                                    {new Date(log.created_at).toLocaleString(undefined, {
                                                        month: 'short',
                                                        day: 'numeric',
                                                        hour: '2-digit',
                                                        minute: '2-digit',
                                                        second: '2-digit',
                                                    })}
                                                </td>
                                            </tr>
                                            {expandedId === log.id && (
                                                <tr key={`ctx-${log.id}`}>
                                                    <td
                                                        colSpan={4}
                                                        className="border-sidebar-border/70 dark:border-sidebar-border border-t bg-muted/30 px-6 py-4"
                                                    >
                                                        <div className="space-y-2">
                                                            <h4 className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                                Context
                                                            </h4>
                                                            <pre className="max-h-64 overflow-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border bg-background/50 p-4 text-xs font-mono text-muted-foreground">
                                                                {Object.keys(log.context).length > 0
                                                                    ? JSON.stringify(log.context, null, 2)
                                                                    : 'No context data available.'}
                                                            </pre>
                                                        </div>
                                                    </td>
                                                </tr>
                                            )}
                                        </>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {/* Pagination */}
                    {!loading && logs.length > 0 && (
                        <div className="border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-between border-t px-4 py-3">
                            <p className="text-xs text-muted-foreground">
                                Showing page <span className="font-medium text-foreground">{pagination.currentPage}</span> of{' '}
                                <span className="font-medium text-foreground">{pagination.lastPage}</span>
                                <span className="ml-2">
                                    ({pagination.total} total {pagination.total === 1 ? 'entry' : 'entries'})
                                </span>
                            </p>
                            <div className="flex items-center gap-1">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => fetchLogs(pagination.currentPage - 1)}
                                    disabled={pagination.currentPage <= 1}
                                    className="h-8"
                                >
                                    <ChevronLeft />
                                    Previous
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => fetchLogs(pagination.currentPage + 1)}
                                    disabled={pagination.currentPage >= pagination.lastPage}
                                    className="h-8"
                                >
                                    Next
                                    <ChevronRight />
                                </Button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
