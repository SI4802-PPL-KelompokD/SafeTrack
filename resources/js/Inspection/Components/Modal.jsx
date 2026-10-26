import React from 'react';

export default function Modal({ open, title, onClose, children, wide = false }) {
    if (!open) return null;
    return <div className="modal-layer" role="presentation" onMouseDown={(event) => event.target === event.currentTarget && onClose()}><section className={`modal ${wide ? 'wide' : ''}`} role="dialog" aria-modal="true" aria-labelledby="modal-title"><header><h2 id="modal-title">{title}</h2><button type="button" aria-label="Tutup" onClick={onClose}>×</button></header><div className="modal-body">{children}</div></section></div>;
}
