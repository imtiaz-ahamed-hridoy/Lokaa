import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    Ban,
    FolderDot,
    FolderTree,
    Home,
    Image,
    Layers,
    LayoutGrid,
    LayoutList,
    Mail,
    Newspaper,
    Percent,
    ScrollText,
    Sparkles,
    Star,
    Tags,
    Terminal,
    Users,
} from 'lucide-react';
import { useMemo } from 'react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'User Management',
        href: '/user',
        icon: Users,
    },
    {
        title: 'Marketplace',
        href: '#',
        icon: Tags,
        children: [
            {
                title: 'Listings',
                href: '/listing',
                icon: Tags,
            },
            {
                title: 'Listing Types',
                href: '/listing-type',
                icon: Layers,
            },
            {
                title: 'Categories',
                href: '/listing-category',
                icon: FolderTree,
            },
            {
                title: 'Reviews',
                href: '/review',
                icon: Star,
            },
        ],
    },
    {
        title: 'Home CMS',
        href: '#',
        icon: Home,
        children: [
            {
                title: 'Hero Section',
                href: '/home-cms/hero',
                icon: Image,
            },
            {
                title: 'Process Section',
                href: '/home-cms/process',
                icon: LayoutList,
            },
            {
                title: 'Hot Deals',
                href: '/home-cms/hot-deals',
                icon: Percent,
            },
            {
                title: 'Featured Services',
                href: '/home-cms/featured-services',
                icon: Sparkles,
            },
            {
                title: 'CTA Banner',
                href: '/home-cms/cta-banner',
                icon: Ban,
            },
        ],
    },
    {
        title: 'Content',
        href: '#',
        icon: ScrollText,
        children: [
            {
                title: 'Dynamic Pages',
                href: '/dynamic_page',
                icon: FolderDot,
            },
        ],
    },
    {
        title: 'Communications',
        href: '#',
        icon: Mail,
        children: [
            {
                title: 'Contacts',
                href: '/contact',
                icon: Mail,
            },
            {
                title: 'Newsletters',
                href: '/newsletter',
                icon: Newspaper,
            },
        ],
    },
    {
        title: 'System Logs',
        href: '/system-log',
        icon: Terminal,
    },
];

const footerNavItems: NavItem[] = [
    //
];

function markActiveItems(items: NavItem[], currentPath: string): NavItem[] {
    return items.map((item) => {
        let isActive = false;
        let hasActiveChild = false;

        if (item.children && item.children.length > 0) {
            const updatedChildren = item.children.map((child) => {
                const childActive = currentPath === child.href || currentPath.startsWith(child.href + '/');
                if (childActive) hasActiveChild = true;
                return { ...child, isActive: childActive };
            });
            isActive = hasActiveChild;
            return { ...item, children: updatedChildren, isActive };
        }

        isActive = currentPath === item.href || currentPath.startsWith(item.href + '/');
        return { ...item, isActive };
    });
}

export function AppSidebar() {
    const page = usePage();
    const { app_env } = usePage<SharedData>().props;
    const currentPath = page.url;

    const navItems = useMemo(() => {
        const items = app_env === 'local' ? mainNavItems : mainNavItems.filter((item) => item.title !== 'System Logs');
        return markActiveItems(items, currentPath);
    }, [app_env, currentPath]);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
