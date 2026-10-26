import React, { useState } from 'react';
import QuestionAnswer from './QuestionAnswer';

export default function ChecklistStep({ questions, answers, setAnswers, errors, onBack, onNext }) {
    const [completionError, setCompletionError] = useState('');
    const update = (index, answer) => {
        setCompletionError('');
        setAnswers(answers.map((current, position) => position === index ? answer : current));
    };
    const proceed = () => {
        const complete = answers.every((answer) => answer.answer && (answer.answer !== 'no' || (answer.description?.trim() && answer.photo)));
        if (!complete) {
            setCompletionError('Jawab semua pertanyaan. Untuk jawaban Tidak, foto dan deskripsi kondisi wajib dilengkapi.');
            return;
        }
        onNext();
    };
    return <div>{(errors.answers || completionError) && <div className="alert error">{errors.answers || completionError}</div>}{questions.map((question, index) => <QuestionAnswer key={question.id} index={index} question={question} answer={answers[index] || { question_id: question.id, answer: '', description: '', photo: null }} onChange={update} />)}<div className="wizard-actions"><button className="btn ghost" type="button" onClick={onBack}>← Kembali</button><button className="btn primary" type="button" onClick={proceed}>Tinjau inspeksi →</button></div></div>;
}