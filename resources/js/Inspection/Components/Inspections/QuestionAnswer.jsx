import React from 'react';

export default function QuestionAnswer({ index, question, answer, onChange }) {
    const update = (field, value) => onChange(index, { ...answer, question_id: question.id, [field]: value });
    const markSafe = () => onChange(index, { ...answer, question_id: question.id, answer: 'yes', description: '', photo: null });
    const unsafe = answer.answer === 'no';

    return <article className="inspection-question"><div className="question-title"><span>{index + 1}</span><strong>{question.question}</strong></div><div className="answer-options"><label><input type="radio" name={`answer-${question.id}`} checked={answer.answer === 'yes'} onChange={markSafe} /> Ya, aman</label><label><input type="radio" name={`answer-${question.id}`} checked={unsafe} onChange={() => update('answer', 'no')} /> Tidak, ada temuan</label></div>{unsafe && <div className="finding-fields"><label>Deskripsi kondisi *<textarea value={answer.description || ''} onChange={(event) => update('description', event.target.value)} required placeholder="Jelaskan kondisi atau kerusakan yang ditemukan" /></label><label>Foto kondisi *<input type="file" accept="image/jpeg,image/png,image/webp" onChange={(event) => update('photo', event.target.files[0] || null)} required /><small>JPG, PNG, atau WebP. Maksimal 5 MB.</small></label></div>}</article>;
}