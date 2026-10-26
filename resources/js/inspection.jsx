import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import '../css/inspection.css';

createInertiaApp({
    title: (title) => `${title} – SafeTrack`,
    resolve: (name) => {
        const pages = import.meta.glob('./Inspection/Pages/**/*.jsx', { eager: true });
        return pages[`./Inspection/Pages/${name}.jsx`];
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});

if ('serviceWorker' in navigator && (window.isSecureContext || window.location.hostname === 'localhost')) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
