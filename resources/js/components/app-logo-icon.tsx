import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/react';
import { SVGAttributes } from 'react';

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

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    const { settings } = usePage().props as any;

    if (settings?.logo) {
        return <img src={normalizeLogoSrc(settings.logo)} alt="Logo" className={cn(props.className, 'object-contain')} />;
    }

    if (settings?.favicon) {
        return <img src={normalizeLogoSrc(settings.favicon)} alt="Logo" className={cn(props.className, 'object-contain')} />;
    }

    return <img src="/assets/brand/logo.png" alt="Logo" className={cn(props.className, 'object-contain')} />;
}
