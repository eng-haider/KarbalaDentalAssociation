import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

Alpine.plugin(intersect);
Alpine.plugin(collapse);
Alpine.plugin(focus);

/* -------------------------------------------------------------------------
 * Animated counter — counts up to `target` once it scrolls into view.
 * Usage: x-data="counter(1280)" x-intersect.once="start()"
 * ------------------------------------------------------------------------- */
Alpine.data('counter', (target = 0, duration = 1600) => ({
    current: 0,
    done: false,
    start() {
        if (this.done) return;
        this.done = true;
        const startTime = performance.now();
        const easeOut = (t) => 1 - Math.pow(1 - t, 3);
        const tick = (now) => {
            const progress = Math.min((now - startTime) / duration, 1);
            this.current = Math.floor(easeOut(progress) * target);
            if (progress < 1) requestAnimationFrame(tick);
            else this.current = target;
        };
        requestAnimationFrame(tick);
    },
    get formatted() {
        return new Intl.NumberFormat('ar-IQ').format(this.current);
    },
}));

/* -------------------------------------------------------------------------
 * Carousel — auto-playing slider with progress, pause-on-hover, keyboard
 * and swipe support. Used by the hero slider.
 * Usage: x-data="carousel(slideCount)"
 * ------------------------------------------------------------------------- */
Alpine.data('carousel', (count = 1, interval = 6500) => ({
    active: 0,
    count,
    interval,
    progress: 0,
    paused: false,
    timer: null,
    touchX: null,
    init() {
        // Respect users who prefer no motion: no autoplay.
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        this.run();
        // Pause autoplay when the tab is hidden.
        document.addEventListener('visibilitychange', () => {
            this.paused = document.hidden;
        });
    },
    run() {
        const tickMs = 40;
        this.timer = setInterval(() => {
            if (this.paused || this.count < 2) return;
            this.progress += (tickMs / this.interval) * 100;
            if (this.progress >= 100) this.next();
        }, tickMs);
    },
    go(i) {
        this.active = (i + this.count) % this.count;
        this.progress = 0;
    },
    next() { this.go(this.active + 1); },
    prev() { this.go(this.active - 1); },
    // Touch swipe (RTL-aware): swipe left → next, swipe right → prev.
    touchStart(e) { this.touchX = e.changedTouches[0].clientX; },
    touchEnd(e) {
        if (this.touchX === null) return;
        const dx = e.changedTouches[0].clientX - this.touchX;
        if (Math.abs(dx) > 40) (dx < 0 ? this.next() : this.prev());
        this.touchX = null;
    },
}));

/* -------------------------------------------------------------------------
 * Reveal-on-scroll helper (adds .is-visible). Pairs with .reveal in CSS.
 * Usage: x-data="reveal()" x-intersect.once="show()" class="reveal"
 * ------------------------------------------------------------------------- */
Alpine.data('reveal', () => ({
    show() {
        this.$el.classList.add('is-visible');
    },
}));

/* -------------------------------------------------------------------------
 * Lite YouTube facade — swaps thumbnail for the iframe only on click,
 * keeping the page fast (no third-party embeds until requested).
 * ------------------------------------------------------------------------- */
Alpine.data('litePlayer', (id) => ({
    playing: false,
    id,
    play() {
        this.playing = true;
    },
    get src() {
        return `https://www.youtube-nocookie.com/embed/${this.id}?autoplay=1&rel=0`;
    },
}));

window.Alpine = Alpine;
Alpine.start();

/* -------------------------------------------------------------------------
 * Register the PWA service worker (production only).
 * ------------------------------------------------------------------------- */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
