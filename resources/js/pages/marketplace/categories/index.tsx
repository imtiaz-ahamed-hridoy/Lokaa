import { ConfirmDialog } from '@/components/alert-dialog';
import Table from '@/components/Table';
import { buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { AlertDialog } from '@heroui/react';
import { Head, Link, router } from '@inertiajs/react';
import { Edit, Trash } from 'lucide-react';
import { toast } from 'sonner';

type Category = { id: number; name: string; slug: string; status: string; type?: { name: string } };
type Props = { categories: { data: Category[]; current_page: number; per_page: number; total: number } };
const columns = [
    { label: 'Name', key: 'name', sortable: true },
    { label: 'Type', key: 'type', sortable: false },
    { label: 'Slug', key: 'slug', sortable: true },
    { label: 'Status', key: 'status', sortable: true },
    { label: 'Actions', key: 'actions' },
];

export default function Index({ categories }: Props) {
    const search = new URLSearchParams(window.location.search).get('search') || '';
    return (
        <AppLayout breadcrumbs={[{ title: 'Categories', href: '/listing-category' }]}>
            <Head title="Categories" />
            <div className="m-5">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between">
                            <h1 className="text-2xl font-semibold">Categories</h1>
                            <Link className={buttonVariants()} href={route('listing-category.create')}>
                                Create Category
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table<Category>
                            data={categories.data}
                            total={categories.total}
                            currentPage={categories.current_page}
                            rowsPerPage={categories.per_page}
                            columns={columns}
                            searchValue={search}
                            searchableKeys={['name', 'slug']}
                            renderCell={(key, value, row) =>
                                key === 'type' ? (
                                    row.type?.name
                                ) : key === 'actions' ? (
                                    <div className="flex gap-2">
                                        <Link
                                            href={route('listing-category.edit', row.id)}
                                            className={buttonVariants({ variant: 'secondary', size: 'icon' })}
                                        >
                                            <Edit className="h-4 w-4 text-blue-500" />
                                        </Link>
                                        <ConfirmDialog
                                            title="Delete category?"
                                            description="Delete this category?"
                                            onConfirm={() =>
                                                router.delete(route('listing-category.destroy', row.id), {
                                                    onSuccess: () => toast.success('Category deleted.'),
                                                })
                                            }
                                            trigger={
                                                <AlertDialog.Trigger className={buttonVariants({ variant: 'secondary', size: 'icon' })}>
                                                    <Trash className="h-4 w-4 text-red-500" />
                                                </AlertDialog.Trigger>
                                            }
                                        />
                                    </div>
                                ) : (
                                    value
                                )
                            }
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
