import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import SafeTrackLayout from '../../Layouts/SafeTrackLayout';
import FlashMessage from '../../Components/FlashMessage';
import Modal from '../../Components/Modal';
import PageHeader from '../../Components/PageHeader';
import Pagination from '../../Components/Pagination';

export default function WorkAreaIndex({ workAreas, departments, filters, endpoints }) {
    const [modal, setModal] = useState(null);
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const applyFilters = (event) => { event.preventDefault(); router.get(endpoints.index, { search, status }, { preserveState: true, replace: true }); };

    return <SafeTrackLayout title="Kelola Area Kerja"><Head title="Kelola Area Kerja" /><PageHeader eyebrow="DATA MASTER" title="Kelola area kerja" description="Atur area dan pertanyaan inspeksi yang berlaku." action={<button className="btn primary" type="button" onClick={() => setModal({})}>+ Tambah area</button>} /><FlashMessage />
        <section className="panel"><form className="toolbar" onSubmit={applyFilters}><input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Cari nama area..." /><select value={status} onChange={(event) => setStatus(event.target.value)}><option value="">Semua status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><button className="btn" type="submit">Terapkan</button><button className="btn ghost" type="button" onClick={() => { setSearch(''); setStatus(''); router.get(endpoints.index); }}>Reset</button></form>
            <div className="table-wrap"><table><thead><tr><th>Area kerja</th><th>Departemen</th><th>Pertanyaan</th><th>Status</th><th>Diperbarui</th><th>Aksi</th></tr></thead><tbody>{workAreas.data.map((area) => <tr key={area.id}><td><strong>{area.name}</strong><small>{area.code || 'Tanpa kode'}</small></td><td>{area.department?.name}</td><td>{area.questions_count}</td><td><span className={`badge ${area.is_active ? 'green' : 'gray'}`}>{area.is_active ? 'Aktif' : 'Nonaktif'}</span></td><td>{formatDate(area.updated_at)}</td><td><div className="actions"><Link className="text-link" href={`/admin/work-areas/${area.id}`}>Detail</Link><button type="button" className="link-button" onClick={() => setModal(area)}>Ubah</button><button type="button" className="link-button" onClick={() => router.patch(`/admin/work-areas/${area.id}/toggle-status`, {}, { preserveScroll: true })}>{area.is_active ? 'Nonaktifkan' : 'Aktifkan'}</button></div></td></tr>)}</tbody></table>{workAreas.data.length === 0 && <div className="empty">Tidak ada area kerja yang cocok.</div>}</div><Pagination links={workAreas.links} />
        </section><AreaForm key={modal?.id || 'new'} open={modal !== null} area={modal} departments={departments} storeUrl={endpoints.store} onClose={() => setModal(null)} />
    </SafeTrackLayout>;
}

function AreaForm({ open, area, departments, storeUrl, onClose }) {
    const editing = Boolean(area?.id);
    const { data, setData, post, put, processing, errors, reset } = useForm({ department_id: area?.department_id || '', name: area?.name || '', code: area?.code || '', description: area?.description || '', is_active: area?.is_active ?? true });
    const close = () => { reset(); onClose(); };
    const submit = (event) => { event.preventDefault(); const options = { preserveScroll: true, onSuccess: close }; editing ? put(`/admin/work-areas/${area.id}`, options) : post(storeUrl, options); };
    return <Modal open={open} title={editing ? 'Ubah area kerja' : 'Tambah area kerja'} onClose={close}><form onSubmit={submit} className="form-grid"><label>Departemen *<select value={data.department_id} onChange={(event) => setData('department_id', event.target.value)} required><option value="">Pilih departemen</option>{departments.map((department) => <option key={department.id} value={department.id}>{department.name}</option>)}</select><Error value={errors.department_id} /></label><label>Nama area *<input value={data.name} onChange={(event) => setData('name', event.target.value)} required /><Error value={errors.name} /></label><label>Kode<input value={data.code} onChange={(event) => setData('code', event.target.value)} maxLength="30" /><Error value={errors.code} /></label><label className="full">Deskripsi<textarea value={data.description} onChange={(event) => setData('description', event.target.value)} /></label><label className="check full"><input type="checkbox" checked={data.is_active} onChange={(event) => setData('is_active', event.target.checked)} /> Area aktif</label><div className="form-actions full"><button className="btn ghost" type="button" onClick={close}>Batal</button><button className="btn primary" disabled={processing}>Simpan</button></div></form></Modal>;
}

function Error({ value }) { return value && <small className="field-error">{value}</small>; }
function formatDate(value) { return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value)); }
