import { describe, it, expect, beforeEach } from 'vitest';
import { qs, qsa, on } from '../../resources/ts/lib/dom';

describe('dom helpers', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="container">
                <button class="item" data-id="1">Button 1</button>
                <button class="item" data-id="2">Button 2</button>
            </div>
        `;
    });

    it('qs finds a single element', () => {
        const el = qs<HTMLButtonElement>('.item');
        expect(el).not.toBeNull();
        expect(el?.getAttribute('data-id')).toBe('1');
    });

    it('qsa finds all matching elements as an array', () => {
        const items = qsa<HTMLButtonElement>('.item');
        expect(items.length).toBe(2);
        expect(items[1]?.getAttribute('data-id')).toBe('2');
    });

    it('on attaches event listener and returns unbind callback', () => {
        const btn = qs<HTMLButtonElement>('.item');
        let clicked = false;
        const unbind = on(btn!, 'click', () => {
            clicked = true;
        });

        btn?.click();
        expect(clicked).toBe(true);

        clicked = false;
        unbind();
        btn?.click();
        expect(clicked).toBe(false);
    });
});
