import { ConfirmDialog } from '@/components/alert-dialog';
import Table from '@/components/Table';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import { AlertDialog } from '@heroui/react';
import { Head, router } from '@inertiajs/react';
import { Eye, Trash } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface LogRow {
    id: string;
    datetime: string;
    env: string;
    level: string;
    message: string;
    short_message: string;
}

interface PaginatedLogs {
    data: LogRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Filters {
    search: string;
}

interface Props {
    logs: PaginatedLogs;
    filters: Filters;
}

export default function Index({ logs, filters: _filters }: Props) {
    const queryParams = new URLSearchParams(window.location.search);
    const search = queryParams.get('search') || '';

    const [selectedIds, setSelectedIds] = useState<string[]>([]);
    const [viewLog, setViewLog] = useState<LogRow | null>(null);

    const columns = [
        { label: '', key: 'select', sortable: false },
        { label: 'Time', key: 'datetime', sortable: true },
        { label: 'Env', key: 'env', sortable: true },
        { label: 'Level', key: 'level', sortable: true },
        { label: 'Message', key: 'short_message', sortable: false },
        { label: 'Actions', key: 'actions', sortable: false },
    ];

    const toggleSelection = (id: string) => {
        setSelectedIds((prev) => (prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]));
    };

    const toggleSelectAll = () => {
        if (selectedIds.length === logs.data.length && logs.data.length > 0) {
            setSelectedIds([]);
        } else {
            setSelectedIds(logs.data.map((log) => log.id));
        }
    };

    const deleteLog = (id: string) => {
        router.delete(route('system-log.destroy', id), {
            onSuccess: () => {
                toast.success('Log deleted successfully!');
                setSelectedIds((prev) => prev.filter((item) => item !== id));
            },
            onError: () => toast.error('Failed to delete log.'),
        });
    };

    const bulkDeleteLogs = () => {
        router.delete(route('system-log.bulkDestroy'), {
            data: { ids: selectedIds },
            onSuccess: () => {
                toast.success('Selected logs deleted successfully!');
                setSelectedIds([]);
            },
            onError: () => toast.error('Failed to delete logs.'),
        });
    };

    const clearAllLogs = () => {
        router.delete(route('system-log.clearAll'), {
            onSuccess: () => {
                toast.success('All logs cleared successfully!');
                setSelectedIds([]);
            },
            onError: () => toast.error('Failed to clear logs.'),
        });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'System Logs', href: '/system-log' }]}>
            <Head title="System Logs" />

            <div className="m-5">
                <Card>
                    <CardHeader>
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <h1 className="text-2xl font-semibold text-gray-800 dark:text-white">System Logs</h1>
                            <div className="flex flex-wrap items-center gap-2">
                                {selectedIds.length > 0 && (
                                    <ConfirmDialog
                                        title="Delete Selected Logs?"
                                        description={`Are you sure you want to delete ${selectedIds.length} logs?`}
                                        onConfirm={bulkDeleteLogs}
                                        trigger={
                                            <AlertDialog.Trigger className={buttonVariants({ variant: 'destructive' })}>
                                                <Trash className="mr-2 h-4 w-4" />
                                                Bulk Delete ({selectedIds.length})
                                            </AlertDialog.Trigger>
                                        }
                                    />
                                )}
                                {logs.data.length > 0 && (
                                    <ConfirmDialog
                                        title="Clear All Logs?"
                                        description="Are you sure you want to clear ALL logs? This action cannot be undone."
                                        onConfirm={clearAllLogs}
                                        trigger={
                                            <AlertDialog.Trigger className={buttonVariants({ variant: 'destructive' })}>
                                                <Trash className="mr-2 h-4 w-4" />
                                                Clear All
                                            </AlertDialog.Trigger>
                                        }
                                    />
                                )}
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <Table<LogRow & { select?: boolean; actions?: boolean }>
                            data={logs.data}
                            total={logs.total}
                            currentPage={logs.current_page}
                            rowsPerPage={logs.per_page}
                            columns={columns as any}
                            searchableKeys={['datetime', 'env', 'level', 'short_message']}
                            renderCell={(key, value, row) => {
                                if (key === 'select') {
                                    return <Checkbox checked={selectedIds.includes(row.id)} onCheckedChange={() => toggleSelection(row.id)} />;
                                }

                                if (key === 'level') {
                                    const isError = typeof value === 'string' && value.toLowerCase() === 'error';
                                    return (
                                        <span
                                            className={`rounded px-2 py-1 text-xs font-medium ${isError ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'}`}
                                        >
                                            {value}
                                        </span>
                                    );
                                }

                                if (key === 'actions') {
                                    return (
                                        <div className="flex gap-2">
                                            <Button variant="secondary" size="icon" onClick={() => setViewLog(row)}>
                                                <Eye className="h-4 w-4 text-blue-500" />
                                            </Button>

                                            <ConfirmDialog
                                                title="Delete this Log?"
                                                description="Are you sure you want to delete this log entry? This action cannot be undone."
                                                onConfirm={() => deleteLog(row.id)}
                                                trigger={
                                                    <AlertDialog.Trigger className={buttonVariants({ variant: 'secondary', size: 'icon' })}>
                                                        <Trash className="h-4 w-4 text-red-500" />
                                                    </AlertDialog.Trigger>
                                                }
                                            />
                                        </div>
                                    );
                                }

                                return value || '—';
                            }}
                            onPageChange={(page) => {
                                const params = Object.fromEntries(new URLSearchParams(window.location.search).entries());
                                router.get(window.location.pathname, { ...params, page }, { preserveState: true, replace: true });
                            }}
                            onPerPageChange={(perPage) => {
                                const params = Object.fromEntries(new URLSearchParams(window.location.search).entries());
                                router.get(
                                    window.location.pathname,
                                    { ...params, page: 1, per_page: perPage },
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
                                router.get(window.location.pathname, { ...params, page: 1 }, { preserveState: true, replace: true });
                            }}
                            searchValue={search}
                        />

                        <div className="mt-4">
                            <Button variant="outline" size="sm" onClick={toggleSelectAll}>
                                {selectedIds.length === logs.data.length && logs.data.length > 0 ? 'Deselect All' : 'Select All visible'}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Dialog open={!!viewLog} onOpenChange={(open) => !open && setViewLog(null)}>
                <DialogContent className="max-h-[90vh] max-w-7xl overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Log Details</DialogTitle>
                    </DialogHeader>
                    {viewLog && (
                        <div className="space-y-4">
                            <div className="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong>Date/Time:</strong> {viewLog.datetime}
                                </div>
                                <div>
                                    <strong>Environment:</strong> {viewLog.env}
                                </div>
                                <div>
                                    <strong>Level:</strong> {viewLog.level}
                                </div>
                            </div>
                            <div>
                                <h4 className="mb-2 font-semibold">Message</h4>
                                <pre className="whitespace-pre-wrap break-all rounded bg-gray-100 p-4 text-sm dark:bg-gray-900">
                                    {viewLog.message}
                                </pre>
                            </div>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
