import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
    children?: NavItem[];
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    app_env?: string;
    notifications: {
        items: NotificationItem[];
        unread_count: number;
        resolved_count: number;
    } | null;
    [key: string]: unknown;
}

export interface NotificationItem {
    id: string;
    title: string;
    message: string;
    url: string | null;
    created_at: string | null;
    read_at: string | null;
    resolved_at: string | null;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface DynamicPage {
    id: number;
    page_title: string;
    page_slug: string;
    page_content: string;
    status: 'Active' | 'Inactive';
    created_at: string;
    updated_at: string;
}

export interface PaginatedDynamicPages {
    data: DynamicPage[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

export interface DynamicPage {
    id: number;
    page_title: string;
    page_slug: string;
    page_content: string;
    status: 'Active' | 'Inactive';
    created_at: string;
    updated_at: string;
}

export interface PaginatedDynamicPages {
    data: DynamicPage[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

export type ListingStatus = 'Draft' | 'Pending' | 'Active' | 'Expired' | 'Rejected' | 'Archived';
export type ListingPricingMode = 'fixed' | 'hourly' | 'daily';

export interface ListingType {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image_url: string | null;
    status: 'Active' | 'Inactive';
}

export interface ListingCategory {
    id: number;
    listing_type_id: number;
    name: string;
    slug: string;
    image_url: string | null;
    status: 'Active' | 'Inactive';
    type?: ListingType;
}

export interface ListingMedia {
    id: number;
    path: string;
    url: string | null;
}

export interface Listing {
    id: number;
    user_id: number;
    listing_type_id: number;
    listing_category_id: number;
    title: string;
    slug: string;
    description: string;
    pricing_mode: ListingPricingMode;
    price: string;
    price_display: string;
    city: string;
    area: string | null;
    street_address: string | null;
    location_display: string;
    status: ListingStatus;
    created_at: string;
    created_at_display: string;
    type?: ListingType;
    category?: ListingCategory;
    cover_media?: ListingMedia;
    media?: ListingMedia[];
}

// Home CMS Types
export interface HomeHeroSection {
    id: number;
    badge: string | null;
    heading: string | null;
    description: string | null;
    background_image_url: string | null;
    status: 'Active' | 'Inactive';
    created_at?: string;
    updated_at?: string;
}

export interface HomeProcessItem {
    id: number;
    icon_url: string | null;
    title: string;
    description?: string;
    status: 'Active' | 'Inactive';
    created_at?: string;
    updated_at?: string;
}

export interface HomeExploreCategory {
    id: number;
    image: string | null;
    image_url: string | null;
    title: string;
    url: string | null;
    display_order: number;
    status: 'Active' | 'Inactive';
    created_at?: string;
    updated_at?: string;
}

export interface HomeHotDealsConfig {
    id: number;
    section_title: string | null;
    listing_type_id: number | null;
    number_of_listings: number;
    listing_source: 'newest' | 'featured' | 'trending' | 'manual' | 'random';
    listing_category_id: number | null;
    manual_listing_ids: number[] | null;
    status: 'Active' | 'Inactive';
    created_at?: string;
    updated_at?: string;
}

export interface HomeFeaturedServicesConfig {
    id: number;
    title: string | null;
    listing_type_id: number | null;
    number_of_listings: number;
    listing_source: 'featured' | 'newest' | 'popular' | 'manual';
    listing_category_id: number | null;
    manual_listing_ids: number[] | null;
    status: 'Active' | 'Inactive';
    created_at?: string;
    updated_at?: string;
}

export interface HomeCtaBanner {
    id: number;
    background_image: string | null;
    background_image_url: string | null;
    title: string | null;
    description: string | null;
    status: 'Active' | 'Inactive';
    created_at?: string;
    updated_at?: string;
}
