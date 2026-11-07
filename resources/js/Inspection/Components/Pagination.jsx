import React from 'react';
import { Link } from '@inertiajs/react';

export default function Pagination({ links = [] }) {
    if (links.length < 4) return null;
    return <nav className="pagination" aria-label="Paginasi">{links.map((link, index) => link.url ? <Link key={index} href={link.url} className={link.active ? 'active' : ''} preserveScroll>{cleanLabel(link.label)}</Link> : <span key={index}>{cleanLabel(link.label)}</span>)}</nav>;
}

function cleanLabel(label) {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›');
}
