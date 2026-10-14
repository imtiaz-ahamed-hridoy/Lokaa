import ImageUpload from '@/components/ImageUpload';
import InputError from '@/components/input-error';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { HomeHeroSection } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { toast } from 'sonner';

interface Props {
    hero: HomeHeroSection;
}

export default function Edit({ hero }: Props) {
    const { data, setData, errors, put, processing } = useForm({
        badge: hero.badge ?? '',
        heading: hero.heading ?? '',
        description: hero.description ?? '',
        background_image: null as File | null,
        status: hero.status,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('home-cms.hero.update', hero.id), {
            forceFormData: true,
            onSuccess: () => toast.success('Hero section updated.'),
            onError: () => toast.error('Failed to update hero section.'),
        });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Edit Hero', href: `/home-cms/hero/${hero.id}/edit` }]}>
            <Head title="Edit Hero Section" />
            <div className="m-5 flex flex-col gap-5">
                <Link className={buttonVariants({ className: 'ml-auto' })} href={route('home-cms.hero.index')}>
                    Hero Sections
                </Link>
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Hero Section</CardTitle>
                    </CardHeader>
                    <form onSubmit={submit}>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-2">
                                <Label>Badge</Label>
                                <Input value={data.badge} onChange={(e) => setData('badge', e.target.value)} />
                                <InputError message={errors.badge} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Heading</Label>
                                <Input value={data.heading} onChange={(e) => setData('heading', e.target.value)} />
                                <InputError message={errors.heading} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Description</Label>
                                <Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} />
                                <InputError message={errors.description} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Background Image</Label>
                                <ImageUpload
                                    value={hero.background_image_url}
                                    onChange={(file) => setData('background_image', file as File | null)}
                                />
                                <InputError message={errors.background_image} />
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
