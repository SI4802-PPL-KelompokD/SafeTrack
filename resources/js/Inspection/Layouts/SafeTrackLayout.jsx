import React from 'react';
import { router, usePage } from '@inertiajs/react';

const icons = {
    dashboard: 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Zm0 9.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z',
    hazard: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
    inspection: 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.4 48.4 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 0 0 2.25 2.25h.75',
    talk: 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.2 48.2 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.4 48.4 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z',
    sap: 'm15.042 21.672-1.358-5.072m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.757l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59',
    bell: 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 0 0-12 0v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
    users: 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
    clock: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    building: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    data: 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125S3.75 14.278 3.75 12m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125',
    export: 'M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3',
};

const mainMenu = [['dashboard', 'Ringkasan', 'dashboard'], ['hazard', 'Laporan Bahaya'], ['inspection', 'Inspeksi Harian'], ['talk', 'Safety Talk'], ['sap', 'Pencapaian SAP'], ['bell', 'Notifikasi']];
const adminMenu = [['users', 'Kelola Karyawan'], ['clock', 'Kelola Shift Kerja', 'shifts'], ['data', 'Data Master', 'workAreas'], ['export', 'Ekspor Laporan'], ['clock', 'Jejak Audit']];

export default function SafeTrackLayout({ title, children }) {
    const { auth, navigation } = usePage().props;
    const user = auth.user;
    const initials = user.name.split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase();
    return <div className="app-shell">
        <aside className="app-sidebar" role="navigation" aria-label="Navigasi SafeTrack">
            <div className="brand-container"><div className="brand-logo-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 2 4 5v6.5c0 5 3.4 9.6 8 10.8 4.6-1.2 8-5.8 8-10.8V5l-8-3Z" fill="currentColor" fillOpacity=".2" stroke="currentColor" strokeWidth="2"/><path d="m9 12 2 2 4-4.5" stroke="#fff" strokeWidth="2.2" strokeLinecap="round"/></svg></div><div className="brand-text"><div className="brand-title">safe<span>Track</span></div><div className="brand-subtitle">SAFETY MANAGEMENT</div></div></div>
            <div className="workspace-card"><div className="workspace-icon"><Icon type="building" /></div><div className="workspace-info"><span className="workspace-name">PT Sentra Industri</span><span className="workspace-desc">Workspace demonstrasi</span></div></div>
            <Menu title="Utama" items={mainMenu} navigation={navigation} />
            {user.isAdmin && <Menu title="Administrasi" items={adminMenu} navigation={navigation} active="Data Master" />}
            <div className="sidebar-user-footer"><div className="user-meta-wrap"><div className="user-avatar-badge">{initials}</div><div className="user-name-title"><span className="user-full-name">{user.name}</span><span className="user-role-label">{user.role === 'super_admin' ? 'Super Admin' : 'Admin'}</span></div></div><button type="button" className="btn-user-exit" title="Keluar / Logout" onClick={() => router.post(navigation.logout)}><Icon type="export" /></button></div>
        </aside>
        <main className="app-main">
            <header className="app-topbar"><nav className="topbar-breadcrumbs"><a href={navigation.dashboard}>Workspace</a><span className="sep">›</span><span className="current">{title}</span></nav><div className="topbar-actions"><label className="topbar-search-box"><Icon type="search" /><input type="text" placeholder="Cari laporan bahaya..." aria-label="Cari laporan" /></label><button type="button" className="account-switch-btn"><span className="account-switch-label">Ganti Akun</span><span className="account-switch-name">{user.name}</span><span>⌄</span></button><button type="button" className="topbar-icon-btn" aria-label="Notifikasi"><Icon type="bell" /></button><div className="user-avatar-badge top-avatar">{initials}</div></div></header>
            <div className="content">{children}</div>
        </main>
        <nav className="mobile-nav" aria-label="Navigasi utama"><a href={navigation.dashboard}><span>▦</span>Beranda</a><a href={navigation.shifts}><span>◷</span>Shift</a><a className="active" href={navigation.workAreas}><span>▤</span>Data Master</a></nav>
    </div>;
}

function Menu({ title, items, navigation, active }) {
    return <><div className="nav-section-title">{title}</div><ul className="sidebar-menu">{items.map(([icon, label, routeKey]) => <li key={label}><a href={routeKey ? navigation[routeKey] : '#'} onClick={!routeKey ? (event) => event.preventDefault() : undefined} className={`sidebar-link ${active === label ? 'active' : ''}`}><Icon type={icon} /><span>{label}</span></a></li>)}</ul></>;
}
function Icon({ type }) {
    if (type === 'search') return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" d="m21 21-5.2-5.2M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>;
    return <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7"><path strokeLinecap="round" strokeLinejoin="round" d={icons[type]} /></svg>;
}
