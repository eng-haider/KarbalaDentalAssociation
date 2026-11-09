<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#2e1065">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الحضور — OSIF KARBALA</title>
    <link rel="stylesheet" href="{{ asset('fonts/cairo/cairo.css') }}">
    <style>
        :root {
            --violet: #2e1065;
            --violet-soft: #ede9fe;
            --ink: #1f1a2e;
            --muted: #6b6480;
            --line: #e4e0ee;
            --page: #f5f3fa;
            --ok: #15803d;
            --warn: #b45309;
            --bad: #b91c1c;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body {
            min-height: 100vh;
            font-family: 'Cairo', system-ui, sans-serif;
            color: var(--ink);
            background: var(--page);
            -webkit-tap-highlight-color: transparent;
        }
        button, input { font: inherit; }

        .bar {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 16px; padding-top: max(12px, env(safe-area-inset-top));
            color: #fff; background: var(--violet);
        }
        .bar h1 { margin: 0; font-size: 1.05rem; font-weight: 800; line-height: 1.3; }
        .bar small { display: block; font-size: .78rem; font-weight: 600; opacity: .75; }
        .bar form { margin: 0; }
        .bar button {
            min-height: 40px; padding: 6px 14px; border: 1px solid rgba(255,255,255,.4); border-radius: 8px;
            color: #fff; background: transparent; font-size: .85rem; font-weight: 700; cursor: pointer;
        }

        main { width: min(100%, 30rem); margin: 0 auto; padding: 16px; display: grid; gap: 14px; }

        .count {
            display: flex; align-items: baseline; justify-content: space-between; gap: 12px;
            padding: 14px 16px; border: 1px solid var(--line); border-radius: 12px; background: #fff;
        }
        .count span { color: var(--muted); font-weight: 700; }
        .count strong { font-size: 1.6rem; font-weight: 900; color: var(--violet); }
        .count em { font-style: normal; color: var(--muted); font-size: .95rem; font-weight: 700; }

        .camera {
            position: relative; aspect-ratio: 1; overflow: hidden;
            border-radius: 14px; background: #111;
        }
        .camera video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .camera .frame {
            position: absolute; inset: 14%; border-radius: 12px; pointer-events: none;
            box-shadow: 0 0 0 999px rgba(0,0,0,.35);
            outline: 3px solid rgba(255,255,255,.9); outline-offset: -3px;
        }
        .camera .off {
            position: absolute; inset: 0; display: none; place-content: center; gap: 12px;
            padding: 24px; text-align: center; color: #fff; background: #1c1726;
        }
        .camera.is-off .off { display: grid; }
        .camera.is-off .frame { display: none; }
        .camera .off p { margin: 0; line-height: 1.7; font-weight: 600; }

        .hint { margin: 0; text-align: center; color: var(--muted); font-weight: 700; }

        .manual { padding: 14px 16px; border: 1px solid var(--line); border-radius: 12px; background: #fff; }
        .manual label { display: block; margin-bottom: 8px; font-weight: 800; }
        .manual div { display: flex; gap: 8px; }
        .manual input {
            flex: 1; min-width: 0; min-height: 48px; padding: 8px 12px;
            border: 1px solid #cfc8de; border-radius: 10px; background: #fff; font-size: 1.05rem; direction: ltr; text-align: left;
        }
        .manual input:focus { outline: 2px solid var(--violet); outline-offset: 1px; }

        .btn {
            min-height: 48px; padding: 8px 18px; border: 0; border-radius: 10px;
            color: #fff; background: var(--violet); font-weight: 800; cursor: pointer;
        }
        .btn:disabled { opacity: .6; }

        .result {
            position: fixed; inset: 0; z-index: 10; display: none;
            flex-direction: column; align-items: center; justify-content: center; gap: 10px;
            padding: 24px 20px; text-align: center; color: #fff;
        }
        .result.is-open { display: flex; }
        .result[data-status="checked_in"] { background: var(--ok); }
        .result[data-status="already"] { background: var(--warn); }
        .result[data-status="invalid"], .result[data-status="error"] { background: var(--bad); }
        .result svg { width: 88px; height: 88px; }
        .result h2 { margin: 0; font-size: 1.7rem; font-weight: 900; line-height: 1.35; }
        .result .name { margin: 6px 0 0; font-size: 1.45rem; font-weight: 800; }
        .result .meta { margin: 0; font-size: 1.05rem; font-weight: 700; opacity: .92; }
        .result .vip {
            display: inline-block; padding: 4px 14px; border-radius: 999px;
            color: #3b2300; background: #fcd34d; font-weight: 900;
        }
        .result .btn { margin-top: 18px; min-width: 12rem; color: var(--ink); background: #fff; font-size: 1.05rem; }
        .result [hidden] { display: none; }
    </style>
    @vite('resources/js/osif-checkin.js')
</head>
<body>
    <header class="bar">
        <div>
            <h1>تسجيل الحضور — OSIF</h1>
            <small>{{ auth()->user()->name }}</small>
        </div>
        <form method="POST" action="{{ route('filament.osif.auth.logout') }}">
            @csrf
            <button type="submit">تسجيل الخروج</button>
        </form>
    </header>

    <main data-checkin data-url="{{ route('osif.checkin.store') }}">
        <div class="count" aria-live="polite">
            <span>الحضور</span>
            <div><strong data-checked-in>{{ $stats['checkedIn'] }}</strong> <em>من أصل <span data-total>{{ $stats['total'] }}</span> مسجّل</em></div>
        </div>

        <div class="camera is-off" data-camera>
            <video playsinline muted autoplay></video>
            <div class="frame"></div>
            <div class="off">
                <p data-camera-message>جارٍ تشغيل الكاميرا…</p>
                <div><button type="button" class="btn" data-camera-start hidden>تشغيل الكاميرا</button></div>
            </div>
        </div>

        <p class="hint">وجّه الكاميرا نحو رمز QR في بطاقة الدعوة</p>

        <form class="manual" data-manual>
            <label for="manual-code">أو اكتب رمز الدعوة المطبوع على البطاقة</label>
            <div>
                <input id="manual-code" name="code" inputmode="text" autocomplete="off" placeholder="OSIF-0012" required>
                <button type="submit" class="btn">تسجيل</button>
            </div>
        </form>
    </main>

    <section class="result" data-result role="alertdialog" aria-modal="true" aria-labelledby="result-title">
        <svg data-icon="ok" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m7.5 12.5 3 3 6-6.5"/></svg>
        <svg data-icon="warn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 7v6M12 16.5v.5"/></svg>
        <svg data-icon="bad" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m8.5 8.5 7 7M15.5 8.5l-7 7"/></svg>
        <h2 id="result-title" data-result-title></h2>
        <span class="vip" data-result-vip hidden>دعوة VIP</span>
        <p class="name" data-result-name></p>
        <p class="meta" data-result-meta></p>
        <button type="button" class="btn" data-result-close>مسح التالي</button>
    </section>
</body>


</html>

