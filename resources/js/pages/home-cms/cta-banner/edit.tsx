import ImageUpload from '@/components/ImageUpload';
import InputError from '@/components/input-error';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { HomeCtaBanner } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { toast } from 'sonner';

interface Props {
    banner: HomeCtaBanner;
}

export default function Edit({ banner }: Props) {
    const { data, setData, errors, put, processing } = useForm({
        background_image: null as File | null,
        title: banner.title ?? '',
        description: banner.description ?? '',
        status: banner.status,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('home-cms.cta-banner.update', banner.id), {
            forceFormData: true,
            onSuccess: () => toast.success('CTA banner updated.'),
            onError: () => toast.error('Failed to update CTA banner.'),
        });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Edit CTA Banner', href: `/home-cms/cta-banner/${banner.id}/edit` }]}>
            <Head title="Edit CTA Banner" />
            <div className="m-5 flex flex-col gap-5">
                <Link className={buttonVariants({ className: 'ml-auto' })} href={route('home-cms.cta-banner.index')}>
                    CTA Banners
                </Link>
                <Card>
                    <CardHeader>
                        <CardTitle>Edit CTA Banner</CardTitle>
                    </CardHeader>
                    <form onSubmit={submit}>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-2">
                                <Label>Background Image</Label>
                                <ImageUpload
                                    value={banner.background_image_url ?? banner.background_image ?? null}
                                    onChange={(file) => setData('background_image', file as File | null)}
                                />
                                <InputError message={errors.background_image} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Title</Label>
                                <Input value={data.title} onChange={(e) => setData('title', e.target.value)} />
                                <InputError message={errors.title} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Description</Label>
                                <Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} />
                                <InputError message={errors.description} />
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
