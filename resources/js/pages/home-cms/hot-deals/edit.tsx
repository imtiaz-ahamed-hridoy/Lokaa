import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button, buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { HomeHotDealsConfig } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { X } from 'lucide-react';
import { FormEventHandler, useMemo, useState } from 'react';
import { toast } from 'sonner';

interface Listing {
    id: number;
    title: string;
}
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
    config: HomeHotDealsConfig;
    listingTypes: ListingType[];
    categories: Category[];
    listings: Listing[];
}

export default function Edit({ config, listingTypes, categories, listings }: Props) {
    const { data, setData, errors, put, processing } = useForm({
        section_title: config.section_title ?? '',
        listing_type_id: String(config.listing_type_id ?? ''),
        number_of_listings: config.number_of_listings,
        listing_source: config.listing_source,
        listing_category_id: String(config.listing_category_id ?? ''),
        manual_listing_ids: (config.manual_listing_ids ?? []) as number[],
        status: config.status,
    });

    const [search, setSearch] = useState('');

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

    const filteredListings = listings.filter((l) => l.title.toLowerCase().includes(search.toLowerCase()));

    const toggleListing = (id: number) => {
        const current = data.manual_listing_ids;
        if (current.includes(id)) {
            setData(
                'manual_listing_ids',
                current.filter((i) => i !== id),
            );
        } else {
            setData('manual_listing_ids', [...current, id]);
        }
    };

    const removeListing = (id: number) => {
        setData(
            'manual_listing_ids',
            data.manual_listing_ids.filter((i) => i !== id),
        );
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('home-cms.hot-deals.update', config.id), {
            onSuccess: () => toast.success('Hot deals config updated.'),
            onError: () => toast.error('Failed to update hot deals config.'),
        });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Edit Hot Deals', href: `/home-cms/hot-deals/${config.id}/edit` }]}>
            <Head title="Edit Hot Deals Config" />
            <div className="m-5 flex flex-col gap-5">
                <Link className={buttonVariants({ className: 'ml-auto' })} href={route('home-cms.hot-deals.index')}>
                    Hot Deals Configs
                </Link>
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Hot Deals Configuration</CardTitle>
                    </CardHeader>
                    <form onSubmit={submit}>
                        <CardContent className="grid gap-4">
                            <div className="grid gap-2">
                                <Label>Section Title</Label>
                                <Input value={data.section_title} onChange={(e) => setData('section_title', e.target.value)} />
                                <InputError message={errors.section_title} />
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
                                <Select
                                    value={data.listing_source}
                                    onValueChange={(v) => setData('listing_source', v as 'manual' | 'featured' | 'newest' | 'trending' | 'random')}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="newest">Newest</SelectItem>
                                        <SelectItem value="featured">Featured</SelectItem>
                                        <SelectItem value="trending">Trending</SelectItem>
                                        <SelectItem value="manual">Manual</SelectItem>
                                        <SelectItem value="random">Random</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.listing_source} />
                            </div>

                            {/* Manual listing picker — shown only when source is Manual */}
                            {data.listing_source === 'manual' && (
                                <div className="grid gap-2">
                                    <Label>
                                        Select Listings <span className="text-muted-foreground text-xs">(all active products &amp; services)</span>
                                    </Label>

                                    {/* Selected badges */}
                                    {data.manual_listing_ids.length > 0 && (
                                        <div className="flex flex-wrap gap-2 rounded-md border p-2">
                                            {data.manual_listing_ids.map((id) => {
                                                const listing = listings.find((l) => l.id === id);
                                                return (
                                                    <Badge key={id} variant="secondary" className="flex items-center gap-1">
                                                        {listing?.title ?? `#${id}`}
                                                        <button
                                                            type="button"
                                                            onClick={() => removeListing(id)}
                                                            className="hover:text-destructive ml-1 rounded-full"
                                                        >
                                                            <X className="h-3 w-3" />
                                                        </button>
                                                    </Badge>
                                                );
                                            })}
                                        </div>
                                    )}

                                    {/* Search + checkbox list */}
                                    <Input placeholder="Search listings..." value={search} onChange={(e) => setSearch(e.target.value)} />
                                    <ScrollArea className="h-56 rounded-md border">
                                        <div className="flex flex-col gap-1 p-2">
                                            {filteredListings.length === 0 ? (
                                                <p className="text-muted-foreground py-4 text-center text-sm">No listings found.</p>
                                            ) : (
                                                filteredListings.map((listing) => (
                                                    <label
                                                        key={listing.id}
                                                        className="hover:bg-muted flex cursor-pointer items-center gap-3 rounded-md px-2 py-1.5 text-sm"
                                                    >
                                                        <Checkbox
                                                            checked={data.manual_listing_ids.includes(listing.id)}
                                                            onCheckedChange={() => toggleListing(listing.id)}
                                                        />
                                                        <span>{listing.title}</span>
                                                    </label>
                                                ))
                                            )}
                                        </div>
                                    </ScrollArea>
                                    <InputError message={errors.manual_listing_ids as string} />
                                </div>
                            )}

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
