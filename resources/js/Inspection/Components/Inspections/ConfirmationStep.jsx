import React from 'react';

export default function ConfirmationStep({ data, questions, workAreas, shifts, processing, onBack, onSubmit }) {
    const area = workAreas.find((item) => String(item.id) === String(data.work_area_id));
    const shift = shifts.find((item) => String(item.id) === String(data.shift_id));
    const unsafe = data.answers.filter((answer) => answer.answer === 'no').length;
    return <div><div className="review-grid"><div><span>Area kerja</span><strong>{area?.name}</strong></div><div><span>Shift</span><strong>{shift?.name}</strong></div><div><span>Tanggal</span><strong>{data.inspection_date}</strong></div><div><span>Ringkasan</span><strong>{questions.length - unsafe} aman · {unsafe} temuan</strong></div></div><div className={`notice ${unsafe ? 'warning' : ''}`}><strong>{unsafe ? `${unsafe} kondisi perlu ditindaklanjuti` : 'Seluruh kondisi dinyatakan aman'}</strong><p>Periksa kembali jawaban sebelum inspeksi dikirim.</p></div><div className="confirm-list">{questions.map((question, index) => <div key={question.id}><span>{index + 1}. {question.question}</span><strong className={data.answers[index].answer === 'yes' ? 'safe' : 'unsafe'}>{data.answers[index].answer === 'yes' ? 'Ya' : 'Tidak'}</strong></div>)}</div><div className="wizard-actions"><button className="btn ghost" type="button" onClick={onBack}>← Ubah checklist</button><button className="btn primary" type="button" disabled={processing} onClick={onSubmit}>Kirim inspeksi</button></div></div>;
}
