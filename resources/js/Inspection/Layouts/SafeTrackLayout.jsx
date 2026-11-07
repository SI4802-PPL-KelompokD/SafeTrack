import React from 'react';
import { router, usePage } from '@inertiajs/react';

export default function SafeTrackLayout({ title, children }) {
    const { auth, navigation } = usePage().props;
    const user = auth.user;
    const initials = user.name.split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase();

    return (
        <div className="app-shell">
            <aside className="sidebar">
                <div className="brand"><span className="brand-mark">✓</span><div><strong>safe<span>Track</span></strong><small>SAFETY MANAGEMENT</small></div></div>
                <div className="workspace"><strong>PT Sentra Industri</strong><small>Workspace demonstrasi</small></div>
                <nav>
                    <p className="nav-label">UTAMA</p>
                    <a href={navigation.dashboard}>▦ <span>Ringkasan</span></a>
                    <a className={title === 'Inspeksi Harian' ? 'active' : ''} href={navigation.inspections}>☑ <span>Inspeksi Harian</span></a>
                    {user.isAdmin && <><p className="nav-label">ADMINISTRASI</p><a href={navigation.shifts}>◷ <span>Kelola Shift Kerja</span></a><a className={title.includes('Area Kerja') ? 'active' : ''} href={navigation.workAreas}>▤ <span>Data Master</span></a></>}
                </nav>
                <div className="sidebar-user"><span className="avatar">{initials}</span><div><strong>{user.name}</strong><small>{user.isAdmin ? 'Admin' : 'Karyawan'}</small></div><button type="button" title="Keluar" onClick={() => router.post(navigation.logout)}>⇥</button></div>
            </aside>
            <div className="main-shell">
                <header className="topbar"><span>Workspace › <strong>{title}</strong></span><div className="user-inline">Halo, <strong>{user.name}</strong><span className="avatar">{initials}</span></div></header>
                <main className="content">{children}</main>
            </div>
            <nav className="mobile-nav" aria-label="Navigasi utama">
                <a href={navigation.dashboard}><span>▦</span>Beranda</a>
                <a className={title === 'Inspeksi Harian' ? 'active' : ''} href={navigation.inspections}><span>☑</span>Inspeksi</a>
                {user.isAdmin && <a className={title.includes('Area Kerja') ? 'active' : ''} href={navigation.workAreas}><span>▤</span>Data Master</a>}
            </nav>
        </div>
    );
}