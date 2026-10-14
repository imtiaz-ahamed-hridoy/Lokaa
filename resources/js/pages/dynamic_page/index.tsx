import { ConfirmDialog } from '@/components/alert-dialog';
import Table from '@/components/Table';
import { buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { DynamicPage, PaginatedDynamicPages } from '@/types';
import { AlertDialog } from '@heroui/react';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Trash } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

interface Props {
    pages: PaginatedDynamicPages;
}

const columns = [
    { label: 'Page Title', key: 'page_title', sortable: true },
    { label: 'Slug', key: 'page_slug', sortable: true },
    { label: 'Status', key: 'status', sortable: true },
    { label: 'Actions', key: 'actions' },
];

export default function Index({ pages }: Props) {
    const queryParams = new URLSearchParams(window.location.search);
    const search = queryParams.get('search') || '';

    const deletePage = (id: number) => {
        router.delete(route('dynamic_page.destroy', id), {
            onSuccess: () => toast.success('Page deleted successfully!'),
            onError: () => toast.error('Failed to delete the page.'),
        });
    };

    const [pendingStatus, setPendingStatus] = useState<{ id: number; newStatus: string; oldStatus: string } | null>(null);
    const statusDialogTriggerRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (pendingStatus) {
            statusDialogTriggerRef.current?.click();
        }
    }, [pendingStatus]);

    const confirmStatusChange = () => {
        if (!pendingStatus) return;
        const { id, newStatus } = pendingStatus;

        router.patch(
            route('dynamic_page.updateStatus', id),
            { status: newStatus },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success(`Status updated to ${newStatus}!`);
                    setPendingStatus(null);
                },
                onError: () => {
                    toast.error('Failed to update status.');
                    setPendingStatus(null);
                },
            },
        );
    };

    const cancelStatusChange = () => {
        setPendingStatus(null);
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Dynamic Pages', href: '/dynamic_page' }]}>
            <Head title="Dynamic Pages List" />

            <div className="m-5">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <h1 className="text-2xl font-semibold text-gray-800 dark:text-white">Dynamic Pages List</h1>
                            <Link className={buttonVariants({ variant: 'default', className: 'ml-auto' })} href={route('dynamic_page.create')}>
                                Create Dynamic Page
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table<DynamicPage>
                            data={pages.data}
                            total={pages.total}
                            currentPage={pages.current_page}
                            rowsPerPage={pages.per_page}
                            columns={columns}
                            searchableKeys={['page_title', 'page_slug']}
                            renderCell={(key, value, row) => {
                                if (key === 'status') {
                                    const getStatusColor = (status: string) => {
                                        switch (status.toLowerCase()) {
                                            case 'active':
                                                return 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400';
                                            case 'inactive':
                                                return 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400';
                                            default:
                                                return 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300';
                                        }
                                    };

                                    const displayStatus = pendingStatus?.id === row.id ? pendingStatus.newStatus : row.status;

                                    return (
                                        <Select
                                            value={displayStatus}
                                            onValueChange={(value) => {
                                                if (value !== row.status) {
                                                    setPendingStatus({ id: row.id, newStatus: value, oldStatus: row.status });
                                                }
                                            }}
                                        >
                                            <SelectTrigger
                                                className={`h-8 w-[110px] cursor-pointer rounded-full border-none px-3 py-1 text-center text-sm font-medium outline-none ${getStatusColor(displayStatus)} shadow-none ring-0 transition-colors focus:ring-0 focus:ring-offset-0`}
                                            >
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent className="rounded-xl border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-900">
                                                <SelectItem
                                                    value="Active"
                                                    className="cursor-pointer rounded-lg px-4 py-2 font-medium focus:bg-green-100 focus:text-green-800 dark:focus:bg-green-900/30 dark:focus:text-green-400"
                                                >
                                                    Active
                                                </SelectItem>
                                                <SelectItem
                                                    value="Inactive"
                                                    className="cursor-pointer rounded-lg px-4 py-2 font-medium focus:bg-red-100 focus:text-red-800 dark:focus:bg-red-900/30 dark:focus:text-red-400"
                                                >
                                                    Inactive
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    );
                                }

                                if (key === 'actions') {
                                    return (
                                        <div className="flex gap-2">
                                            <Link
                                                href={route('dynamic_page.edit', row.id)}
                                                className={buttonVariants({ variant: 'secondary', size: 'icon' })}
                                            >
                                                <Edit className="h-4 w-4 text-blue-500" />
                                            </Link>
                                            <ConfirmDialog
                                                title="Delete this Page?"
                                                description="Are you sure you want to delete this page? This action cannot be undone."
                                                onConfirm={() => deletePage(row.id)}
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
                            onPageChange={(page) => {
                                const params = Object.fromEntries(new URLSearchParams(window.location.search).entries());
                                router.get(
                                    window.location.pathname,
                                    {
                                        ...params,
                                        page,
                                    },
                                    { preserveState: true, replace: true },
                                );
                            }}
                            onPerPageChange={(perPage) => {
                                const params = Object.fromEntries(new URLSearchParams(window.location.search).entries());
                                router.get(
                                    window.location.pathname,
                                    {
                                        ...params,
                                        page: 1,
                                        per_page: perPage,
                                    },
                                    { preserveState: true, replace: true },
                                );
                            }}
                            onSearchChange={(searchVal) => {
                                const params = Object.fromEntries(new URLSearchParams(window.location.search).entries());
                                if (searchVal) {
                                    params.search = searchVal;
                                } else {
                                    delete params.search;
                                }
                                router.get(
                                    window.location.pathname,
                                    {
                                        ...params,
                                        page: 1,
                                    },
                                    { preserveState: true, replace: true },
                                );
                            }}
                            searchValue={search}
                        />
                    </CardContent>
                </Card>
            </div>

            <ConfirmDialog
                title="Change Status?"
                description={`Are you sure you want to change the status from ${pendingStatus?.oldStatus} to ${pendingStatus?.newStatus}?`}
                onConfirm={confirmStatusChange}
                confirmText="Change Status"
                confirmColor="primary"
                icon={<Edit className="size-5" />}
                trigger={
                    <AlertDialog.Trigger>
                        <button ref={statusDialogTriggerRef} className="hidden" />
                    </AlertDialog.Trigger>
                }
                onOpenChange={(isOpen) => {
                    if (!isOpen) cancelStatusChange();
                }}
            />
        </AppLayout>
    );
}
