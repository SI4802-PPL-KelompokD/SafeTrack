/* shifts.js — Dialog, scrollable time-picker, & admin-identity handler for Shift Kerja */

const confirmDialog = document.getElementById('admin-confirm');
const identityForm = document.getElementById('admin-confirm-form');
let pendingToggleForm = null;
let toggleSubmitter = null;

const timePickerPrefixes = ['add_start', 'add_end', 'edit_start', 'edit_end'];
const minuteSteps = ['00', '15', '30', '45'];

/* Initialize custom scrollable time pickers */
function initTimePickers() {
    timePickerPrefixes.forEach((pfx) => {
        const hScroll = document.getElementById(pfx + '_h_list');
        const mScroll = document.getElementById(pfx + '_m_list');
        if (!hScroll || !mScroll) return;

        hScroll.innerHTML = '';
        mScroll.innerHTML = '';

        for (let h = 0; h < 24; h++) {
            const hh = String(h).padStart(2, '0');
            const hItem = document.createElement('div');
            hItem.className = 'time-scroll-item';
            hItem.dataset.hour = hh;
            hItem.textContent = hh;
            hItem.addEventListener('click', (ev) => {
                ev.stopPropagation();
                selectHour(pfx, hh);
            });
            hScroll.appendChild(hItem);
        }

        minuteSteps.forEach((mm) => {
            const mItem = document.createElement('div');
            mItem.className = 'time-scroll-item';
            mItem.dataset.minute = mm;
            mItem.textContent = mm;
            mItem.addEventListener('click', (ev) => {
                ev.stopPropagation();
                selectMinute(pfx, mm);
            });
            mScroll.appendChild(mItem);
        });

        const curVal = document.getElementById(pfx + '_display')?.value || '07:00';
        applyPresetTime(pfx, curVal.substring(0, 2), curVal.substring(3, 5));
    });
}

function openTimePickerPopover(e, pfx) {
    if (e) e.stopPropagation();
    const popover = document.getElementById(pfx + '_popover');
    if (!popover) return;
    const wasOpen = popover.classList.contains('open');
    closeAllTimePickers();
    if (!wasOpen) {
        popover.classList.add('open');
        const activeH = popover.querySelector('.time-column-scroll .time-scroll-item.selected');
        if (activeH) activeH.scrollIntoView({ block: 'nearest' });
    }
}

function closeAllTimePickers() {
    document.querySelectorAll('.time-popover-box.open').forEach((pop) => {
        pop.classList.remove('open');
    });
}

function selectHour(pfx, h) {
    const hScroll = document.getElementById(pfx + '_h_list');
    if (!hScroll) return;
    hScroll.querySelectorAll('.time-scroll-item').forEach((item) => {
        item.classList.toggle('selected', item.dataset.hour === h);
    });
    updateTimeCombinedValue(pfx);
}

function selectMinute(pfx, m) {
    const mScroll = document.getElementById(pfx + '_m_list');
    if (!mScroll) return;
    mScroll.querySelectorAll('.time-scroll-item').forEach((item) => {
        item.classList.toggle('selected', item.dataset.minute === m);
    });
    updateTimeCombinedValue(pfx);
}

