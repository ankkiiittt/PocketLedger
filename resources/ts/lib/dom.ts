/**
 * Typed DOM query helpers
 */

export function qs<T extends HTMLElement = HTMLElement>(
    selector: string,
    parent: ParentNode = document
): T | null {
    return parent.querySelector<T>(selector);
}

export function qsa<T extends HTMLElement = HTMLElement>(
    selector: string,
    parent: ParentNode = document
): T[] {
    return Array.from(parent.querySelectorAll<T>(selector));
}

export function on<E extends Event = Event>(
    element: EventTarget,
    event: string,
    handler: (event: E) => void,
    options?: AddEventListenerOptions | boolean
): () => void {
    const listener = (e: Event) => handler(e as E);
    element.addEventListener(event, listener, options);
    return () => element.removeEventListener(event, listener, options);
}
