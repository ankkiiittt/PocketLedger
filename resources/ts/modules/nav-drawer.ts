import { qs, qsa, on } from '../lib/dom';

export function initNavDrawer(): void {
    const toggleBtn = qs<HTMLButtonElement>('[data-drawer-toggle]');
    const drawer = qs<HTMLElement>('[data-drawer]');
    const backdrop = qs<HTMLElement>('[data-drawer-backdrop]');
    const closeBtns = qsa<HTMLButtonElement>('[data-drawer-close]');

    if (!toggleBtn || !drawer || !backdrop) {
        return;
    }

    const openDrawer = () => {
        drawer.classList.add('is-open');
        backdrop.classList.add('is-open');
        toggleBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';

        // Focus first link in drawer
        const firstLink = qs<HTMLAnchorElement>('a, button', drawer);
        firstLink?.focus();
    };

    const closeDrawer = () => {
        drawer.classList.remove('is-open');
        backdrop.classList.remove('is-open');
        toggleBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        toggleBtn.focus();
    };

    on(toggleBtn, 'click', () => {
        const isOpen = drawer.classList.contains('is-open');
        if (isOpen) {
            closeDrawer();
        } else {
            openDrawer();
        }
    });

    on(backdrop, 'click', closeDrawer);

    closeBtns.forEach((btn) => {
        on(btn, 'click', closeDrawer);
    });

    on(document, 'keydown', (e: KeyboardEvent) => {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
            closeDrawer();
        }
    });

    // Close on navigation link click inside drawer
    const navLinks = qsa<HTMLAnchorElement>('a', drawer);
    navLinks.forEach((link) => {
        on(link, 'click', closeDrawer);
    });
}
