const confirmDialog = document.getElementById('admin-confirm');
const identityForm = document.getElementById('admin-confirm-form');
let pendingForm = null;
let submitter = null;

document.addEventListener('click', (event) => {
    const open = event.target.closest('[data-open-dialog]');
    if (open) document.getElementById(open.dataset.openDialog)?.showModal();
    const close = event.target.closest('[data-close-dialog]');
    if (close) close.closest('dialog')?.close();
    const edit = event.target.closest('[data-question-edit]');
    const create = event.target.closest('[data-question-new]');
    if (edit || create) {
        const form = document.getElementById('question-form');
        form.reset();
        form.querySelectorAll('[data-identity-value]').forEach(input => input.remove());
        form.action = edit ? edit.dataset.url : form.dataset.storeUrl;
        form.elements.namedItem('_method').value = edit ? 'PUT' : 'POST';
        form.elements.namedItem('_question_id').value = edit ? edit.dataset.id : '';
        form.elements.namedItem('question').value = edit ? edit.dataset.text : '';
        form.elements.namedItem('sort_order').value = edit ? edit.dataset.order : '1';
        document.getElementById('question-title').textContent = edit ? 'Ubah pertanyaan' : 'Tambah pertanyaan';
        document.getElementById('question-editor').showModal();
    }
});

document.querySelectorAll('[data-wizard]').forEach(form => {
    const steps = [...form.querySelectorAll('[data-wizard-step]')];
    const showStep = number => {
        steps.forEach(step => { step.hidden = step.dataset.wizardStep !== String(number); });
        form.querySelectorAll('[data-step-indicator]').forEach(step => step.classList.toggle('current', step.dataset.stepIndicator === String(number)));
        form.querySelector('[data-wizard-next]').hidden = number === 2;
        form.querySelector('[data-wizard-cancel]').hidden = number === 2;
        form.querySelector('[data-wizard-back]').hidden = number === 1;
        form.querySelector('[data-wizard-save]').hidden = number === 1;
    };
    form.querySelector('[data-wizard-next]').addEventListener('click', () => {
        for (const input of steps[0].querySelectorAll('input,textarea')) if (!input.reportValidity()) return;
        if (!form.elements.namedItem('name').value.trim()) return;
        showStep(2);
    });
    form.querySelector('[data-wizard-back]').addEventListener('click', () => showStep(1));
    form.addEventListener('submit', event => {
        const questions = [...form.querySelectorAll('[name^="questions["]')].map(input => input.value.trim().toLocaleLowerCase('id'));
        if (questions.some(text => !text) || new Set(questions).size !== 3) {
            event.preventDefault();
            event.stopImmediatePropagation();
            form.querySelector('[data-wizard-error]').textContent = 'Isi tiga pertanyaan awal yang berbeda.';
        }
    });
    showStep(1);
});

document.addEventListener('submit', event => {
    const form = event.target.closest('[data-admin-action]');
    if (!form) return;
    event.preventDefault();
    if (!form.reportValidity()) return;
    pendingForm = form;
    submitter = event.submitter;
    identityForm.reset();
    document.getElementById('identity-error').textContent = '';
    document.getElementById('confirm-title').textContent = form.dataset.confirmTitle || 'Konfirmasi simpan perubahan';
    document.getElementById('confirm-submit').textContent = form.dataset.confirmLabel || 'Simpan perubahan';
    confirmDialog.showModal();
    identityForm.elements.namedItem('admin_name').focus();
});

identityForm?.addEventListener('submit', event => {
    event.preventDefault();
    if (!pendingForm || !identityForm.reportValidity()) return;
    const name = identityForm.elements.namedItem('admin_name').value.trim();
    const reason = identityForm.elements.namedItem('action_reason').value.trim();
    if (name.length < 3 || reason.length < 10) {
        document.getElementById('identity-error').textContent = 'Isi nama Admin minimal 3 karakter dan alasan minimal 10 karakter.';
        return;
    }
    for (const [key, value] of Object.entries({ admin_name: name, action_reason: reason })) {
        let input = pendingForm.querySelector(`[name="${key}"]`);
        if (!input) { input = document.createElement('input'); input.type = 'hidden'; input.name = key; input.dataset.identityValue = ''; pendingForm.append(input); }
        input.value = value;
    }
    document.getElementById('confirm-submit').disabled = true;
    if (submitter) submitter.disabled = true;
    HTMLFormElement.prototype.submit.call(pendingForm);
});
confirmDialog?.addEventListener('close', () => { pendingForm = null; submitter?.focus(); });
document.querySelector('dialog[data-reopen]')?.showModal();
if ('serviceWorker' in navigator && window.isSecureContext) navigator.serviceWorker.register('/sw.js').catch(() => {});
