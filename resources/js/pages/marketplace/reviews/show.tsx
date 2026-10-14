import { buttonVariants } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';

type Review = {
    id: number;
    rating: number;
    description: string;
    created_at: string;
    reviewer?: { name: string; email: string; avatar?: string };
    reviewee?: { name: string; email: string; avatar?: string };
    listing?: { title: string; slug: string };
};

type Props = { review: Review };

export default function Show({ review }: Props) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Review Details', href: `/review/${review.id}` }]}>
            <Head title="Review Details" />
            <div className="m-5 flex flex-col gap-5">
                <Link className={buttonVariants({ className: 'ml-auto' })} href={route('review.index')}>
                    Reviews
                </Link>
                <Card>
                    <CardHeader>
                        <CardTitle>Review Details</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 text-sm">
                        <div>
                            <span className="font-medium">Rating:</span> {review.rating}/5
                        </div>
                        <div>
                            <span className="font-medium">Reviewer:</span> {review.reviewer?.name} ({review.reviewer?.email})
                        </div>
                        <div>
                            <span className="font-medium">Owner:</span> {review.reviewee?.name} ({review.reviewee?.email})
                        </div>
                        <div>
                            <span className="font-medium">Listing:</span> {review.listing?.title || '-'}
                        </div>
                        <div>
                            <span className="font-medium">Submitted:</span> {new Date(review.created_at).toLocaleString()}
                        </div>
                        <div className="rounded-md border p-4 leading-6">{review.description}</div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
