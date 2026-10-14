import { ConfirmDialog } from '@/components/alert-dialog';
import Table from '@/components/Table';
import { buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { HomeHeroSection } from '@/types';
import { AlertDialog } from '@heroui/react';
import { Head, Link, router } from '@inertiajs/react';
import { Edit } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

interface Props {
    heroes: { data: HomeHeroSection[]; current_page: number; per_page: number; total: number };
}

const columns = [
    { label: 'Badge', key: 'badge', sortable: true },
    { label: 'Heading', key: 'heading', sortable: true },
    { label: 'Status', key: 'status', sortable: true },
    { label: 'Actions', key: 'actions' },
];

export default function Index({ heroes }: Props) {
    const search = new URLSearchParams(window.location.search).get('search') || '';
    const [pendingStatus, setPendingStatus] = useState<{ id: number; newStatus: string; oldStatus: string } | null>(null);
    const triggerRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (pendingStatus) triggerRef.current?.click();
    }, [pendingStatus]);

    return (
        <AppLayout breadcrumbs={[{ title: 'Hero Sections', href: '/home-cms/hero' }]}>
            <Head title="Hero Sections" />
            <div className="m-5">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <h1 className="text-2xl font-semibold">Hero Sections</h1>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table<HomeHeroSection>
                            data={heroes.data}
                            total={heroes.total}
                            currentPage={heroes.current_page}
                            rowsPerPage={heroes.per_page}
                            columns={columns}
                            searchValue={search}
                            searchableKeys={['badge', 'heading']}
                            renderCell={(key, value, row) => {
                                if (key === 'status') {
                                    return (
                                        <Select
                                            value={row.status}
                                            onValueChange={(v) => setPendingStatus({ id: row.id, newStatus: v, oldStatus: row.status })}
                                        >
                                            <SelectTrigger
                                                className={`h-8 w-[110px] cursor-pointer rounded-full border-none px-3 py-1 text-center text-sm font-medium ${row.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}
                                            >
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
                                                href={route('home-cms.hero.edit', row.id)}
                                                className={buttonVariants({ variant: 'secondary', size: 'icon' })}
                                            >
                                                <Edit className="h-4 w-4 text-blue-500" />
                                            </Link>
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
                description={`Change status from ${pendingStatus?.oldStatus} to ${pendingStatus?.newStatus}?`}
                confirmText="Change"
                confirmColor="primary"
                onConfirm={() =>
                    pendingStatus &&
                    router.patch(
                        route('home-cms.hero.updateStatus', pendingStatus.id),
                        {},
                        {
                            onSuccess: () => {
                                toast.success('Status updated.');
                                setPendingStatus(null);
                            },
                            onFinish: () => setPendingStatus(null),
                        },
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
