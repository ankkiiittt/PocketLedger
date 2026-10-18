import { qs, qsa, on } from '../lib/dom';

export function initConfirmDialog(): void {
    const dialog = qs<HTMLDialogElement>('#confirm-modal');
    if (!dialog) {
        return;
    }

    const messageEl = qs<HTMLElement>('#confirm-modal-message', dialog);
    const cancelBtn = qs<HTMLButtonElement>('#confirm-modal-cancel', dialog);
    const confirmBtn = qs<HTMLButtonElement>('#confirm-modal-confirm', dialog);

    let pendingForm: HTMLFormElement | null = null;
    let openerElement: HTMLElement | null = null;

    const closeConfirm = () => {
        if (dialog.open) {
            dialog.close();
        }
        pendingForm = null;
        if (openerElement) {
            openerElement.focus();
            openerElement = null;
        }
    };

    on(cancelBtn ?? dialog, 'click', (e) => {
        if (e.target === cancelBtn) {
            closeConfirm();
        }
    });

    on(dialog, 'cancel', () => {
        closeConfirm();
    });

    on(confirmBtn ?? dialog, 'click', (e) => {
        if (e.target === confirmBtn && pendingForm) {
            const formToSubmit = pendingForm;
            // Mark form as confirmed to prevent re-intercepting
            formToSubmit.dataset['confirmed'] = 'true';
            dialog.close();
            if (typeof formToSubmit.requestSubmit === 'function') {
                formToSubmit.requestSubmit();
            } else {
                formToSubmit.submit();
            }
        }
    });

    const forms = qsa<HTMLFormElement>('form[data-confirm]');
    forms.forEach((form) => {
        on(form, 'submit', (e: Event) => {
            if (form.dataset['confirmed'] === 'true') {
                return; // Proceed with submit
            }

            e.preventDefault();
            const message = form.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
            if (messageEl) {
                messageEl.textContent = message;
            }

            pendingForm = form;
            const submitEvent = e as SubmitEvent;
            openerElement = (submitEvent.submitter as HTMLElement | null) || form;
            dialog.showModal();
            cancelBtn?.focus();
        });
    });
}
