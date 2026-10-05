/**
 * Body scroll lock that preserves the scroll position.
 *
 * Locking with `overflow: hidden` on <html> resets the viewport scroll to 0, so
 * sticky elements (top nav, table headers) appear to jump back to their natural
 * position. Instead we freeze the body in place with `position: fixed` offset by
 * the current scroll, then restore it on unlock.
 *
 * Reference-counted so nested overlays (e.g. a confirm dialog over a panel)
 * don't unlock too early. Exposed as `window.appScrollLock`.
 */
export default function registerScrollLock() {
    let count = 0;
    let savedY = 0;

    const lock = () => {
        count += 1;
        if (count > 1) return;

        savedY = window.scrollY || document.documentElement.scrollTop || 0;
        const body = document.body;
        body.style.position = 'fixed';
        body.style.top = `-${savedY}px`;
        body.style.left = '0';
        body.style.right = '0';
        body.style.width = '100%';
    };

    const unlock = () => {
        if (count === 0) return;
        count -= 1;
        if (count > 0) return;

        const body = document.body;
        body.style.position = '';
        body.style.top = '';
        body.style.left = '';
        body.style.right = '';
        body.style.width = '';
        window.scrollTo(0, savedY);
    };

    window.appScrollLock = { lock, unlock };
}
