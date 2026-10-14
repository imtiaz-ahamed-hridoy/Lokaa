import ImageUpload from '@/components/ImageUpload';
import InputError from '@/components/input-error';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { toast } from 'sonner';

type ListingType = { id: number; name: string; slug: string; description: string | null; image_url: string | null; status: string };
type Props = { type: ListingType };

export default function Edit({ type }: Props) {
    const { data, setData, errors, put, processing } = useForm({
        name: type.name,
        slug: type.slug,
        description: type.description || '',
        image: null as File | null,
        status: type.status,
    });
    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        put(route('listing-type.update', type.id), { forceFormData: true, onSuccess: () => toast.success('Listing type updated.') });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Edit Listing Type', href: `/listing-type/${type.id}/edit` }]}>
            <Head title="Edit Listing Type" />
            <div className="m-5 flex flex-col gap-5">
                <Link className={buttonVariants({ className: 'ml-auto' })} href={route('listing-type.index')}>
                    Listing Types
                </Link>
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Listing Type</CardTitle>
                    </CardHeader>
                    <form onSubmit={submit}>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-2">
                                <Label>Name</Label>
                                <Input value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                                <InputError message={errors.name} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Slug</Label>
                                <Input value={data.slug} onChange={(e) => setData('slug', e.target.value)} />
                                <InputError message={errors.slug} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Description</Label>
                                <Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} />
                                <InputError message={errors.description} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Image (upload image or SVG)</Label>
                                <ImageUpload
                                    accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                    value={type.image_url}
                                    onChange={(file) => setData('image', file as File | null)}
                                />
                                <InputError message={errors.image} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Status</Label>
                                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
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
                            <Link className={buttonVariants({ variant: 'outline' })} href={route('listing-type.index')}>
                                Cancel
                            </Link>
                            <Button disabled={processing}>Update</Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
