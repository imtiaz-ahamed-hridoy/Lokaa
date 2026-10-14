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

type User = { id: number; name: string; email: string };
type Type = { id: number; name: string; slug: string };
type Category = { id: number; listing_type_id: number; name: string };
type Listing = {
    id: number;
    user_id: number;
    listing_type_id: number;
    listing_category_id: number;
    title: string;
    description: string;
    pricing_mode: string;
    price: string;
    country: string | null;
    city: string;
    area: string | null;
    street_address: string | null;
    status: string;
    is_featured?: boolean;
    is_verified?: boolean;
    media?: { id: number; url?: string | null; path: string; original_name?: string | null }[];
};
type Props = { users: User[]; types: Type[]; categories: Category[]; listing?: Listing };

export default function Create({ users, types, categories, listing }: Props) {
    const isEditing = Boolean(listing);
    const { data, setData, errors, post, processing, reset } = useForm({
        user_id: listing ? String(listing.user_id) : '',
        listing_type_id: listing ? String(listing.listing_type_id) : '',
        listing_category_id: listing ? String(listing.listing_category_id) : '',
        title: listing?.title || '',
        description: listing?.description || '',
        pricing_mode: listing?.pricing_mode || 'fixed',
        price: listing?.price || '',
        country: listing?.country || '',
        city: listing?.city || '',
        area: listing?.area || '',
        street_address: listing?.street_address || '',
        status: listing?.status || 'Pending',
        media: [] as File[],
        keep_media_ids: listing?.media?.map((item) => item.id) || [],
        is_featured: Boolean(listing?.is_featured),
        is_verified: Boolean(listing?.is_verified),
        _method: listing ? 'put' : '',
    });
    const selectedType = types.find((type) => String(type.id) === data.listing_type_id);
    const visibleCategories = categories.filter((category) => String(category.listing_type_id) === data.listing_type_id);
    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post(isEditing ? route('listing.update', listing?.id) : route('listing.store'), {
            forceFormData: true,
            onSuccess: () => {
                if (!isEditing) reset();
                toast.success(isEditing ? 'Listing updated.' : 'Listing created.');
            },
        });
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: isEditing ? 'Edit Listing' : 'Create Listing', href: isEditing ? `/listing/${listing?.id}/edit` : '/listing/create' },
            ]}
        >
            <Head title={isEditing ? 'Edit Listing' : 'Create Listing'} />
            <div className="m-5 flex flex-col gap-5">
                <Link className={buttonVariants({ className: 'ml-auto' })} href={route('listing.index')}>
                    Listings
                </Link>
                <Card>
                    <CardHeader>
                        <CardTitle>{isEditing ? 'Edit Listing' : 'Create Listing'}</CardTitle>
                    </CardHeader>
                    <form onSubmit={submit}>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-2">
                                <Label>Owner</Label>
                                <Select value={data.user_id} onValueChange={(value) => setData('user_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select owner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {users.map((user) => (
                                            <SelectItem key={user.id} value={String(user.id)}>
                                                {user.name} · {user.email}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.user_id} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Listing Type</Label>
                                <Select
                                    value={data.listing_type_id}
                                    onValueChange={(value) => {
                                        const type = types.find((item) => String(item.id) === value);
                                        setData({
                                            ...data,
                                            listing_type_id: value,
                                            listing_category_id: '',
                                            pricing_mode: type?.slug === 'service' ? 'daily' : 'fixed',
                                        });
                                    }}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select type" />
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
                                <Label>Category</Label>
                                <Select value={data.listing_category_id} onValueChange={(value) => setData('listing_category_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {visibleCategories.map((category) => (
                                            <SelectItem key={category.id} value={String(category.id)}>
                                                {category.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.listing_category_id} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Title</Label>
                                <Input value={data.title} onChange={(e) => setData('title', e.target.value)} required />
                                <InputError message={errors.title} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Description</Label>
                                <Textarea value={data.description} onChange={(e) => setData('description', e.target.value)} required />
                                <InputError message={errors.description} />
                            </div>
                            {selectedType?.slug === 'service' && (
                                <div className="grid gap-2">
                                    <Label>Pricing Mode</Label>
                                    <Select value={data.pricing_mode} onValueChange={(value) => setData('pricing_mode', value)}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="daily">Daily</SelectItem>
                                            <SelectItem value="hourly">Hourly</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.pricing_mode} />
                                </div>
                            )}
                            <div className="grid gap-2">
                                <Label>
                                    {data.pricing_mode === 'hourly' ? 'Hourly price' : data.pricing_mode === 'daily' ? 'Daily price' : 'Price'}
                                </Label>
                                <Input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={data.price}
                                    onChange={(e) => setData('price', e.target.value)}
                                    required
                                />
                                <InputError message={errors.price} />
                            </div>
                            <div className="grid gap-4 md:grid-cols-4">
                                <div className="grid gap-2">
                                    <Label>Country</Label>
                                    <Input value={data.country} onChange={(e) => setData('country', e.target.value)} />
                                    <InputError message={errors.country} />
                                </div>
                                <div className="grid gap-2">
                                    <Label>City</Label>
                                    <Input value={data.city} onChange={(e) => setData('city', e.target.value)} required />
                                    <InputError message={errors.city} />
                                </div>
                                <div className="grid gap-2">
                                    <Label>Area / Neighborhood</Label>
                                    <Input value={data.area} onChange={(e) => setData('area', e.target.value)} />
                                    <InputError message={errors.area} />
                                </div>
                                <div className="grid gap-2">
                                    <Label>Street Address</Label>
                                    <Input value={data.street_address} onChange={(e) => setData('street_address', e.target.value)} />
                                    <InputError message={errors.street_address} />
                                </div>
                            </div>
                            <div className="grid gap-2">
                                <Label>Status</Label>
                                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {['Draft', 'Pending', 'Active', 'Expired', 'Rejected', 'Archived'].map((status) => (
                                            <SelectItem key={status} value={status}>
                                                {status}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.status} />
                            </div>
                            <div className="grid gap-4 md:grid-cols-2">
                                <label className="flex items-center gap-2 text-sm">
                                    <input type="checkbox" checked={data.is_featured} onChange={(e) => setData('is_featured', e.target.checked)} />{' '}
                                    Featured
                                </label>
                                <label className="flex items-center gap-2 text-sm">
                                    <input type="checkbox" checked={data.is_verified} onChange={(e) => setData('is_verified', e.target.checked)} />{' '}
                                    Verified
                                </label>
                            </div>
                            {isEditing && listing?.media?.length ? (
                                <div className="grid gap-2">
                                    <Label>Existing Images</Label>
                                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                        {listing.media.map((item) => (
                                            <label key={item.id} className="rounded-md border p-2 text-sm">
                                                <img
                                                    src={item.url || `/${item.path}`}
                                                    alt={item.original_name || 'Listing image'}
                                                    className="mb-2 aspect-video w-full rounded object-cover"
                                                />
                                                <span className="flex items-center gap-2">
                                                    <input
                                                        type="checkbox"
                                                        checked={data.keep_media_ids.includes(item.id)}
                                                        onChange={(e) =>
                                                            setData(
                                                                'keep_media_ids',
                                                                e.target.checked
                                                                    ? [...data.keep_media_ids, item.id]
                                                                    : data.keep_media_ids.filter((id) => id !== item.id),
                                                            )
                                                        }
                                                    />{' '}
                                                    Keep image
                                                </span>
                                            </label>
                                        ))}
                                    </div>
                                    <InputError message={errors.keep_media_ids} />
                                </div>
                            ) : null}
                            <div className="grid gap-2">
                                <Label>Media</Label>
                                <Input
                                    type="file"
                                    multiple
                                    accept=".jpg,.jpeg,.png,.gif,.webp"
                                    onChange={(e) => setData('media', Array.from(e.target.files || []))}
                                />
                                <p className="text-muted-foreground text-xs">
                                    {isEditing ? 'Leave empty to keep existing images. ' : ''}Upload at least 4 images. JPG, JPEG, PNG, GIF or WEBP.
                                    No maximum image count.
                                </p>
                                <InputError message={errors.media} />
                            </div>
                        </CardContent>
                        <CardFooter className="mt-5 justify-between">
                            <Button type="button" variant="outline" onClick={() => reset()} disabled={processing}>
                                Cancel
                            </Button>
                            <Button disabled={processing}>{isEditing ? 'Update' : 'Create'}</Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
