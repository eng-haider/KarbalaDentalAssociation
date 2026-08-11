/* ==========================================================================
   نقابة أطباء الأسنان – فرع كربلاء المقدسة
   Site interactions — vanilla JavaScript (no jQuery)
   ========================================================================== */
(function () {
    'use strict';

    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    /* -------------------------------------------------------------------
       Arabic-Indic digit helper
       ------------------------------------------------------------------- */
    const AR_DIGITS = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    const toArabicNumber = (n) =>
        Math.round(n).toLocaleString('en-US').replace(/\d/g, (d) => AR_DIGITS[d]);

    document.addEventListener('DOMContentLoaded', function () {

        /* ---------------------------------------------------------------
           1. Navbar scroll effect
           --------------------------------------------------------------- */
        const nav = $('#mainNav');
        const onScrollNav = () => {
            if (!nav) return;
            nav.classList.toggle('scrolled', window.scrollY > 20);
        };
        onScrollNav();
        window.addEventListener('scroll', onScrollNav, { passive: true });

        /* ---------------------------------------------------------------
           2. Reveal-on-scroll (fade in)
           --------------------------------------------------------------- */
        const revealEls = $$('.reveal');
        if ('IntersectionObserver' in window && revealEls.length) {
            const io = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in');
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            revealEls.forEach((el) => io.observe(el));
        } else {
            revealEls.forEach((el) => el.classList.add('in'));
        }

        /* ---------------------------------------------------------------
           3. Animated counters (statistics)
           --------------------------------------------------------------- */
        const counters = $$('[data-counter]');
        const runCounter = (el) => {
            const target = parseInt(el.dataset.target, 10) || 0;
            const suffix = el.dataset.suffix || '';
            const duration = 1800;
            const start = performance.now();
            const step = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
                el.textContent = toArabicNumber(target * eased) + suffix;
                if (progress < 1) requestAnimationFrame(step);
                else el.textContent = toArabicNumber(target) + suffix;
            };
            requestAnimationFrame(step);
        };

        if ('IntersectionObserver' in window && counters.length) {
            const cio = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        runCounter(entry.target);
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.4 });
            counters.forEach((el) => cio.observe(el));
        } else {
            counters.forEach(runCounter);
        }

        /* ---------------------------------------------------------------
           4. Activities gallery filter
           --------------------------------------------------------------- */
        const filterBtns = $$('.filter-btn');
        const galleryItems = $$('.gallery-col');
        filterBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                filterBtns.forEach((b) => b.classList.remove('active'));
                btn.classList.add('active');
                const filter = btn.dataset.filter;
                galleryItems.forEach((item) => {
                    const show = filter === 'all' || item.dataset.category === filter;
                    item.classList.toggle('d-none', !show);
                });
            });
        });

        /* ---------------------------------------------------------------
           5. Active nav link on scroll (scroll-spy)
           --------------------------------------------------------------- */
        // Scroll-spy only runs on the homepage (where the hash sections live);
        // on sub-pages the nav uses cross-page links and keeps its own active state.
        if ($('#hero')) {
            const sections = $$('main section[id], #hero');
            const navLinks = $$('.navbar-gov .nav-link');
            const spy = () => {
                const pos = window.scrollY + 160;
                let currentId = '';
                sections.forEach((sec) => {
                    if (sec.offsetTop <= pos) currentId = sec.id;
                });
                navLinks.forEach((link) => {
                    link.classList.toggle('active', link.getAttribute('href') === '#' + currentId);
                });
            };
            window.addEventListener('scroll', spy, { passive: true });
            spy();
        }

        /* ---------------------------------------------------------------
           6. Close mobile navbar after clicking a link
           --------------------------------------------------------------- */
        const navCollapseEl = $('#navMenu');
        if (navCollapseEl) {
            $$('#navMenu .nav-link').forEach((link) => {
                link.addEventListener('click', () => {
                    if (navCollapseEl.classList.contains('show')) {
                        const c = bootstrap.Collapse.getOrCreateInstance(navCollapseEl);
                        c.hide();
                    }
                });
            });
        }

        /* ---------------------------------------------------------------
           6b. Wire listing cards to their detail pages (by DOM order)
           --------------------------------------------------------------- */
        if (!document.body.classList.contains('detail-page')) {
            const wireCards = (selector, base, prefix) => {
                $$(selector).forEach((card, i) => {
                    const url = base + '?id=' + prefix + (i + 1);
                    $$('a', card).forEach((a) => {
                        if (a.getAttribute('href') === '#') a.setAttribute('href', url);
                    });
                });
            };
            wireCards('.news-card', 'news-detail.html', 'news');
            wireCards('.vcourse-card', 'course-detail.html', 'course');
        }

        /* ---------------------------------------------------------------
           7. Back to top
           --------------------------------------------------------------- */
        const toTop = $('#backToTop');
        if (toTop) {
            window.addEventListener('scroll', () => {
                toTop.classList.toggle('show', window.scrollY > 500);
            }, { passive: true });
            toTop.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        /* ---------------------------------------------------------------
           8. Language switch (label toggle — placeholder for i18n)
           --------------------------------------------------------------- */
        const langBtn = $('#langSwitch');
        const langLabel = $('#langLabel');
        if (langBtn && langLabel) {
            langBtn.addEventListener('click', () => {
                langLabel.textContent = langLabel.textContent.trim() === 'EN' ? 'ع' : 'EN';
            });
        }

        /* ---------------------------------------------------------------
           9. Contact form validation
           --------------------------------------------------------------- */
        const form = $('#contactForm');
        const success = $('#formSuccess');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                let valid = true;
                $$('input[required], textarea[required]', form).forEach((field) => {
                    const ok = field.checkValidity();
                    field.classList.toggle('is-invalid', !ok);
                    if (!ok) valid = false;
                });
                if (valid) {
                    form.reset();
                    if (success) {
                        success.classList.remove('d-none');
                        setTimeout(() => success.classList.add('d-none'), 6000);
                    }
                }
            });
            $$('input, textarea', form).forEach((field) => {
                field.addEventListener('input', () => {
                    if (field.classList.contains('is-invalid') && field.checkValidity()) {
                        field.classList.remove('is-invalid');
                    }
                });
            });
        }

        /* ---------------------------------------------------------------
           10. Newsletter form
           --------------------------------------------------------------- */
        const news = $('#newsletterForm');
        const newsMsg = $('#newsletterMsg');
        if (news) {
            news.addEventListener('submit', (e) => {
                e.preventDefault();
                const input = $('input', news);
                if (input && input.checkValidity()) {
                    news.reset();
                    if (newsMsg) newsMsg.classList.remove('d-none');
                } else if (input) {
                    input.classList.add('is-invalid');
                }
            });
        }

        /* ---------------------------------------------------------------
           11. Regulations — accordion on mobile, sidebar + panel on desktop

           Same markup both ways: the conditions panel is moved next to its
           own header on small screens so it opens in place (no jumping to
           the bottom of the page and back), and returns to the side column
           on wide screens.
           --------------------------------------------------------------- */
        const regItems = $('#regItems');
        const regPanels = $('#regPanels');
        if (regItems && regPanels) {
            const heads = $$('.reg-head', regItems);
            const bodyOf = (head) => document.getElementById(head.dataset.regTarget);
            const wide = window.matchMedia('(min-width: 992px)');
            let isWide = null;

            const open = (head, scroll) => {
                heads.forEach((h) => {
                    const on = h === head;
                    h.classList.toggle('is-active', on);
                    h.setAttribute('aria-expanded', String(on));
                    bodyOf(h).hidden = !on;
                });
                if (!scroll || wide.matches) return;
                // Collapsing a taller row above can push the tapped header
                // off-screen — pull it back under the sticky navbar.
                requestAnimationFrame(() => {
                    const top = head.getBoundingClientRect().top;
                    if (top < 130 || top > window.innerHeight - 120) {
                        window.scrollTo({ top: window.scrollY + top - 130, behavior: 'smooth' });
                    }
                });
            };

            const relayout = () => {
                if (isWide === wide.matches) return;
                isWide = wide.matches;
                heads.forEach((h) => {
                    // desktop: all panels live in the side column
                    // mobile: each panel sits inside its own row
                    (isWide ? regPanels : h.parentNode).appendChild(bodyOf(h));
                });
                // Desktop always shows one panel; mobile starts fully collapsed
                // so the whole list of types fits on one screen.
                const active = heads.find((h) => h.classList.contains('is-active'));
                if (isWide && !active) open(heads[0], false);
            };

            heads.forEach((head) => {
                head.addEventListener('click', () => {
                    const isOpen = head.classList.contains('is-active');
                    // on mobile a second tap closes the row again
                    if (isOpen && !wide.matches) {
                        head.classList.remove('is-active');
                        head.setAttribute('aria-expanded', 'false');
                        bodyOf(head).hidden = true;
                        return;
                    }
                    if (!isOpen) open(head, true);
                });
            });

            relayout();
            wide.addEventListener('change', relayout);
        }

        /* ---------------------------------------------------------------
           14. Complaint form
           --------------------------------------------------------------- */
        const complaintForm = $('#complaintForm');
        const complaintSuccess = $('#complaintSuccess');
        if (complaintForm) {
            const field = $('#complaintText', complaintForm);
            complaintForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const ok = field.checkValidity();
                field.classList.toggle('is-invalid', !ok);
                if (!ok) { field.focus(); return; }
                // NOTE: no backend yet — this confirms locally only.
                complaintForm.reset();
                if (complaintSuccess) {
                    complaintSuccess.classList.remove('d-none');
                    setTimeout(() => complaintSuccess.classList.add('d-none'), 8000);
                }
            });
            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid') && field.checkValidity()) {
                    field.classList.remove('is-invalid');
                }
            });
        }

        /* ---------------------------------------------------------------
           13. Featured event — countdown, calendar file, registration

           Event details come from the data-* attributes on #featured-event,
           so updating the event only means editing the markup.
           --------------------------------------------------------------- */
        const feSection = $('#featured-event');
        if (feSection) {
            const starts = new Date(feSection.dataset.eventDate);
            const ends = new Date(feSection.dataset.eventEnd || feSection.dataset.eventDate);
            const title = feSection.dataset.eventTitle || 'فعالية النقابة';
            const place = feSection.dataset.eventLocation || '';

            /* --- Countdown --- */
            const cdBox = $('#eventCountdown');
            const cdNote = $('#eventCdNote');
            const cdParts = {};
            if (cdBox) $$('[data-cd]', cdBox).forEach((el) => { cdParts[el.dataset.cd] = el; });

            const tick = () => {
                const left = starts - Date.now();
                if (left <= 0) {
                    // Event has started (or passed) — swap the timer for a notice.
                    if (cdBox) cdBox.hidden = true;
                    if (cdNote) {
                        cdNote.hidden = false;
                        cdNote.textContent = Date.now() > ends
                            ? 'انتهت هذه الفعالية. ترقّبوا الفعالية القادمة.'
                            : 'الفعالية جارية الآن';
                    }
                    return false;
                }
                const sec = Math.floor(left / 1000);
                const set = (k, v) => { if (cdParts[k]) cdParts[k].textContent = toArabicNumber(v); };
                set('days', Math.floor(sec / 86400));
                set('hours', Math.floor(sec / 3600) % 24);
                set('minutes', Math.floor(sec / 60) % 60);
                set('seconds', sec % 60);
                return true;
            };

            if (!isNaN(starts) && tick()) {
                const timer = setInterval(() => { if (!tick()) clearInterval(timer); }, 1000);
            }

            /* --- "Add to calendar" — build an .ics entirely client-side --- */
            const icsBtn = $('#eventIcs');
            if (icsBtn && !isNaN(starts)) {
                const stamp = (d) => d.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
                icsBtn.addEventListener('click', () => {
                    const ics = [
                        'BEGIN:VCALENDAR', 'VERSION:2.0',
                        'PRODID:-//Karbala Dental Association//Events//AR',
                        'BEGIN:VEVENT',
                        'UID:' + Date.now() + '@karbala-dental.iq',
                        'DTSTAMP:' + stamp(new Date()),
                        'DTSTART:' + stamp(starts),
                        'DTEND:' + stamp(ends),
                        'SUMMARY:' + title,
                        'LOCATION:' + place,
                        'END:VEVENT', 'END:VCALENDAR',
                    ].join('\r\n');
                    const url = URL.createObjectURL(new Blob([ics], { type: 'text/calendar;charset=utf-8' }));
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'event.ics';
                    a.click();
                    URL.revokeObjectURL(url);
                });
            }

            /* --- Registration form (same client-side pattern as contact) --- */
            const regForm = $('#eventRegForm');
            const regSuccess = $('#regSuccess');
            if (regForm) {
                regForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    let valid = true;
                    $$('input[required]', regForm).forEach((field) => {
                        const ok = field.checkValidity();
                        field.classList.toggle('is-invalid', !ok);
                        if (!ok) valid = false;
                    });
                    if (!valid) return;
                    // NOTE: no backend yet — this confirms locally only.
                    regForm.reset();
                    if (regSuccess) {
                        regSuccess.classList.remove('d-none');
                        setTimeout(() => regSuccess.classList.add('d-none'), 8000);
                    }
                });
                $$('input', regForm).forEach((field) => {
                    field.addEventListener('input', () => {
                        if (field.classList.contains('is-invalid') && field.checkValidity()) {
                            field.classList.remove('is-invalid');
                        }
                    });
                });
            }
        }

        /* ---------------------------------------------------------------
           12. Transaction search

           Reads the association's published transactions sheet and matches
           names the way the previous portal did: Arabic letter forms are
           folded together (أ/إ/آ→ا, ة→ه, ى→ي, diacritics dropped) so a
           doctor finds their record however they happen to type it.
           --------------------------------------------------------------- */
        const trxInput = $('#trxInput');
        if (trxInput) {
            const SHEET_URL = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSd5s-PrXwWHDK5' +
                'RV5BS6qe4LDY-ajWtfQ8R-G0DmkxHfX7APt0P7bWsSCvKzNbMFFXz_6ZgcZx8Jk9/pub?output=csv';
            const MIN_CHARS = 2;   // shorter queries match almost everything
            const MAX_RESULTS = 30;

            const trxState = $('#trxState');
            const trxResults = $('#trxResults');
            const trxClear = $('#trxClear');
            const trxStats = $('#trxStats');

            /* --- Arabic-aware text folding --- */
            const fold = (s) => String(s).toLowerCase().trim()
                .replace(/[أإآ]/g, 'ا')
                .replace(/ة/g, 'ه')
                .replace(/[ىي]/g, 'ي')
                .replace(/[ً-ْ]/g, '');

            /* --- Sørensen–Dice coefficient over character bigrams --- */
            const similarity = (a, b) => {
                a = a.replace(/\s+/g, '');
                b = b.replace(/\s+/g, '');
                if (a === b) return 1;
                if (a.length < 2 || b.length < 2) return 0;
                const grams = new Map();
                for (let i = 0; i < a.length - 1; i++) {
                    const g = a.slice(i, i + 2);
                    grams.set(g, (grams.get(g) || 0) + 1);
                }
                let hits = 0;
                for (let i = 0; i < b.length - 1; i++) {
                    const g = b.slice(i, i + 2);
                    const n = grams.get(g) || 0;
                    if (n > 0) { grams.set(g, n - 1); hits++; }
                }
                return (2 * hits) / (a.length + b.length - 2);
            };

            /* --- Minimal RFC-4180 CSV reader (names may contain commas) --- */
            const parseCsv = (text) => {
                const rows = [];
                let row = [], field = '', quoted = false;
                for (let i = 0; i < text.length; i++) {
                    const c = text[i];
                    if (quoted) {
                        if (c === '"') {
                            if (text[i + 1] === '"') { field += '"'; i++; } else quoted = false;
                        } else field += c;
                    } else if (c === '"') quoted = true;
                    else if (c === ',') { row.push(field); field = ''; }
                    else if (c === '\n') { row.push(field); rows.push(row); row = []; field = ''; }
                    else if (c !== '\r') field += c;
                }
                if (field || row.length) { row.push(field); rows.push(row); }
                return rows;
            };

            /* --- Category drives the row colour and chip --- */
            const categorise = (t) => {
                if (t.includes('انتماء')) return { key: 'join',     label: 'انتماء',      icon: 'bi-person-plus' };
                if (t.includes('بدون'))   return { key: 'noclinic', label: 'بدون عيادة',  icon: 'bi-person-badge' };
                if (t.includes('عيادة'))  return { key: 'clinic',   label: 'مع عيادة',    icon: 'bi-hospital' };
                return { key: 'other', label: 'معاملة', icon: 'bi-file-earmark-text' };
            };

            let records = null;         // [{ name, txn, nName, nTxn, cat }]
            let loading = null;         // in-flight promise, so we fetch once

            const setState = (html, kind) => {
                trxState.className = 'trx-state' + (kind ? ' trx-state--' + kind : '');
                trxState.innerHTML = html;
            };

            const load = () => {
                if (loading) return loading;
                setState('<span class="trx-spinner" aria-hidden="true"></span> جارٍ تحميل سجل المعاملات…', 'loading');
                loading = fetch(SHEET_URL)
                    .then((r) => { if (!r.ok) throw new Error(r.status); return r.text(); })
                    .then((text) => {
                        const rows = parseCsv(text).slice(1); // drop header
                        records = rows
                            .filter((r) => r.length > 1 && r[0].trim())
                            .map((r) => {
                                const name = r[0].trim(), txn = r[1].trim();
                                return { name, txn, nName: fold(name), nTxn: fold(txn), cat: categorise(txn) };
                            });
                        renderStats();
                        setState('', '');
                        return records;
                    })
                    .catch(() => {
                        loading = null; // allow a retry on the next keystroke
                        setState('<i class="bi bi-wifi-off" aria-hidden="true"></i> تعذّر تحميل سجل المعاملات حالياً. تحقق من اتصالك ثم حاول مرة أخرى.', 'error');
                        return null;
                    });
                return loading;
            };

            const renderStats = () => {
                if (!records) return;
                const n = { total: records.length, clinic: 0, noclinic: 0, join: 0 };
                records.forEach((r) => { if (n[r.cat.key] !== undefined) n[r.cat.key]++; });
                $$('[data-trx-stat]', trxStats).forEach((el) => {
                    el.textContent = toArabicNumber(n[el.dataset.trxStat] || 0);
                });
                trxStats.hidden = false;
            };

            /* --- Ranking mirrors the old portal: exact contains beats fuzzy --- */
            const search = (raw) => {
                const q = fold(raw);
                const score = (r) => {
                    let s = similarity(r.nName, q);
                    if (r.nName.includes(q)) s += 0.4;
                    if (r.nName.startsWith(q)) s += 0.2;
                    return Math.max(s, similarity(r.nTxn, q));
                };
                return records
                    .filter((r) => r.nName.includes(q) || r.nTxn.includes(q) || similarity(r.nName, q) > 0.35)
                    .map((r) => ({ r, s: score(r) }))
                    .sort((a, b) => b.s - a.s || a.r.txn.localeCompare(b.r.txn, 'ar'))
                    .slice(0, MAX_RESULTS)
                    .map((x) => x.r);
            };

            const escapeHtml = (s) => s.replace(/[&<>"]/g, (c) =>
                ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

            const render = (list, query) => {
                if (!list.length) {
                    trxResults.innerHTML = '';
                    setState(
                        '<i class="bi bi-hourglass-split" aria-hidden="true"></i> معاملتك قيد الانتظار.',
                        'empty');
                    return;
                }
                setState('<i class="bi bi-check2-circle" aria-hidden="true"></i> ' +
                    toArabicNumber(list.length) + ' نتيجة مطابقة' +
                    (list.length === MAX_RESULTS ? ' (اكتب اسماً أدق لتضييق النتائج)' : ''), 'found');
                trxResults.innerHTML = list.map((r) => `
                    <div class="trx-item trx-${r.cat.key}">
                        <span class="trx-item-icon"><i class="bi ${r.cat.icon}" aria-hidden="true"></i></span>
                        <span class="trx-item-text">
                            <strong>${escapeHtml(r.name)}</strong>
                            <small>${escapeHtml(r.txn)}</small>
                        </span>
                        <span class="trx-chip">${r.cat.label}</span>
                        <span class="trx-done"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> منجزة</span>
                    </div>`).join('');
            };

            const run = async () => {
                const raw = trxInput.value.trim();
                trxClear.hidden = !raw;
                if (raw.length < MIN_CHARS) {
                    trxResults.innerHTML = '';
                    setState(raw ? '<i class="bi bi-info-circle" aria-hidden="true"></i> اكتب حرفين على الأقل للبحث.' : '', raw ? 'hint' : '');
                    return;
                }
                if (!records && !(await load())) return;
                render(search(raw), raw);
            };

            let timer;
            trxInput.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(run, 220); // debounce so we filter once per pause
            });
            trxInput.addEventListener('focus', load, { once: true });
            trxClear.addEventListener('click', () => {
                trxInput.value = '';
                trxInput.focus();
                run();
            });

            // Warm the sheet up as the section comes into view, so the first
            // search feels instant instead of waiting on the network.
            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries, obs) => {
                    if (entries.some((e) => e.isIntersecting)) { load(); obs.disconnect(); }
                }, { rootMargin: '200px' });
                io.observe($('#transaction-search'));
            }
        }
    });
})();
