import InputError from '@/components/input-error';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useMemo } from 'react';
import { toast } from 'sonner';

interface ListingType {
    id: number;
    name: string;
}
interface Category {
    id: number;
    name: string;
    listing_type_id: number | null;
}
interface Props {
    listingTypes: ListingType[];
    categories: Category[];
}

export default function Create({ listingTypes, categories }: Props) {
    const { data, setData, errors, post, processing, reset } = useForm({
        title: '',
        listing_type_id: '',
        view_all_text: '',
        view_all_url: '',
        number_of_listings: 8,
        listing_source: 'featured',
        listing_category_id: '',
        manual_listing_ids: [] as number[],
        status: 'Active',
    });

    // Filter categories based on selected listing type
    const filteredCategories = useMemo(() => {
        if (!data.listing_type_id) return categories;
        return categories.filter((cat) => cat.listing_type_id === Number(data.listing_type_id));
    }, [data.listing_type_id, categories]);

    // Clear category if it doesn't belong to the selected listing type
    const handleListingTypeChange = (value: string) => {
        setData('listing_type_id', value);
        if (value && data.listing_category_id) {
            const catBelongsToType = categories.find((cat) => cat.id === Number(data.listing_category_id) && cat.listing_type_id === Number(value));
            if (!catBelongsToType) {
                setData('listing_category_id', '');
            }
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('home-cms.featured-services.store'), {
            onSuccess: () => {
                reset();
                toast.success('Featured services config created.');
            },
            onError: () => toast.error('Failed to create featured services config.'),
        });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Create Featured Services', href: '/home-cms/featured-services/create' }]}>
            <Head title="Create Featured Services Config" />
            <div className="m-5 flex flex-col gap-5">
                <Link className={buttonVariants({ className: 'ml-auto' })} href={route('home-cms.featured-services.index')}>
                    Featured Services
                </Link>
                <Card>
                    <CardHeader>
                        <CardTitle>Create Featured Services Configuration</CardTitle>
                    </CardHeader>
                    <form onSubmit={submit}>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-2">
                                <Label>Section Title</Label>
                                <Input value={data.title} onChange={(e) => setData('title', e.target.value)} placeholder="e.g. Featured Services" />
                                <InputError message={errors.title} />
                            </div>

                            <div className="grid gap-2">
                                <Label>Listing Type</Label>
                                <Select value={data.listing_type_id} onValueChange={handleListingTypeChange}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Types" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All Types</SelectItem>
                                        {listingTypes.map((lt) => (
                                            <SelectItem key={lt.id} value={String(lt.id)}>
                                                {lt.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.listing_type_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label>View All Text</Label>
                                <Input value={data.view_all_text} onChange={(e) => setData('view_all_text', e.target.value)} />
                                <InputError message={errors.view_all_text} />
                            </div>
                            <div className="grid gap-2">
                                <Label>View All URL</Label>
                                <Input value={data.view_all_url} onChange={(e) => setData('view_all_url', e.target.value)} />
                                <InputError message={errors.view_all_url} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Number of Listings</Label>
                                <Input
                                    type="number"
                                    value={data.number_of_listings}
                                    onChange={(e) => setData('number_of_listings', Number(e.target.value))}
                                />
                                <InputError message={errors.number_of_listings} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Listing Source</Label>
                                <Select value={data.listing_source} onValueChange={(v) => setData('listing_source', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="featured">Featured</SelectItem>
                                        <SelectItem value="newest">Newest</SelectItem>
                                        <SelectItem value="popular">Popular</SelectItem>
                                        <SelectItem value="manual">Manual</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.listing_source} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Category Filter</Label>
                                <Select value={data.listing_category_id} onValueChange={(v) => setData('listing_category_id', v)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="All categories" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All Categories</SelectItem>
                                        {filteredCategories.map((cat) => (
                                            <SelectItem key={cat.id} value={String(cat.id)}>
                                                {cat.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.listing_category_id} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Status</Label>
                                <Select value={data.status} onValueChange={(v) => setData('status', v)}>
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
                            <Button type="button" variant="outline" onClick={() => reset()} disabled={processing}>
                                Cancel
                            </Button>
                            <Button disabled={processing}>Create</Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
