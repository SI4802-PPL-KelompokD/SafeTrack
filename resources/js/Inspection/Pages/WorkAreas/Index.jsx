import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import SafeTrackLayout from '../../Layouts/SafeTrackLayout';
import FlashMessage from '../../Components/FlashMessage';
import Modal from '../../Components/Modal';
import PageHeader from '../../Components/PageHeader';
import Pagination from '../../Components/Pagination';

export default function WorkAreaIndex({ workAreas, filters, endpoints }) {
    const [createOpen, setCreateOpen] = useState(false);
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const applyFilters = (event) => { event.preventDefault(); router.get(endpoints.index, { search, status }, { preserveState: true, replace: true }); };
    const removeArea = (area) => confirm(`Hapus area kerja "${area.name}"?`) && router.delete(`/admin/work-areas/${area.id}`, { preserveScroll: true });

    return <SafeTrackLayout title="Kelola Area Kerja">
        <Head title="Kelola Area Kerja" />
        <PageHeader eyebrow="ADMINISTRASI" title="Data master" description="Satu referensi data untuk aktivitas keselamatan yang konsisten." action={<button className="btn primary" type="button" onClick={() => setCreateOpen(true)}>+ Tambah area</button>} />
        <FlashMessage />
        <div style={{ display: 'flex', gap: '8px', marginBottom: '24px', flexWrap: 'wrap' }}>
            <button type="button" style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', padding: '8px 16px', background: '#fff', border: '1px solid #e2e8f0', borderRadius: '6px', fontSize: '13.5px', fontWeight: 500, color: '#475569', cursor: 'pointer' }} onClick={() => alert('Modul Departemen dalam integrasi Data Master.')}>
                <span>Departemen</span> <span style={{ color: '#64748b', fontSize: '12px', fontWeight: 600 }}>2</span>
            </button>
            <a href="/shifts" style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', padding: '8px 16px', background: '#fff', border: '1px solid #e2e8f0', borderRadius: '6px', fontSize: '13.5px', fontWeight: 500, color: '#475569', textDecoration: 'none', cursor: 'pointer' }}>
                <span>Shift kerja</span>
            </a>
            <button type="button" style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', padding: '8px 16px', background: '#fff', border: '1px solid #e2e8f0', borderRadius: '6px', fontSize: '13.5px', fontWeight: 500, color: '#475569', cursor: 'pointer' }} onClick={() => alert('Modul Kategori Temuan dalam integrasi Data Master.')}>
                <span>Kategori temuan</span> <span style={{ color: '#64748b', fontSize: '12px', fontWeight: 600 }}>2</span>
            </button>
            <a href="/admin/work-areas" style={{ display: 'inline-flex', alignItems: 'center', gap: '8px', padding: '8px 16px', background: '#102a45', border: '1px solid #102a45', color: '#fff', borderRadius: '6px', fontSize: '13.5px', fontWeight: 600, textDecoration: 'none', cursor: 'pointer' }}>
                <span>Area &amp; pertanyaan</span> <span style={{ color: '#94a3b8', fontSize: '12px', fontWeight: 600 }}>{workAreas.total ?? workAreas.data?.length ?? 16}</span>
            </a>
        </div>
        <section className="panel"><form className="toolbar filter-toolbar" onSubmit={applyFilters}><label className="filter-search"><span aria-hidden="true">⌕</span><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Cari nama area..." /></label><select className="filter-select" value={status} onChange={(event) => setStatus(event.target.value)}><option value="">Semua status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><button className="btn" type="submit">Terapkan</button><button className="btn ghost" type="button" onClick={() => { setSearch(''); setStatus(''); router.get(endpoints.index); }}>Reset</button></form>
            <div className="table-wrap"><table><thead><tr><th>Area kerja</th><th>Pertanyaan</th><th>Status</th><th>Diperbarui</th><th>Aksi</th></tr></thead><tbody>{workAreas.data.map((area) => <tr key={area.id}><td><strong>{area.name}</strong></td><td>{area.questions_count}</td><td><span className={`badge ${area.is_active ? 'green' : 'gray'}`}>{area.is_active ? 'Aktif' : 'Nonaktif'}</span></td><td>{formatDate(area.updated_at)}</td><td><div className="row-actions"><Link className="action-btn action-edit" href={`/admin/work-areas/${area.id}`}>Ubah</Link><button type="button" className="action-btn action-delete" onClick={() => removeArea(area)}>Hapus</button></div></td></tr>)}</tbody></table>{workAreas.data.length === 0 && <div className="empty">Tidak ada area kerja yang cocok.</div>}</div><Pagination links={workAreas.links} />
        </section><CreateAreaWizard open={createOpen} storeUrl={endpoints.store} onClose={() => setCreateOpen(false)} />
    </SafeTrackLayout>;
}

function CreateAreaWizard({ open, storeUrl, onClose }) {
    const [step, setStep] = useState(1);
    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({ name: '', description: '', questions: ['', '', ''] });
    const close = () => { reset(); clearErrors(); setStep(1); onClose(); };
    const setQuestion = (index, value) => { const questions = [...data.questions]; questions[index] = value; setData('questions', questions); };
    const submit = (event) => { event.preventDefault(); post(storeUrl, { preserveScroll: true, onSuccess: close }); };
    return <Modal open={open} title="Tambah area kerja" onClose={close}><div className="wizard-progress"><span className={step === 1 ? 'active' : 'done'}>1</span><i /><span className={step === 2 ? 'active' : ''}>2</span></div>{step === 1 ? <div className="form-grid"><label className="full">Nama area kerja *<input value={data.name} onChange={(event) => setData('name', event.target.value)} placeholder="Contoh: Workshop Utama" required /><Error value={errors.name} /></label><label className="full">Deskripsi<textarea value={data.description} onChange={(event) => setData('description', event.target.value)} placeholder="Jelaskan lokasi atau fungsi area kerja." /><Error value={errors.description} /></label><div className="form-actions full"><button className="btn ghost" type="button" onClick={close}>Batal</button><button className="btn primary" type="button" disabled={!data.name.trim()} onClick={() => setStep(2)}>Lanjut ke pertanyaan →</button></div></div> : <form onSubmit={submit} className="initial-questions"><div className="wizard-copy"><strong>Tambahkan 3 pertanyaan awal</strong><p>Pertanyaan dapat diubah kembali setelah area tersimpan.</p></div>{data.questions.map((question, index) => <label key={index}><span>Pertanyaan {index + 1} *</span><textarea value={question} onChange={(event) => setQuestion(index, event.target.value)} placeholder={`Tulis pertanyaan inspeksi ${index + 1}`} required /><Error value={errors[`questions.${index}`]} /></label>)}<Error value={errors.questions} /><div className="form-actions"><button className="btn ghost" type="button" onClick={() => setStep(1)}>← Kembali</button><button className="btn primary" disabled={processing || data.questions.some((question) => !question.trim())}>Simpan area</button></div></form>}</Modal>;
}
function Error({ value }) { return value && <small className="field-error">{value}</small>; }
function formatDate(value) { return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value)); }

