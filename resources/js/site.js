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
        if (complaintForm) {
            const field = $('#complaintText', complaintForm);
            // Validate client-side first; a valid form submits normally to Laravel.
            complaintForm.addEventListener('submit', (e) => {
                const ok = field.checkValidity();
                field.classList.toggle('is-invalid', !ok);
                if (!ok) { e.preventDefault(); field.focus(); }
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

            /* --- Registration form: validate client-side, then POST to Laravel --- */
            const regForm = $('#eventRegForm');
            if (regForm) {
                regForm.addEventListener('submit', (e) => {
                    let valid = true;
                    $$('input[required]', regForm).forEach((field) => {
                        const ok = field.checkValidity();
                        field.classList.toggle('is-invalid', !ok);
                        if (!ok) valid = false;
                    });
                    if (!valid) e.preventDefault();
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
           12. Transaction search — database-backed search
           --------------------------------------------------------------- */
        const trxInput = $('#trxInput');
        if (trxInput) {
            const API_URL = '/api/transactions/search';
            const MIN_CHARS = 2;
            const trxState = $('#trxState');
            const trxResults = $('#trxResults');
            const trxClear = $('#trxClear');
            const trxStats = $('#trxStats');

            const categorise = (t) => {
                if (t.includes('انتماء')) return { key: 'join',     label: 'انتماء',      icon: 'bi-person-plus' };
                if (t.includes('بدون'))   return { key: 'noclinic', label: 'بدون عيادة',  icon: 'bi-person-badge' };
                if (t.includes('عيادة'))  return { key: 'clinic',   label: 'مع عيادة',    icon: 'bi-hospital' };
                return { key: 'other', label: 'معاملة', icon: 'bi-file-earmark-text' };
            };

            let statsCache = null;

            const setState = (html, kind) => {
                trxState.className = 'trx-state' + (kind ? ' trx-state--' + kind : '');
                trxState.innerHTML = html;
            };

            const renderStats = (stats) => {
                if (!stats) return;
                $$('[data-trx-stat]', trxStats).forEach((el) => {
                    const key = el.dataset.trxStat;
                    el.textContent = toArabicNumber(stats[key] || 0);
                });
                trxStats.hidden = false;
            };

            const escapeHtml = (s) => s.replace(/[&<>"]/g, (c) =>
                ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

            const showModal = (name, type) => {
                $('#trxDetailName').textContent = name;
                $('#trxDetailType').textContent = type;
                const modal = bootstrap.Modal.getOrCreateInstance('#trxModal');
                modal.show();
            };

            const render = (results, stats, query) => {
                if (!results.length) {
                    trxResults.innerHTML = '';
                    setState(
                        '<i class="bi bi-search" aria-hidden="true"></i> لم نعثر على نتائج لـ "' +
                        escapeHtml(query) + '". جرب كتابة الاسم الأول أو اللقب فقط.', 'empty');
                    return;
                }
                setState('<i class="bi bi-check2-circle" aria-hidden="true"></i> ' +
                    toArabicNumber(results.length) + ' نتيجة مطابقة', 'found');
                trxResults.innerHTML = results.map((r) => {
                    const cat = categorise(r.transaction_type);
                    return `
                    <div class="trx-item trx-${cat.key}" role="button" tabindex="0" style="cursor: pointer;">
                        <span class="trx-item-icon"><i class="bi ${cat.icon}" aria-hidden="true"></i></span>
                        <span class="trx-item-text">
                            <strong>${escapeHtml(r.name)}</strong>
                            <small>${escapeHtml(r.transaction_type)}</small>
                        </span>
                        <span class="trx-chip">${cat.label}</span>
                        <span class="trx-done"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> منجزة</span>
                    </div>`;
                }).join('');

                // Add click handlers to result items
                $$('.trx-item', trxResults).forEach((item) => {
                    item.addEventListener('click', () => {
                        const name = item.querySelector('.trx-item-text strong').textContent;
                        const type = item.querySelector('.trx-item-text small').textContent;
                        showModal(name, type);
                    });
                    item.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            item.click();
                        }
                    });
                });
            };

            const run = async () => {
                const raw = trxInput.value.trim();
                trxClear.hidden = !raw;
                if (raw.length < MIN_CHARS) {
                    trxResults.innerHTML = '';
                    setState(raw ? '<i class="bi bi-info-circle" aria-hidden="true"></i> اكتب حرفين على الأقل للبحث.' : '', raw ? 'hint' : '');
                    return;
                }

                setState('<span class="trx-spinner" aria-hidden="true"></span> جارٍ البحث…', 'loading');
                try {
                    const response = await fetch(`${API_URL}?q=${encodeURIComponent(raw)}`);
                    if (!response.ok) throw new Error(response.status);
                    const data = await response.json();
                    if (statsCache !== data.stats) {
                        statsCache = data.stats;
                        renderStats(data.stats);
                    }
                    render(data.results, data.stats, raw);
                } catch {
                    setState('<i class="bi bi-wifi-off" aria-hidden="true"></i> تعذّر البحث حالياً. تحقق من اتصالك ثم حاول مرة أخرى.', 'error');
                }
            };

            const loadStats = async () => {
                try {
                    const response = await fetch(`${API_URL}?q=`);
                    if (response.ok) {
                        const data = await response.json();
                        statsCache = data.stats;
                        renderStats(data.stats);
                    }
                } catch {}
            };

            let timer;
            trxInput.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(run, 220);
            });
            trxInput.addEventListener('focus', loadStats, { once: true });
            trxClear.addEventListener('click', () => {
                trxInput.value = '';
                trxInput.focus();
                run();
            });

            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries, obs) => {
                    if (entries.some((e) => e.isIntersecting)) { loadStats(); obs.disconnect(); }
                }, { rootMargin: '200px' });
                io.observe($('#transaction-search'));
            }
        }
    });
})();
