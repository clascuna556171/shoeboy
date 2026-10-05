/**
 * Reservation countdown.
 *
 * `x-data="reservationCountdown({ expiresAt, startedAt, releaseUrl, sku })"`
 * ticks down each second and, when the window elapses, POSTs to `releaseUrl`
 * to free the reserved pairs.
 */
export default function registerReservation(Alpine) {
    Alpine.data('reservationCountdown', (opts = {}) => ({
        now: Date.now(),
        expired: false,
        settled: false,
        busy: false,
        timer: null,

        get expiresAt() { return new Date(opts.expiresAt).getTime(); },
        get startedAt() { return new Date(opts.startedAt).getTime(); },
        get total() { return Math.max(1, this.expiresAt - this.startedAt); },
        get remainingMs() { return this.expiresAt - this.now; },
        get remainingSec() { return Math.max(0, Math.floor(this.remainingMs / 1000)); },
        get percent() { return Math.max(0, Math.min(100, (this.remainingMs / this.total) * 100)); },
        get urgent() { return this.remainingMs > 0 && (this.remainingSec <= 600 || this.percent <= 15); },
        get label() {
            let s = this.remainingSec;
            const h = Math.floor(s / 3600); s -= h * 3600;
            const m = Math.floor(s / 60); s -= m * 60;
            const pad = (n) => String(n).padStart(2, '0');
            return h > 0 ? `${h}:${pad(m)}:${pad(s)}` : `${m}:${pad(s)}`;
        },

        init() {
            this.tick();
            this.timer = setInterval(() => this.tick(), 1000);
        },
        destroy() {
            clearInterval(this.timer);
        },
        tick() {
            this.now = Date.now();
            if (!this.expired && this.remainingMs <= 0) {
                this.release();
            }
        },
        async release() {
            if (this.settled || this.busy) return;
            this.busy = true;
            try {
                const res = await fetch(opts.releaseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ reason: 'Reservation window elapsed (auto-released)' }),
                });
                const data = await res.json().catch(() => ({}));
                if (data.retry) {
                    this.busy = false;
                    return;
                }
                this.settled = true;
                this.busy = false;
                if (data.released) {
                    this.expired = true;
                    window.dispatchEvent(new CustomEvent('toast', { detail: {
                        type: 'info',
                        message: data.message || `Reservation${opts.sku ? ' ' + opts.sku : ''} expired and was released.`,
                    }}));
                }
            } catch (e) {
                this.busy = false;
            }
        },
    }));
}
