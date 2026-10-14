import Create from './create';

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
type Props = { users: User[]; types: Type[]; categories: Category[]; listing: Listing };

export default function Edit(props: Props) {
    return <Create {...props} />;
}
