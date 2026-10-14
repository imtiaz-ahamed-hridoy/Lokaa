// resources/js/components/Pagination.tsx
import { Link } from '@inertiajs/react';
import React from 'react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationProps {
    links: PaginationLink[];
}

const Pagination: React.FC<PaginationProps> = ({ links }) => {
    return (
        <div className="mt-6 flex justify-center space-x-1">
            {links.map((link, index) => (
                <Link
                    key={index}
                    href={link.url ?? ''}
                    className={`rounded border px-3 py-1 text-sm ${
                        link.active ? 'bg-blue-600 text-white' : link.url ? 'hover:bg-gray-100' : 'cursor-not-allowed text-gray-400'
                    }`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </div>
    );
};

export default Pagination;
