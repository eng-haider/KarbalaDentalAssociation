{{-- A printable A4 sign for a company's booth: doctors scan it to open the company's page and order. --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>ملصق QR — {{ $company->name }}</title>
    <link rel="stylesheet" href="{{ asset('fonts/cairo/cairo.css') }}">
    <style>
        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #140a2c; background: #ece8f1; font-family: Cairo, Tahoma, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .bar { position: sticky; top: 0; z-index: 1; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: .75rem 1rem; padding: .75rem 1rem; background: #fff; border-bottom: 1px solid #e4dcec; }
        .bar p { margin: 0; color: #6c5b78; font-size: .9rem; font-weight: 600; }
        .bar button { display: inline-flex; align-items: center; gap: .5rem; min-height: 44px; padding: .5rem 1.4rem; border: 0; border-radius: 10px; color: #fff; background: #5f168f; font: inherit; font-size: 1rem; font-weight: 800; cursor: pointer; }
        .bar button:hover { background: #7c2bb8; }

        .sheet { position: relative; display: flex; flex-direction: column; align-items: center; width: 210mm; height: 297mm; margin: 24px auto; padding: 22mm 18mm 14mm; overflow: hidden; background: #fff; box-shadow: 0 20px 50px rgba(40, 10, 60, .18); text-align: center; }
        .sheet::before { content: ''; position: absolute; inset: 0 0 auto; height: 9mm; background: linear-gradient(118deg, #16021f, #5f168f 60%, #a23ae1); }
        .sheet::after { content: ''; position: absolute; inset: auto 0 0; height: 3mm; background: #f7a531; }

        .event { display: flex; align-items: center; gap: 3mm; color: #5f168f; font-size: 11pt; font-weight: 800; letter-spacing: .02em; }
        .event img { width: 12mm; height: 12mm; padding: 1.6mm; border-radius: 50%; background: #5f168f; object-fit: contain; }

        .logo { display: grid; place-items: center; width: 34mm; height: 34mm; margin-top: 9mm; overflow: hidden; border: 1px solid #e4dcec; border-radius: 7mm; background: #fff; }
        .logo img { width: 100%; height: 100%; padding: 3mm; object-fit: contain; }
        .logo span { color: #5f168f; font-size: 26pt; font-weight: 900; }
        h1 { margin: 5mm 0 0; font-size: 30pt; font-weight: 900; line-height: 1.25; }
        .booth { display: inline-block; margin-top: 3mm; padding: 1mm 5mm; border-radius: 99px; color: #5a3100; background: #fff1d6; font-size: 13pt; font-weight: 800; }

        .qr { width: 112mm; height: 112mm; margin-top: 9mm; padding: 5mm; border: 1.2mm solid #5f168f; border-radius: 8mm; }
        .qr svg { display: block; width: 100%; height: 100%; }

        .cta { margin: 8mm 0 0; font-size: 22pt; font-weight: 900; line-height: 1.35; }
        .sub { margin: 2mm 0 0; color: #4b3a58; font-size: 14pt; font-weight: 600; }

        .steps { display: flex; justify-content: center; gap: 4mm; margin: auto 0 0; padding: 0; list-style: none; }
        .steps li { display: flex; align-items: center; gap: 2mm; padding: 2mm 4mm; border: 1px solid #e4dcec; border-radius: 99px; font-size: 11pt; font-weight: 700; }
        .steps b { display: grid; place-items: center; width: 7mm; height: 7mm; border-radius: 50%; color: #5a3100; background: #f7a531; font-size: 10pt; }
        .url { margin: 4mm 0 0; color: #8a7a96; font-size: 9pt; direction: ltr; }

        /* Phones and narrow windows: shrink the sheet to fit; it still prints at full A4. */
        @media screen and (max-width: 820px) { .sheet { zoom: .85; } }
        @media screen and (max-width: 700px) { .sheet { zoom: .72; } }
        @media screen and (max-width: 600px) { .sheet { zoom: .6; margin: 12px auto; } }
        @media screen and (max-width: 500px) { .sheet { zoom: .5; } }
        @media screen and (max-width: 420px) { .sheet { zoom: .45; } }
        @media screen and (max-width: 370px) { .sheet { zoom: .42; } }

        @media print {
            body { background: #fff; }
            .bar { display: none; }
            .sheet { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="bar">
        <p>ملصق جاهز للطباعة على ورق A4</p>
        <button type="button" onclick="window.print()">🖨 طباعة الملصق</button>
    </div>

    <main class="sheet">
        <div class="event">
            <img src="{{ asset('images/osif/logos/osif.png') }}" alt="">
            <span>OSIF KARBALA 2026 · المعرض المتخصص</span>
        </div>

        <div class="logo">
            @if ($company->logoUrl())
                <img src="{{ $company->logoUrl() }}" alt="">
            @else
                <span>{{ $company->initials() }}</span>
            @endif
        </div>
        <h1>{{ $company->name }}</h1>
        @if ($company->booth)
            <span class="booth">جناح {{ $company->booth }}</span>
        @endif

        <div class="qr" role="img" aria-label="رمز QR لصفحة {{ $company->name }}">{!! $qr !!}</div>

        <p class="cta">امسح الرمز بكاميرا هاتفك</p>
        <p class="sub">لتصفّح منتجاتنا وعروضنا، وأرسل طلبك إلينا عبر واتساب</p>

        <ol class="steps">
            <li><b>1</b> امسح الرمز</li>
            <li><b>2</b> أضف المنتجات إلى السلة</li>
            <li><b>3</b> أرسل الطلب عبر واتساب</li>
        </ol>
        <p class="url">{{ $url }}</p>
    </main>
</body>
</html>
