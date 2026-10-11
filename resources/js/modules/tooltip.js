/**
 * Shared truncation tooltip.
 *
 * Any element with a `data-tip` attribute (e.g. the <x-cell> component) or any
 * element carrying Tailwind's `.truncate` utility shows a small styled bubble on
 * hover/focus/tap — but only when its text is actually clipped, so short values
 * stay quiet. The bubble is teleported to <body> and positioned fixed so a
 * table's horizontal scroll container can never clip it.
 */
export default function registerTooltip() {
    const tip = document.createElement('div');
    tip.className = 'app-tooltip';
    tip.setAttribute('role', 'tooltip');
    tip.style.display = 'none';
    document.body.appendChild(tip);

    let current = null;

    // Explicit `data-tip` wins; otherwise fall back to a `.truncate` element's
    // own text so every clipped label is revealable without per-element markup.
    const targetOf = (el) => el.closest('[data-tip], .truncate');

    const textOf = (target) =>
        (target.getAttribute('data-tip') || target.textContent || '').trim();

    const hide = () => {
        tip.style.display = 'none';
        current = null;
    };

    const show = (target) => {
        const text = textOf(target);
        if (!text) return;

        // Only bother when the value is genuinely truncated.
        if (target.scrollWidth <= target.clientWidth + 1) return;

        tip.textContent = text;
        tip.style.display = 'block';

        const r = target.getBoundingClientRect();
        const tr = tip.getBoundingClientRect();

        let top = r.top - tr.height - 8;
        if (top < 8) top = r.bottom + 8;

        let left = r.left;
        const maxLeft = window.innerWidth - tr.width - 8;
        if (left > maxLeft) left = maxLeft;
        if (left < 8) left = 8;

        tip.style.top = `${Math.round(top)}px`;
        tip.style.left = `${Math.round(left)}px`;
        current = target;
    };

    document.addEventListener('mouseover', (e) => {
        const t = targetOf(e.target);
        if (t && t !== current) show(t);
    });

    document.addEventListener('mouseout', (e) => {
        if (current && !current.contains(e.relatedTarget)) hide();
    });

    document.addEventListener('focusin', (e) => {
        const t = targetOf(e.target);
        if (t) show(t);
    });

    document.addEventListener('focusout', () => hide());

    // Tap / click to reveal (phones have no hover).
    document.addEventListener('click', (e) => {
        const t = targetOf(e.target);
        if (t) {
            if (current === t) { hide(); } else { show(t); }
        } else {
            hide();
        }
    });

    window.addEventListener('scroll', hide, true);
    window.addEventListener('resize', hide);
}