function updateTimeCombinedValue(pfx) {
    const hSelected = document.querySelector('#' + pfx + '_h_list .time-scroll-item.selected');
    const mSelected = document.querySelector('#' + pfx + '_m_list .time-scroll-item.selected');
    const h = hSelected ? hSelected.dataset.hour : '07';
    const m = mSelected ? mSelected.dataset.minute : '00';
    const combined = h + ':' + m;

    const displayInput = document.getElementById(pfx + '_display');
    if (displayInput) displayInput.value = combined;

    const badge = document.getElementById(pfx + '_badge');
    if (badge) badge.textContent = combined;

    // Map prefix to actual hidden form input (e.g. add_start -> add_starts_at)
    const hiddenId = (pfx.indexOf('start') !== -1)
        ? (pfx.startsWith('add') ? 'add_starts_at' : 'edit_starts_at')
        : (pfx.startsWith('add') ? 'add_ends_at' : 'edit_ends_at');

    const hiddenInput = document.getElementById(hiddenId);
    if (hiddenInput) {
        hiddenInput.value = combined;
        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function applyPresetTime(pfx, h, m) {
    const hScroll = document.getElementById(pfx + '_h_list');
    const mScroll = document.getElementById(pfx + '_m_list');
    if (!hScroll || !mScroll) return;

    hScroll.querySelectorAll('.time-scroll-item').forEach((item) => {
        item.classList.toggle('selected', item.dataset.hour === h);
    });
    mScroll.querySelectorAll('.time-scroll-item').forEach((item) => {
        item.classList.toggle('selected', item.dataset.minute === m);
    });
    updateTimeCombinedValue(pfx);
}

// Expose functions globally for onclick attributes
window.openTimePickerPopover = openTimePickerPopover;
window.closeAllTimePickers = closeAllTimePickers;
window.applyPresetTime = applyPresetTime;

/* Close popovers on outside click */
document.addEventListener('click', (e) => {
    if (!e.target.closest('.time-picker-wrapper')) {
        closeAllTimePickers();
    }
});

/* Open / Close dialogs */
document.addEventListener('click', (event) => {
    const open = event.target.closest('[data-open-dialog]');
    if (open) {
        const dialog = document.getElementById(open.dataset.openDialog);
        if (dialog) dialog.showModal();
    }
    const close = event.target.closest('[data-close-dialog]');
    if (close) {
        close.closest('dialog')?.close();
    }
});

/* Edit shift button — populate fields and open edit modal */
document.addEventListener('click', (event) => {
    const edit = event.target.closest('[data-edit-shift]');
    if (!edit) return;

    const form = document.getElementById('edit-shift-form');
    form.action = edit.dataset.url;
    document.getElementById('edit-name').value = edit.dataset.name || '';

    const startVal = (edit.dataset.starts || '07:00').substring(0, 5);
    const endVal = (edit.dataset.ends || '15:00').substring(0, 5);

    applyPresetTime('edit_start', startVal.substring(0, 2), startVal.substring(3, 5));
    applyPresetTime('edit_end', endVal.substring(0, 2), endVal.substring(3, 5));

    document.getElementById('edit-admin-name').value = '';
    document.getElementById('edit-action-reason').value = '';

    document.getElementById('edit-shift').showModal();
});

/* Toggle status form interception */
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-toggle-form]');
    if (!form) return;

    event.preventDefault();

    pendingToggleForm = form;
    toggleSubmitter = event.submitter;

    identityForm.reset();
    document.getElementById('identity-error').textContent = '';
    document.getElementById('confirm-title').textContent = form.dataset.confirmTitle || 'Konfirmasi status shift kerja';
    document.getElementById('confirm-entity').textContent = form.dataset.confirmEntity || '';
    document.getElementById('confirm-submit').textContent = form.dataset.confirmLabel || 'Simpan perubahan';

    document.getElementById('confirm-submit').disabled = false;
    confirmDialog.showModal();
    identityForm.elements.namedItem('admin_name').focus();
});

/* Confirm identity for toggle status */
identityForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!pendingToggleForm || !identityForm.reportValidity()) return;

    const name = identityForm.elements.namedItem('admin_name').value.trim();
    const reason = identityForm.elements.namedItem('action_reason').value.trim();

    if (name.length < 3 || reason.length < 10) {
        document.getElementById('identity-error').textContent = 'Isi nama Admin minimal 3 karakter dan alasan minimal 10 karakter.';
        return;
    }

    for (const [key, value] of Object.entries({ admin_name: name, action_reason: reason })) {
        let input = pendingToggleForm.querySelector(`[name="${key}"]`);
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            pendingToggleForm.append(input);
        }
        input.value = value;
    }

    document.getElementById('confirm-submit').disabled = true;
    if (toggleSubmitter) toggleSubmitter.disabled = true;
    HTMLFormElement.prototype.submit.call(pendingToggleForm);
});

/* Reset state on modal close */
confirmDialog?.addEventListener('close', () => {
    pendingToggleForm = null;
    toggleSubmitter?.focus();
});

/* Initialize when DOM is ready */
document.addEventListener('DOMContentLoaded', () => {
    initTimePickers();
    document.querySelector('dialog[data-reopen]')?.showModal();
});

if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initTimePickers();
}
