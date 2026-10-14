import { useOptionalSidebar } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/react';

function normalizeLogoSrc(src?: string | null) {
    // Fallback to default logo if not provided
    if (!src) {
        return '/assets/brand/logo.png';
    }
    // If already an absolute URL (http/https) – return as‑is
    if (/^(?:https?:)?\/\//i.test(src)) {
        return src;
    }
    // Ensure the path starts with a leading slash
    const path = src.startsWith('/') ? src : `/${src}`;
    // Prepend origin to make it absolute – works for any route depth
    return `${window.location.origin}${path}`;
}

export default function AppLogo() {
    const { settings } = usePage().props as any;

    const sidebar = useOptionalSidebar();
    const isCollapsed = sidebar?.state === 'collapsed';
    const hasLogo = !!settings?.logo;
    const hasFavicon = !!settings?.favicon;
    const displayName = settings?.system_name || 'Admin Dashboard';

    // Normalized URLs for logo and favicon
    const logoSrc = normalizeLogoSrc(settings?.logo);
    const faviconSrc = normalizeLogoSrc(settings?.favicon);

    /* ── Collapsed sidebar: show logo (if available) or favicon, otherwise fallback ── */
    if (isCollapsed) {
        if (hasLogo) {
            return (
                <div className="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md">
                    <img src={logoSrc} alt={displayName} className="size-5 object-contain" />
                </div>
            );
        }
        if (hasFavicon) {
            return (
                <div className="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md">
                    <img src={faviconSrc} alt={displayName} className="size-5 object-contain" />
                </div>
            );
        }
        // fallback to first letter avatar
        return (
            <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-md text-sm font-semibold">
                {displayName.charAt(0).toUpperCase()}
            </div>
        );
    }

    /* ── Expanded sidebar: logo → system_name ── */
    if (hasLogo) {
        return (
            <div className="flex items-center justify-center overflow-hidden">
                <img src={logoSrc} alt={displayName} className="h-8 w-auto object-contain" />
            </div>
        );
    }

    return (
        <div className="flex flex-1 items-center text-left text-sm">
            <span className="truncate font-semibold">{displayName}</span>
        </div>
    );
}
