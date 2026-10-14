import { buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface Contact {
    id: number;
    name: string;
    email: string;
    topic: string;
    message: string;
    created_at: string;
}

interface Props {
    contact: Contact;
}

export default function Show({ contact }: Props) {
    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Contacts Management', href: '/contact' },
                { title: 'View Contact', href: `/contact/${contact.id}` },
            ]}
        >
            <Head title={`View Contact: ${contact.name}`} />

            <div className="m-5 mx-auto max-w-4xl">
                <Card className="overflow-hidden rounded-xl border-0 bg-white shadow-lg dark:bg-gray-900">
                    <CardHeader className="border-b border-gray-100 bg-gray-50/50 pb-6 dark:border-gray-800 dark:bg-gray-800/50">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <Link href={route('contact.index')} className={buttonVariants({ variant: 'outline', size: 'icon' })}>
                                    <ArrowLeft className="h-4 w-4" />
                                </Link>
                                <CardTitle className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Contact Message</CardTitle>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="p-8">
                        <div className="grid gap-8">
                            {/* Contact Details Header */}
                            <div className="grid grid-cols-1 gap-6 rounded-lg bg-gray-50 p-6 md:grid-cols-2 dark:bg-gray-800/50">
                                <div className="space-y-1">
                                    <h3 className="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Sender Name</h3>
                                    <p className="text-lg font-semibold text-gray-900 dark:text-white">{contact.name}</p>
                                </div>
                                <div className="space-y-1">
                                    <h3 className="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Email Address</h3>
                                    <p className="break-all text-lg font-medium text-blue-600 dark:text-blue-400">{contact.email}</p>
                                </div>
                                <div className="space-y-1">
                                    <h3 className="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Topic</h3>
                                    <p className="text-lg font-medium text-gray-900 dark:text-white">{contact.topic}</p>
                                </div>
                                <div className="space-y-1">
                                    <h3 className="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date Submitted</h3>
                                    <p className="text-lg font-medium text-gray-900 dark:text-white">
                                        {new Date(contact.created_at).toLocaleString()}
                                    </p>
                                </div>
                            </div>

                            {/* Message Content */}
                            <div className="space-y-3">
                                <h3 className="border-b border-gray-100 pb-2 text-sm font-medium uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                    Message Content
                                </h3>
                                <div className="mt-4 rounded-lg border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                    <p className="whitespace-pre-wrap text-base leading-relaxed text-gray-700 dark:text-gray-300">
                                        {contact.message}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
