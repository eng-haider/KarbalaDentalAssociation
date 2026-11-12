<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#2e1065">
    <meta name="robots" content="noindex, nofollow">
    <title>الضيوف الذين قرأت بطاقاتهم — OSIF KARBALA</title>
    <link rel="stylesheet" href="{{ asset('fonts/cairo/cairo.css') }}">
    <style>
        :root {
            --violet: #2e1065;
            --ink: #1f1a2e;
            --muted: #6b6480;
            --line: #e4e0ee;
            --page: #f5f3fa;
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

        .bar {
            position: sticky; top: 0; z-index: 1;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 16px; padding-top: max(12px, env(safe-area-inset-top));
            color: #fff; background: var(--violet);
        }
        .bar h1 { margin: 0; font-size: 1.05rem; font-weight: 800; line-height: 1.3; }
        .bar small { display: block; font-size: .78rem; font-weight: 600; opacity: .75; }
        .hall-name {
            display: inline-block; margin-top: 2px; padding: 1px 10px; border-radius: 999px;
            color: var(--violet); background: #fff; font-size: .82rem; font-weight: 800;
        }
        .back {
            display: inline-flex; align-items: center; min-height: 44px; padding: 6px 14px;
            border-radius: 10px; color: var(--violet); background: #fff;
            font-size: .95rem; font-weight: 800; text-decoration: none; white-space: nowrap;
        }

        main { width: min(100%, 30rem); margin: 0 auto; padding: 16px; display: grid; gap: 14px; }

        .total {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 14px 16px; border: 1px solid var(--line); border-radius: 12px; background: #fff;
        }
        .total span { font-weight: 800; }
        .total strong { font-size: 2.2rem; font-weight: 900; line-height: 1.1; color: var(--violet); }

        .list { padding: 4px 16px; border: 1px solid var(--line); border-radius: 12px; background: #fff; }
        .list p { margin: 0; padding: 12px 0; color: var(--muted); font-weight: 700; }
        .list ol { margin: 0; padding: 0; list-style: none; }
        .list li { display: grid; gap: 2px; padding: 10px 0; border-top: 1px solid var(--line); }
        .list li:first-child { border-top: 0; }
        .list li b { font-weight: 800; overflow-wrap: anywhere; }
        .list li span { color: var(--muted); font-size: .85rem; font-weight: 700; }
        .list li em {
            margin-inline-end: 6px; padding: 0 8px; border-radius: 999px;
            font-style: normal; color: #3b2300; background: #fcd34d; font-weight: 900;
        }
    </style>
</head>
<body>
    <header class="bar">
        <div>
            <h1>الضيوف الذين قرأت بطاقاتهم</h1>
            <small>{{ auth()->user()->name }}</small>
            <span class="hall-name">{{ $halls[$hall] }}</span>
        </div>
        <a class="back" href="{{ route('osif.checkin', $canChangeHall ? ['hall' => $hall] : []) }}">رجوع إلى المسح</a>
    </header>

    <main>
        <div class="total">
            <span>البطاقات التي قرأتها</span>
            <strong>{{ count($scanned) }}</strong>
        </div>

        <section class="list">
            @if ($scanned)
                {{-- Newest first. --}}
                <ol>
                    @foreach ($scanned as $guest)
                        <li>
                            <b>{{ $guest['name'] }}</b>
                            <span>
                                {{-- Each part kept whole: «12:53 صباحاً» and «OSIF-0007» go opposite ways. --}}
                                @if ($guest['vip'])<em>VIP</em>@endif
                                <bdi>{{ $guest['time'] }}</bdi> · <bdi>{{ $guest['code'] }}</bdi>
                            </span>
                        </li>
                    @endforeach
                </ol>
            @else
                <p>لم تقرأ أي بطاقة بعد.</p>
            @endif
        </section>
    </main>
</body>
</html>
