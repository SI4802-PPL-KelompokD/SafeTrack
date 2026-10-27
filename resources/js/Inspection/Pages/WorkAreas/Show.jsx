import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import SafeTrackLayout from '../../Layouts/SafeTrackLayout';
import FlashMessage from '../../Components/FlashMessage';
import Modal from '../../Components/Modal';
import PageHeader from '../../Components/PageHeader';
import Pagination from '../../Components/Pagination';

export default function WorkAreaShow({ workArea, questions, endpoints }) {
    const [question, setQuestion] = useState(null);
    return <SafeTrackLayout title="Ubah Area Kerja"><Head title={`Ubah ${workArea.name}`} /><PageHeader eyebrow="DATA MASTER" title="Ubah area kerja" description="Perbarui informasi area dan pertanyaan inspeksi." action={<div className="actions"><Link className="btn" href={endpoints.index}>← Kembali</Link><button className="btn primary" type="button" onClick={() => setQuestion({})}>+ Tambah pertanyaan</button></div>} /><FlashMessage />
        <AreaForm workArea={workArea} updateUrl={endpoints.update} />
        <section className="panel"><div className="panel-head"><div><h2>Pertanyaan inspeksi</h2><p>Atur pertanyaan yang digunakan pada area {workArea.name}.</p></div><span className="badge">{questions.total} pertanyaan</span></div>{questions.data.map((item) => <article className="question-row" key={item.id}><div><span className="order">{item.sort_order}</span><strong>{item.question}</strong><small>Diperbarui {formatDate(item.updated_at)}</small></div><div className="question-actions"><span className={`badge ${item.is_active ? 'green' : 'gray'}`}>{item.is_active ? 'Aktif' : 'Nonaktif'}</span><button className="action-btn action-edit" type="button" onClick={() => setQuestion(item)}>Ubah</button><button className={`action-btn ${item.is_active ? 'action-disable' : 'action-enable'}`} type="button" onClick={() => router.patch(`/admin/work-areas/${workArea.id}/questions/${item.id}/toggle-status`, {}, { preserveScroll: true })}>{item.is_active ? 'Nonaktifkan' : 'Aktifkan'}</button><button className="action-btn action-delete" type="button" onClick={() => confirm('Hapus pertanyaan ini?') && router.delete(`/admin/work-areas/${workArea.id}/questions/${item.id}`, { preserveScroll: true })}>Hapus</button></div></article>)}{questions.data.length === 0 && <div className="empty">Belum ada pertanyaan inspeksi untuk area ini.</div>}<Pagination links={questions.links} /></section>
        <QuestionForm key={question?.id || 'new'} open={question !== null} item={question} workArea={workArea} storeUrl={endpoints.questionsStore} onClose={() => setQuestion(null)} />
    </SafeTrackLayout>;
}

function AreaForm({ workArea, updateUrl }) {
    const { data, setData, put, processing, errors, recentlySuccessful } = useForm({
        name: workArea.name || '',
        description: workArea.description || '',
    });
    const submit = (event) => {
        event.preventDefault();
        put(updateUrl, { preserveScroll: true });
    };
    return <section className="panel area-editor"><div className="panel-head"><div><h2>Informasi area</h2><p>Ubah nama dan deskripsi area kerja.</p></div>{recentlySuccessful && <span className="save-indicator">Tersimpan</span>}</div><form onSubmit={submit} className="area-editor-form"><label>Nama area kerja *<input value={data.name} onChange={(event) => setData('name', event.target.value)} required /><Error value={errors.name} /></label><label>Deskripsi<textarea value={data.description} onChange={(event) => setData('description', event.target.value)} placeholder="Jelaskan lokasi atau fungsi area kerja." /><Error value={errors.description} /></label><div className="area-editor-actions"><button className="btn primary" disabled={processing}>Simpan perubahan</button></div></form></section>;
}

function QuestionForm({ open, item, workArea, storeUrl, onClose }) {
    const editing = Boolean(item?.id);
    const { data, setData, post, put, processing, errors, reset } = useForm({ question: item?.question || '', sort_order: item?.sort_order || 1, is_active: item?.is_active ?? true });
    const close = () => { reset(); onClose(); };
    const submit = (event) => { event.preventDefault(); const options = { preserveScroll: true, onSuccess: close }; editing ? put(`/admin/work-areas/${workArea.id}/questions/${item.id}`, options) : post(storeUrl, options); };
    return <Modal open={open} title={editing ? 'Ubah pertanyaan' : 'Tambah pertanyaan'} onClose={close}><form onSubmit={submit} className="form-grid"><label className="full">Teks pertanyaan *<textarea value={data.question} onChange={(event) => setData('question', event.target.value)} required /><Error value={errors.question} /></label><label>Urutan *<input type="number" min="1" value={data.sort_order} onChange={(event) => setData('sort_order', event.target.value)} required /><Error value={errors.sort_order} /></label><label className="check"><input type="checkbox" checked={data.is_active} onChange={(event) => setData('is_active', event.target.checked)} /> Pertanyaan aktif</label><div className="form-actions full"><button className="btn ghost" type="button" onClick={close}>Batal</button><button className="btn primary" disabled={processing}>Simpan</button></div></form></Modal>;
}

function Error({ value }) { return value && <small className="field-error">{value}</small>; }
function formatDate(value) { return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value)); }
