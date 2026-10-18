import { qsa, on } from '../lib/dom';

export function initSubmitGuard(): void {
    const forms = qsa<HTMLFormElement>('form:not([data-no-guard])');

    forms.forEach((form) => {
        on(form, 'submit', (e: Event) => {
            // If the form has data-confirm and hasn't been confirmed yet, do not guard yet
            if (form.hasAttribute('data-confirm') && form.dataset['confirmed'] !== 'true') {
                return;
            }

            const submitEvent = e as SubmitEvent;
            const submitBtn = (submitEvent.submitter as HTMLButtonElement | null) ||
                form.querySelector<HTMLButtonElement>('button[type="submit"]');

            if (!submitBtn) {
                return;
            }

            // Prevent double submits
            if (submitBtn.getAttribute('aria-busy') === 'true') {
                e.preventDefault();
                return;
            }

            submitBtn.setAttribute('aria-busy', 'true');
            submitBtn.disabled = true;

            const loadingText = submitBtn.getAttribute('data-loading-text') || 'Saving...';
            // Save original text in case of client-side validation failure
            submitBtn.dataset['originalText'] = submitBtn.textContent || '';
            submitBtn.textContent = loadingText;
        });
    });
}
