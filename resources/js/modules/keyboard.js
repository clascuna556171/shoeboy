/**
 * On-screen keyboard awareness.
 *
 * Mobile browsers don't reliably shrink the layout viewport for the keyboard,
 * so dialogs anchored near the bottom can be hidden behind it. We watch the
 * visual viewport, expose the keyboard height as `--kb`, and flag
 * `html.kb-open` so CSS can lift dialogs to the top. Focused fields inside a
 * dialog are also scrolled into view once the keyboard has animated in.
 */
export default function registerKeyboard() {
    const root = document.documentElement;
    const vv = window.visualViewport;
    if (!vv) return;

    const update = () => {
        const keyboard = Math.max(0, window.innerHeight - vv.height - vv.offsetTop);
        root.classList.toggle('kb-open', keyboard > 120);
        root.style.setProperty('--kb', Math.round(keyboard) + 'px');
    };

    vv.addEventListener('resize', update);
    vv.addEventListener('scroll', update);
    update();

    document.addEventListener('focusin', (e) => {
        const el = e.target;
        if (!el || typeof el.closest !== 'function' || !el.closest('.app-modal-panel')) return;
        setTimeout(() => {
            try { el.scrollIntoView({ block: 'center', behavior: 'smooth' }); } catch (err) { /* ignore */ }
        }, 250);
    });
}
