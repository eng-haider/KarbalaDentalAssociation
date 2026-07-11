# نقابة أطباء الأسنان – كربلاء المقدسة | Karbala Dental Association

البوابة الرقمية الرسمية لنقابة أطباء الأسنان في كربلاء المقدسة.
A premium, government-style public website (frontend) built with Laravel Blade, Tailwind CSS v4, and Alpine.js — Arabic-first (RTL), responsive, SEO-optimized, accessible, and PWA-ready.

## ✨ Highlights

- **RTL-first Arabic design** with Cairo / IBM Plex Sans Arabic (+ Inter for Latin).
- **11 sections**: Hero, News, Announcements, Educational Center, Digital Services, Events, Media Center, Statistics, About, Contact, Footer.
- **Premium UX**: sticky header, mobile drawer, command-style search, animated hero, scroll-reveal, animated counters, lite-YouTube video facade, skeleton-ready cards, empty/success states.
- **PWA**: web manifest, service worker (offline fallback), maskable icons.
- **SEO**: per-page meta, Open Graph, Twitter cards, JSON-LD (`GovernmentOrganization`), canonical URLs.
- **Accessible**: skip link, focus-visible rings, reduced-motion support, semantic landmarks, progressive enhancement (content visible without JS).

## 🧱 Tech & Structure

- Laravel 11 · Tailwind CSS **v4** (CSS-first `@theme`, no `tailwind.config.js`) · Alpine.js (+ intersect / collapse / focus).
- Content is served from `app/Support/SiteData.php` as plain arrays via `HomeController` — swap for Eloquent models later without touching the views.

```
resources/views/
├── home.blade.php                 # assembles all sections
├── components/
│   ├── layouts/app.blade.php      # base layout (SEO, fonts, PWA, loader)
│   ├── icon.blade.php             # inline SVG icon set
│   ├── logo.blade.php             # crest emblem (SVG)
│   ├── cover.blade.php            # gradient placeholder cover art
│   ├── section-heading.blade.php  # shared section header
│   └── sections/                  # hero, news, announcements, education,
│                                  # services, events, media, statistics,
│                                  # about, contact
└── partials/                      # header, footer
```

## 🚀 Getting started

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate   # already done in this repo
npm run build        # or: npm run dev
php artisan serve
```

Open http://127.0.0.1:8000.

## 🎨 Design tokens

Defined in `resources/css/app.css` under `@theme` — brand blues (`#0F172A → #2563EB`),
slate neutrals (`#F8FAFC`, `#E2E8F0`), accents (`#0EA5E9`, `#22C55E`), soft shadows,
and named keyframe animations (float, shimmer, marquee, pulse-ring).

> Public frontend only — no admin dashboard / Filament resources by design.
