import { qsa, on } from '../lib/dom';

export function initFlash(): void {
    const dismissButtons = qsa<HTMLButtonElement>('[data-flash-dismiss]');

    dismissButtons.forEach((btn) => {
        on(btn, 'click', () => {
            const banner = btn.closest('.flash-banner') as HTMLElement | null;
            if (banner) {
                banner.style.opacity = '0';
                banner.style.transform = 'translateY(-4px)';
                setTimeout(() => {
                    banner.remove();
                }, 150);
            }
        });
    });
}
