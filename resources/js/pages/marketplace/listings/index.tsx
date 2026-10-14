import { ConfirmDialog } from '@/components/alert-dialog';
import Table from '@/components/Table';
import { buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { AlertDialog } from '@heroui/react';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Trash } from 'lucide-react';
import { toast } from 'sonner';

type Listing = {
    id: number;
    title: string;
    price_display?: string;
    price: string;
    pricing_mode: string;
    city: string;
    area?: string;
    status: string;
    created_at: string;
    user?: { name: string };
    type?: { name: string };
    category?: { name: string };
    cover_media?: { path: string };
};
type Props = { listings: { data: Listing[]; current_page: number; per_page: number; total: number } };
const columns = [
    { label: 'Listing', key: 'title', sortable: true },
    { label: 'Owner', key: 'user', sortable: false },
    { label: 'Type', key: 'type', sortable: false },
    { label: 'Category', key: 'category', sortable: false },
    { label: 'Status', key: 'status', sortable: true },
    { label: 'Actions', key: 'actions' },
];

function price(row: Listing) {
    const suffix = row.pricing_mode === 'hourly' ? '/hr' : row.pricing_mode === 'daily' ? '/day' : '';
    return `$${Number(row.price).toLocaleString()}${suffix}`;
}

export default function Index({ listings }: Props) {
    const search = new URLSearchParams(window.location.search).get('search') || '';
    return (
        <AppLayout breadcrumbs={[{ title: 'Listings', href: '/listing' }]}>
            <Head title="Listings" />
            <div className="m-5">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between">
                            <h1 className="text-2xl font-semibold">Listings</h1>
                            <Link className={buttonVariants()} href={route('listing.create')}>
                                Create Listing
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table<Listing>
                            data={listings.data}
                            total={listings.total}
                            currentPage={listings.current_page}
                            rowsPerPage={listings.per_page}
                            columns={columns}
                            searchValue={search}
                            searchableKeys={['title', 'city']}
                            renderCell={(key, value, row) => {
                                if (key === 'title')
                                    return (
                                        <div>
                                            <div className="font-medium">{row.title}</div>
                                            <div className="text-muted-foreground text-xs">
                                                {price(row)} · {[row.area, row.city].filter(Boolean).join(', ')}
                                            </div>
                                        </div>
                                    );
                                if (key === 'user') return row.user?.name;
                                if (key === 'type') return row.type?.name;
                                if (key === 'category') return row.category?.name;
                                if (key === 'actions')
                                    return (
                                        <div className="flex gap-2">
                                            <Link
                                                href={route('listing.edit', row.id)}
                                                className={buttonVariants({ variant: 'secondary', size: 'icon' })}
                                            >
                                                <Edit className="h-4 w-4 text-blue-500" />
                                            </Link>
                                            <ConfirmDialog
                                                title="Delete listing?"
                                                description="Delete this listing?"
                                                onConfirm={() =>
                                                    router.delete(route('listing.destroy', row.id), {
                                                        onSuccess: () => toast.success('Listing deleted.'),
                                                    })
                                                }
                                                trigger={
                                                    <AlertDialog.Trigger className={buttonVariants({ variant: 'secondary', size: 'icon' })}>
                                                        <Trash className="h-4 w-4 text-red-500" />
                                                    </AlertDialog.Trigger>
                                                }
                                            />
                                        </div>
                                    );
                                return value;
                            }}
                            onPageChange={(page) =>
                                router.get(
                                    window.location.pathname,
                                    { ...Object.fromEntries(new URLSearchParams(window.location.search)), page },
                                    { preserveState: true, replace: true },
                                )
                            }
                            onPerPageChange={(per_page) =>
                                router.get(
                                    window.location.pathname,
                                    { ...Object.fromEntries(new URLSearchParams(window.location.search)), page: 1, per_page },
                                    { preserveState: true, replace: true },
                                )
                            }
                            onSearchChange={(value) =>
                                router.get(
                                    window.location.pathname,
                                    { ...Object.fromEntries(new URLSearchParams(window.location.search)), search: value || undefined, page: 1 },
                                    { preserveState: true, replace: true },
                                )
                            }
                        />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
