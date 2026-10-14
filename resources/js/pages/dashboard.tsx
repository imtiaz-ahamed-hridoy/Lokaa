import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowUpRight, FolderTree, Layers, Mail, MessageSquare, Tags, Users } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface DashboardProps {
    stats: {
        total_users: number;
        total_listing_types: number;
        total_categories: number;
        total_listings: number;
        active_listings: number;
        pending_listings: number;
        rejected_listings: number;
        total_contacts: number;
        total_newsletters: number;
    };
}

export default function Dashboard({ stats }: DashboardProps) {
    const statCards = [
        {
            title: 'Total Users',
            value: stats.total_users,
            icon: Users,
            description: 'Registered users on platform',
            color: 'text-blue-600',
            bg: 'bg-blue-100 dark:bg-blue-900/30',
            href: '/user',
        },
        {
            title: 'Listing Types',
            value: stats.total_listing_types,
            icon: Layers,
            description: 'Marketplace listing types',
            color: 'text-violet-600',
            bg: 'bg-violet-100 dark:bg-violet-900/30',
            href: '/listing-type',
        },
        {
            title: 'Categories',
            value: stats.total_categories,
            icon: FolderTree,
            description: 'Active marketplace categories',
            color: 'text-emerald-600',
            bg: 'bg-emerald-100 dark:bg-emerald-900/30',
            href: '/listing-category',
        },
        {
            title: 'Total Listings',
            value: stats.total_listings,
            icon: Tags,
            description: 'All marketplace listings',
            color: 'text-orange-600',
            bg: 'bg-orange-100 dark:bg-orange-900/30',
            href: '/listing',
        },
        {
            title: 'Active Listings',
            value: stats.active_listings,
            icon: Tags,
            description: 'Currently published listings',
            color: 'text-pink-600',
            bg: 'bg-pink-100 dark:bg-pink-900/30',
            href: '/listing?status=Active',
        },
        {
            title: 'Pending Listings',
            value: stats.pending_listings,
            icon: Tags,
            description: 'Listings waiting for review',
            color: 'text-amber-600',
            bg: 'bg-amber-100 dark:bg-amber-900/30',
            href: '/listing?status=Pending',
        },
        {
            title: 'Rejected Listings',
            value: stats.rejected_listings,
            icon: Tags,
            description: 'Listings rejected by admins',
            color: 'text-red-600',
            bg: 'bg-red-100 dark:bg-red-900/30',
            href: '/listing?status=Rejected',
        },
        {
            title: 'Contact Messages',
            value: stats.total_contacts,
            icon: MessageSquare,
            description: 'Messages from contact form',
            color: 'text-rose-600',
            bg: 'bg-rose-100 dark:bg-rose-900/30',
            href: '/contact',
        },
        {
            title: 'Newsletter Subscribers',
            value: stats.total_newsletters,
            icon: Mail,
            description: 'Active newsletter subscribers',
            color: 'text-cyan-600',
            bg: 'bg-cyan-100 dark:bg-cyan-900/30',
            href: '/newsletter',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight">Overview</h1>
                    <p className="text-muted-foreground">Welcome back! Here's what's happening today.</p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {statCards.map((card) => (
                        <Link key={card.title} href={card.href}>
                            <Card className="group h-full cursor-pointer overflow-hidden border-none shadow-sm transition-all duration-200 hover:shadow-md">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-muted-foreground text-sm font-medium">{card.title}</CardTitle>
                                    <div className={`rounded-lg p-2 ${card.bg}`}>
                                        <card.icon className={`h-4 w-4 ${card.color}`} />
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-3xl font-bold tracking-tight">{card.value.toLocaleString()}</div>
                                    <p className="text-muted-foreground mt-1 text-xs">{card.description}</p>
                                    <div
                                        className={`mt-4 flex items-center text-xs font-medium ${card.color} opacity-70 transition-opacity duration-200 group-hover:opacity-100`}
                                    >
                                        View details
                                        <ArrowUpRight className="ml-1 h-3 w-3" />
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
