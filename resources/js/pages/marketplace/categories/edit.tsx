import ImageUpload from '@/components/ImageUpload';
import InputError from '@/components/input-error';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { toast } from 'sonner';

type Type = { id: number; name: string };
type Category = { id: number; listing_type_id: number; name: string; slug: string; image_url: string | null; status: string };
type Props = { category: Category; types: Type[] };

export default function Edit({ category, types }: Props) {
    const { data, setData, errors, put, processing } = useForm({
        listing_type_id: String(category.listing_type_id),
        name: category.name,
        slug: category.slug,
        image: null as File | null,
        status: category.status,
    });
    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        put(route('listing-category.update', category.id), { forceFormData: true, onSuccess: () => toast.success('Category updated.') });
    };
    return (
        <AppLayout breadcrumbs={[{ title: 'Edit Category', href: `/listing-category/${category.id}/edit` }]}>
            <Head title="Edit Category" />
            <div className="m-5 flex flex-col gap-5">
                <Link className={buttonVariants({ className: 'ml-auto' })} href={route('listing-category.index')}>
                    Categories
                </Link>
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Category</CardTitle>
                    </CardHeader>
                    <form onSubmit={submit}>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-2">
                                <Label>Listing Type</Label>
                                <Select value={data.listing_type_id} onValueChange={(value) => setData('listing_type_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {types.map((type) => (
                                            <SelectItem key={type.id} value={String(type.id)}>
                                                {type.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.listing_type_id} />
                            </div>
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
                                <Label>Image (upload image or SVG)</Label>
                                <ImageUpload
                                    accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                    value={category.image_url}
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
                            <Link className={buttonVariants({ variant: 'outline' })} href={route('listing-category.index')}>
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
