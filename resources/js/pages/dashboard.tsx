import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Server, ScrollText, Shield } from 'lucide-react';
import { getUser } from '@/lib/api-client';
import { useEffect } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const adminCards = [
    {
        title: 'Sandbox Management',
        description: 'Create, view, and activate Proxmox containers and virtual machines.',
        href: '/admin/sandboxes',
        icon: Server,
        gradient: 'from-blue-500/10 to-cyan-500/10 dark:from-blue-500/20 dark:to-cyan-500/20',
        iconColor: 'text-blue-600 dark:text-blue-400',
        borderColor: 'border-blue-500/20 dark:border-blue-500/30',
    },
    {
        title: 'Log History',
        description: 'View system logs, admin actions, and sandbox events with filtering.',
        href: '/admin/logs',
        icon: ScrollText,
        gradient: 'from-amber-500/10 to-orange-500/10 dark:from-amber-500/20 dark:to-orange-500/20',
        iconColor: 'text-amber-600 dark:text-amber-400',
        borderColor: 'border-amber-500/20 dark:border-amber-500/30',
    },
];

export default function Dashboard() {
    const user = getUser<{ username: string; role: string }>();

    useEffect(() => {
        if (!user) {
            window.location.href = '/login';
        }
    }, [user]);

    if (!user) return null;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Welcome Header */}
                <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-500">
                        <Shield className="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Welcome back, <span className="text-blue-600 dark:text-blue-400">{user.username}</span>
                        </h1>
                        <p className="text-muted-foreground text-sm">
                            Sandbox Monitoring — <span className="capitalize">{user.role}</span> Panel
                        </p>
                    </div>
                </div>

                {/* Navigation Cards */}
                <div className="grid gap-4 md:grid-cols-2">
                    {adminCards.map((card) => (
                        <Link
                            key={card.href}
                            href={card.href}
                            className={`group relative overflow-hidden rounded-xl border ${card.borderColor} bg-gradient-to-br ${card.gradient} p-6 transition-all duration-300 hover:shadow-lg hover:scale-[1.02]`}
                        >
                            <div className="flex items-start gap-4">
                                <div className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-white/80 dark:bg-white/10 ${card.iconColor} shadow-sm`}>
                                    <card.icon className="h-6 w-6" />
                                </div>
                                <div className="flex-1">
                                    <h2 className="text-lg font-semibold tracking-tight group-hover:underline">
                                        {card.title}
                                    </h2>
                                    <p className="text-muted-foreground mt-1 text-sm leading-relaxed">
                                        {card.description}
                                    </p>
                                </div>
                            </div>
                            <div className="text-muted-foreground mt-4 flex items-center text-xs font-medium">
                                Open →
                            </div>
                        </Link>
                    ))}
                </div>

                {/* Quick Info */}
                <div className="border-sidebar-border/70 dark:border-sidebar-border rounded-xl border p-6">
                    <h3 className="text-sm font-medium text-muted-foreground uppercase tracking-wider mb-3">Quick Info</h3>
                    <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <p className="text-2xl font-bold">—</p>
                            <p className="text-xs text-muted-foreground mt-1">Total Sandboxes</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">—</p>
                            <p className="text-xs text-muted-foreground mt-1">Active</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-amber-600 dark:text-amber-400">—</p>
                            <p className="text-xs text-muted-foreground mt-1">Queued</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold text-red-600 dark:text-red-400">—</p>
                            <p className="text-xs text-muted-foreground mt-1">Failed</p>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
