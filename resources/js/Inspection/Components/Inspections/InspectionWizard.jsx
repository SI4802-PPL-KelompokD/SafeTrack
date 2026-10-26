import React, { useEffect, useState } from 'react';
import { useForm } from '@inertiajs/react';
import Modal from '../Modal';
import ChecklistStep from './ChecklistStep';
import ConfirmationStep from './ConfirmationStep';
import ContextStep, { todayInJakarta } from './ContextStep';

export default function InspectionWizard({ open, onClose, workAreas, shifts, endpoints }) {
    const [step, setStep] = useState(1);
    const [questions, setQuestions] = useState([]);
    const [loading, setLoading] = useState(false);
    const [loadError, setLoadError] = useState('');
    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({ work_area_id: '', shift_id: '', inspection_date: todayInJakarta(), notes: '', answers: [] });
    useEffect(() => { if (!open) { setStep(1); setQuestions([]); setLoadError(''); clearErrors(); reset(); } }, [open]);
    const close = () => { if (!processing) onClose(); };
    const loadQuestions = async () => {
        setLoading(true); setLoadError('');
        try {
            const response = await fetch(endpoints.questions.replace('__AREA__', data.work_area_id), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!response.ok) throw new Error();
            const payload = await response.json();
            if (!payload.data.length) { setLoadError('Belum ada pertanyaan aktif untuk area ini. Hubungi Admin.'); return; }
            const answers = payload.data.map((question) => ({ question_id: question.id, answer: '', description: '', photo: null }));
            setQuestions(payload.data); setData('answers', answers); setStep(2);
        } catch { setLoadError('Pertanyaan gagal dimuat. Periksa koneksi dan coba kembali.'); } finally { setLoading(false); }
    };
    const submit = () => post(endpoints.store, { forceFormData: true, preserveScroll: true, onSuccess: close });
    return <Modal open={open} title="Buat inspeksi harian" onClose={close} wide><div className="steps">{['Pilih area', 'Isi checklist', 'Konfirmasi'].map((label, index) => <div className={step >= index + 1 ? 'active' : ''} key={label}><span>{index + 1}</span>{label}</div>)}</div>{loadError && <div className="alert error">{loadError}</div>}{step === 1 && <ContextStep data={data} setData={setData} workAreas={workAreas} shifts={shifts} errors={errors} onNext={loadQuestions} />}{step === 2 && <ChecklistStep questions={questions} answers={data.answers} setAnswers={(answers) => setData('answers', answers)} errors={errors} onBack={() => setStep(1)} onNext={() => setStep(3)} />}{step === 3 && <ConfirmationStep data={data} questions={questions} workAreas={workAreas} shifts={shifts} processing={processing} onBack={() => setStep(2)} onSubmit={submit} />}{loading && <div className="loading">Memuat pertanyaan…</div>}</Modal>;
}
