import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import SafeTrackLayout from '../../Layouts/SafeTrackLayout';
import FlashMessage from '../../Components/FlashMessage';
import PageHeader from '../../Components/PageHeader';
import Pagination from '../../Components/Pagination';
import InspectionWizard from '../../Components/Inspections/InspectionWizard';

export default function DailyInspectionIndex({ inspections, isAdmin, openCreateModal, workAreas, shifts, endpoints }) {
    const [wizardOpen, setWizardOpen] = useState(Boolean(openCreateModal));
    return <SafeTrackLayout title="Inspeksi Harian"><Head title="Inspeksi Harian" /><PageHeader eyebrow="AKTIVITAS KESELAMATAN" title="Inspeksi harian" description={isAdmin ? 'Lihat dan verifikasi hasil inspeksi yang dikirim akun pribadi pengguna.' : 'Periksa kondisi area, dokumentasikan temuan, dan pantau tindak lanjutnya.'} action={!isAdmin && <button type="button" className="btn primary" onClick={() => setWizardOpen(true)}>+ Buat inspeksi</button>} /><FlashMessage />
        <section className="stats"><div><span>Total inspeksi</span><strong>{inspections.total}</strong><small>Checklist terdokumentasi</small></div><div><span>Menunggu review</span><strong>{inspections.data.filter((item) => item.status === 'submitted').length}</strong><small>Perlu pemeriksaan Admin</small></div><div><span>Selesai</span><strong>{inspections.data.filter((item) => item.status === 'closed').length}</strong><small>Inspeksi ditutup</small></div></section>
        <section className="panel"><div className="panel-head"><div><h2>Daftar inspeksi</h2><p>{isAdmin ? 'Seluruh inspeksi di lingkungan kerja.' : 'Riwayat inspeksi yang Anda kirim.'}</p></div><span className="badge">{inspections.total} inspeksi</span></div><div className="table-wrap"><table><thead><tr><th>Area kerja</th><th>Pelaksana</th><th>Shift</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>{inspections.data.map((inspection) => <tr key={inspection.id}><td><strong>{inspection.work_area?.name}</strong><small>#{inspection.id}</small></td><td>{inspection.inspector?.name}</td><td>{inspection.shift?.name}</td><td>{formatDate(inspection.inspection_date)}</td><td><span className="badge">{statusLabel(inspection.status)}</span></td></tr>)}</tbody></table>{inspections.data.length === 0 && <div className="empty">Belum ada inspeksi yang dikirim.</div>}</div><Pagination links={inspections.links} /></section>
        {!isAdmin && <InspectionWizard open={wizardOpen} onClose={() => setWizardOpen(false)} workAreas={workAreas} shifts={shifts} endpoints={endpoints} />}
    </SafeTrackLayout>;
}

function statusLabel(status) { return { submitted: 'Menunggu Review', reviewed: 'Direview', closed: 'Selesai' }[status] || status; }
function formatDate(value) { return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(`${value}T00:00:00`)); }
