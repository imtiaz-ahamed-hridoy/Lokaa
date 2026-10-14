import { ConfirmDialog } from '@/components/alert-dialog';
import Table from '@/components/Table';
import { buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { AlertDialog } from '@heroui/react';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Trash } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

type ListingType = { id: number; name: string; slug: string; image_url: string | null; status: 'Active' | 'Inactive' };
type Props = { types: { data: ListingType[]; current_page: number; per_page: number; total: number } };

const columns = [
    { label: 'Image', key: 'image', sortable: false },
    { label: 'Name', key: 'name', sortable: true },
    { label: 'Slug', key: 'slug', sortable: true },
    { label: 'Status', key: 'status', sortable: true },
    { label: 'Actions', key: 'actions' },
];

export default function Index({ types }: Props) {
    const search = new URLSearchParams(window.location.search).get('search') || '';
    const [pendingStatus, setPendingStatus] = useState<ListingType | null>(null);
    const triggerRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (pendingStatus) triggerRef.current?.click();
    }, [pendingStatus]);

    return (
        <AppLayout breadcrumbs={[{ title: 'Listing Types', href: '/listing-type' }]}>
            <Head title="Listing Types" />
            <div className="m-5">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between gap-3">
                            <h1 className="text-2xl font-semibold">Listing Types</h1>
                            <Link className={buttonVariants()} href={route('listing-type.create')}>
                                Create Type
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table<ListingType>
                            data={types.data}
                            total={types.total}
                            currentPage={types.current_page}
                            rowsPerPage={types.per_page}
                            columns={columns}
                            searchValue={search}
                            searchableKeys={['name', 'slug']}
                            renderCell={(key, value, row) => {
                                if (key === 'image') {
                                    return row.image_url ? (
                                        <img src={row.image_url} alt={row.name} className="h-10 w-10 rounded-md object-cover" />
                                    ) : (
                                        <span className="text-muted-foreground text-xs">—</span>
                                    );
                                }
                                if (key === 'status') {
                                    return (
                                        <Select value={row.status} onValueChange={() => setPendingStatus(row)}>
                                            <SelectTrigger className="h-8 w-[110px] rounded-full">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="Active">Active</SelectItem>
                                                <SelectItem value="Inactive">Inactive</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    );
                                }
                                if (key === 'actions') {
                                    return (
                                        <div className="flex gap-2">
                                            <Link
                                                href={route('listing-type.edit', row.id)}
                                                className={buttonVariants({ variant: 'secondary', size: 'icon' })}
                                            >
                                                <Edit className="h-4 w-4 text-blue-500" />
                                            </Link>
                                            <ConfirmDialog
                                                title="Delete listing type?"
                                                description="Delete this listing type?"
                                                onConfirm={() =>
                                                    router.delete(route('listing-type.destroy', row.id), {
                                                        onSuccess: () => toast.success('Listing type deleted.'),
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
            <ConfirmDialog
                title="Change status?"
                description="Update this listing type status?"
                onConfirm={() =>
                    pendingStatus &&
                    router.patch(
                        route('listing-type.updateStatus', pendingStatus.id),
                        {},
                        { onSuccess: () => toast.success('Status updated.'), onFinish: () => setPendingStatus(null) },
                    )
                }
                trigger={
                    <AlertDialog.Trigger>
                        <button ref={triggerRef} className="hidden" />
                    </AlertDialog.Trigger>
                }
                onOpenChange={(open) => !open && setPendingStatus(null)}
            />
        </AppLayout>
    );
}
