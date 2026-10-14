import { ConfirmDialog } from '@/components/alert-dialog';
import Table from '@/components/Table';
import { buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { AlertDialog } from '@heroui/react';
import { Head, Link, router } from '@inertiajs/react';
import { Eye, Trash } from 'lucide-react';
import { toast } from 'sonner';

type Review = {
    id: number;
    rating: number;
    description: string;
    created_at: string;
    reviewer?: { name: string; email: string };
    reviewee?: { name: string; email: string };
    listing?: { title: string; slug: string };
};

type Props = {
    reviews: { data: Review[]; current_page: number; per_page: number; total: number };
    analytics: { total_reviews: number; average_rating: number; rating_distribution: Record<string, number> };
};

const columns = [
    { label: 'Reviewer', key: 'reviewer', sortable: false },
    { label: 'Owner', key: 'reviewee', sortable: false },
    { label: 'Listing', key: 'listing', sortable: false },
    { label: 'Rating', key: 'rating', sortable: true },
    { label: 'Review', key: 'description', sortable: true },
    { label: 'Actions', key: 'actions' },
];

export default function Index({ reviews, analytics }: Props) {
    const search = new URLSearchParams(window.location.search).get('search') || '';

    return (
        <AppLayout breadcrumbs={[{ title: 'Reviews', href: '/review' }]}>
            <Head title="Reviews" />
            <div className="m-5 space-y-5">
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="text-muted-foreground text-sm">Total Reviews</CardHeader>
                        <CardContent className="text-2xl font-semibold">{analytics.total_reviews}</CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="text-muted-foreground text-sm">Average Rating</CardHeader>
                        <CardContent className="text-2xl font-semibold">{analytics.average_rating || 0}</CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="text-muted-foreground text-sm">5 Star Reviews</CardHeader>
                        <CardContent className="text-2xl font-semibold">{analytics.rating_distribution?.['5'] || 0}</CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <div className="flex justify-between">
                            <h1 className="text-2xl font-semibold">Reviews</h1>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table<Review>
                            data={reviews.data}
                            total={reviews.total}
                            currentPage={reviews.current_page}
                            rowsPerPage={reviews.per_page}
                            columns={columns}
                            searchValue={search}
                            searchableKeys={['description']}
                            renderCell={(key, value, row) => {
                                if (key === 'reviewer') return row.reviewer?.name || '-';
                                if (key === 'reviewee') return row.reviewee?.name || '-';
                                if (key === 'listing') return row.listing?.title || '-';
                                if (key === 'description') return <span className="block max-w-md truncate">{row.description}</span>;
                                if (key === 'rating') return `${row.rating}/5`;
                                if (key === 'actions') {
                                    return (
                                        <div className="flex gap-2">
                                            <Link
                                                href={route('review.show', row.id)}
                                                className={buttonVariants({ variant: 'secondary', size: 'icon' })}
                                            >
                                                <Eye className="h-4 w-4 text-blue-500" />
                                            </Link>
                                            <ConfirmDialog
                                                title="Delete review?"
                                                description="Delete this review permanently?"
                                                onConfirm={() =>
                                                    router.delete(route('review.destroy', row.id), {
                                                        onSuccess: () => toast.success('Review deleted.'),
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
                                }
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
