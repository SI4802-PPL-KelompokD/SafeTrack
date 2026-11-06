import React from 'react';
import Modal from './Modal';

export default function ConfirmDialog({
    open,
    title = 'Konfirmasi hapus',
    message,
    itemName,
    confirmLabel = 'Hapus',
    processing = false,
    onCancel,
    onConfirm,
}) {
    return <Modal open={open} title={title} onClose={processing ? () => {} : onCancel}>
        <div className="confirm-dialog-copy">
            <div className="confirm-dialog-icon" aria-hidden="true">!</div>
            <div>
                <p>{message}</p>
                {itemName && <strong>{itemName}</strong>}
                <small>Data akan dihapus dari daftar aktif, tetapi riwayatnya tetap tersimpan.</small>
            </div>
        </div>
        <div className="confirm-dialog-actions">
            <button className="btn ghost" type="button" disabled={processing} onClick={onCancel}>Batal</button>
            <button className="btn danger" type="button" disabled={processing} onClick={onConfirm} autoFocus>
                {processing ? 'Menghapus...' : confirmLabel}
            </button>
        </div>
    </Modal>;
}
