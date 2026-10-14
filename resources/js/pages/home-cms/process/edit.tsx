import ImageUpload from '@/components/ImageUpload';
import InputError from '@/components/input-error';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { HomeProcessItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { toast } from 'sonner';

interface Props {
    item: HomeProcessItem;
}

export default function Edit({ item }: Props) {
    const { data, setData, errors, put, processing } = useForm({
        icon: null as File | null,
        title: item.title,
        description: item.description || '',
        status: item.status,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('home-cms.process.update', item.id), {
            forceFormData: true,
            onSuccess: () => toast.success('Process item updated.'),
            onError: () => toast.error('Failed to update process item.'),
        });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Edit Process', href: `/home-cms/process/${item.id}/edit` }]}>
            <Head title="Edit Process Item" />
            <div className="m-5 flex flex-col gap-5">
                <Link className={buttonVariants({ className: 'ml-auto' })} href={route('home-cms.process.index')}>
                    Process Items
                </Link>
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Process Item</CardTitle>
                    </CardHeader>
                    <form onSubmit={submit}>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-2">
                                <Label>Icon (upload image or SVG)</Label>
                                <ImageUpload
                                    accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                    value={item.icon_url}
                                    onChange={(file) => setData('icon', file as File | null)}
                                />
                                <InputError message={errors.icon} />
                            </div>
                            <div className="grid gap-2">
                                <Label>
                                    Title <span className="text-red-500">*</span>
                                </Label>
                                <Input value={data.title} onChange={(e) => setData('title', e.target.value)} required />
                                <InputError message={errors.title} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Description</Label>
                                <Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} rows={4} />
                                <InputError message={errors.description as string | undefined} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Status</Label>
                                <Select value={data.status} onValueChange={(v) => setData('status', v as 'Active' | 'Inactive')}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.status} />
                            </div>
                        </CardContent>
                        <CardFooter className="mt-5 justify-between">
                            <Button type="button" variant="outline" onClick={() => window.history.back()} disabled={processing}>
                                Cancel
                            </Button>
                            <Button disabled={processing}>Update</Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
