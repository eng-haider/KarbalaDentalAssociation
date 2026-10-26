// Draws an attendee's OSIF invitation (a badge-style card with a QR code, the
// same card as a PDF, and a wide preview for WhatsApp's link preview) in the
// browser and uploads them, so the invitation page and WhatsApp can use them.
// VIP guests get a black-and-gold version of the card. Canvas text is
// shaped by the browser, so Arabic renders correctly without a headless Chrome
// on the server. Used by the "إرسال الدعوة" modal
// (resources/views/filament/osif/invitation-modal.blade.php) and «إرسال للجميع».

import qrcode from 'qrcode-generator';

const COLOR = {
    night: '#0d0b24',
    plum: '#1e0937',
    purple: '#5f168f',
    amber: '#f7a531',
    amberInk: '#2a1104',
    white: '#ffffff',
    soft: 'rgba(255, 255, 255, 0.74)',
    faint: 'rgba(255, 255, 255, 0.16)',
    ink: '#140a2c',
    muted: '#6b5b80',
};

/** The VIP card's black and gold. */
const GOLD = {
    base: '#d4a64a',
    deep: '#8f6a1f',
    ink: '#241a06',
    black: '#0b0a0c',
    coal: '#1a160f',
    ivory: '#fdf8ec',
    name: '#1b1408',
    muted: '#86744f',
    soft: 'rgba(249, 231, 167, 0.8)',
    faint: 'rgba(212, 166, 74, 0.3)',
    rule: 'rgba(184, 134, 43, 0.45)',
};

const ORGANISERS = [
    { x: 800, logo: 'osif', label: 'OSIF KARBALA', brand: true },
    { x: 540, logo: 'syndicate', label: 'نقابة أطباء أسنان كربلاء' },
    { x: 280, logo: 'university', label: 'جامعة السبطين للعلوم الطبية' },
];

const label = (data) => (data.vip ? 'دعوة VIP' : 'دعوة حضور');

let fontsReady;
const images = new Map();

/** Loads the self-hosted Cairo stylesheet (already present in the OSIF panel). */
function ensureFonts(href) {
    fontsReady ??= new Promise((resolve) => {
        const load = () => Promise.all(
            ['400', '600', '700', '800'].map((weight) => document.fonts.load(`${weight} 32px Cairo`, 'دعوة OSIF 2026')),
        ).catch(() => {}).then(resolve);

        const path = new URL(href, location.href).pathname;
        const loaded = [...document.querySelectorAll('link[rel="stylesheet"]')]
            .some((link) => new URL(link.href, location.href).pathname === path);

        if (loaded) {
            load();
            return;
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.onload = load;
        link.onerror = resolve;
        document.head.append(link);

        // Never hold the card back; the fallback font still renders Arabic.
        setTimeout(resolve, 5000);
    });

    return fontsReady;
}

function loadImage(src) {
    if (! images.has(src)) {
        images.set(src, new Promise((resolve) => {
            const image = new Image();
            image.onload = () => resolve(image);
            image.onerror = () => resolve(null);
            image.src = src;
        }));
    }

    return images.get(src);
}

const font = (weight, size) => `${weight} ${size}px Cairo, Tahoma, sans-serif`;

function roundRect(ctx, x, y, width, height, radius) {
    const [tl, tr, br, bl] = Array.isArray(radius) ? radius : [radius, radius, radius, radius];
    ctx.beginPath();
    ctx.moveTo(x + tl, y);
    ctx.arcTo(x + width, y, x + width, y + height, tr);
    ctx.arcTo(x + width, y + height, x, y + height, br);
    ctx.arcTo(x, y + height, x, y, bl);
    ctx.arcTo(x, y, x + width, y, tl);
    ctx.closePath();
}

/** Largest size (down to min) at which the text fits on one line. */
function fit(ctx, text, weight, size, maxWidth, min) {
    let current = size;
    ctx.font = font(weight, current);

    while (current > min && ctx.measureText(text).width > maxWidth) {
        current -= 2;
        ctx.font = font(weight, current);
    }

    return current;
}

function wrap(ctx, text, maxWidth) {
    const lines = [];
    let line = '';

    for (const word of text.split(/\s+/)) {
        const candidate = line ? `${line} ${word}` : word;

        if (line && ctx.measureText(candidate).width > maxWidth) {
            lines.push(line);
            line = word;
        } else {
            line = candidate;
        }
    }

    if (line) {
        lines.push(line);
    }

    return lines;
}

function text(ctx, value, x, y, { weight = 400, size = 28, color = COLOR.white, align = 'center' } = {}) {
    ctx.font = font(weight, size);
    ctx.fillStyle = color;
    ctx.textAlign = align;
    ctx.fillText(value, x, y);
}

/**
 * An amber, AEEDC-style pill label; x is its right edge, or its centre when
 * centred. A fill function receives the pill's box, for gradients.
 */
function pill(ctx, value, x, y, { size = 28, centered = false, fill = COLOR.amber, ink = COLOR.amberInk } = {}) {
    ctx.font = font(700, size);
    const width = ctx.measureText(value).width + size * 2.2;
    const height = size * 1.9;
    const left = centered ? x - width / 2 : x - width;

    roundRect(ctx, left, y, width, height, height / 2);
    ctx.fillStyle = typeof fill === 'function' ? fill(left, y, left + width, y + height) : fill;
    ctx.fill();
    text(ctx, value, left + width / 2, y + height / 2 + size * 0.36, { weight: 700, size, color: ink });
}

/** Polished gold along the line from (x0, y0) to (x1, y1). */
function gold(ctx, x0, y0, x1, y1) {
    const gradient = ctx.createLinearGradient(x0, y0, x1, y1);
    gradient.addColorStop(0, '#a87a26');
    gradient.addColorStop(0.3, '#f6df8e');
    gradient.addColorStop(0.5, '#c99a3e');
    gradient.addColorStop(0.72, '#fbedb4');
    gradient.addColorStop(1, '#a87a26');

    return gradient;
}

function diamond(ctx, cx, cy, radius, fill) {
    ctx.beginPath();
    ctx.moveTo(cx, cy - radius);
    ctx.lineTo(cx + radius, cy);
    ctx.lineTo(cx, cy + radius);
    ctx.lineTo(cx - radius, cy);
    ctx.closePath();
    ctx.fillStyle = fill;
    ctx.fill();
}

/** Deep night background with a purple glow and the OSIF orbit rings. */
function backdrop(ctx, width, height, glowX, glowY) {
    const base = ctx.createLinearGradient(0, 0, width, height);
    base.addColorStop(0, COLOR.plum);
    base.addColorStop(0.55, '#130a2d');
    base.addColorStop(1, COLOR.night);
    ctx.fillStyle = base;
    ctx.fillRect(0, 0, width, height);

    const glow = ctx.createRadialGradient(glowX, glowY, 0, glowX, glowY, Math.max(width, height) * 0.6);
    glow.addColorStop(0, 'rgba(143, 50, 209, 0.45)');
    glow.addColorStop(1, 'rgba(143, 50, 209, 0)');
    ctx.fillStyle = glow;
    ctx.fillRect(0, 0, width, height);

    ctx.save();
    ctx.translate(glowX, glowY + 140);
    ctx.rotate(-0.42);
    for (const [rx, ry, alpha] of [[560, 150, 0.16], [440, 112, 0.1]]) {
        ctx.beginPath();
        ctx.ellipse(0, 0, rx, ry, 0, 0, Math.PI * 2);
        ctx.strokeStyle = `rgba(230, 175, 255, ${alpha})`;
        ctx.lineWidth = 2;
        ctx.stroke();
    }
    ctx.restore();
}

/** Black with faint gold rays and a gold glow: the VIP card's background. */
function vipBackdrop(ctx, width, height, glowX, glowY) {
    const base = ctx.createLinearGradient(0, 0, 0, height);
    base.addColorStop(0, GOLD.coal);
    base.addColorStop(1, GOLD.black);
    ctx.fillStyle = base;
    ctx.fillRect(0, 0, width, height);

    ctx.save();
    ctx.translate(glowX, glowY);
    ctx.fillStyle = 'rgba(232, 196, 110, 0.035)';
    const rays = 40;
    const reach = Math.hypot(width, height);
    for (let ray = 0; ray < rays; ray += 2) {
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.arc(0, 0, reach, (ray / rays) * Math.PI * 2, ((ray + 1) / rays) * Math.PI * 2);
        ctx.closePath();
        ctx.fill();
    }
    ctx.restore();

    const glow = ctx.createRadialGradient(glowX, glowY, 0, glowX, glowY, Math.max(width, height) * 0.62);
    glow.addColorStop(0, 'rgba(212, 166, 74, 0.3)');
    glow.addColorStop(1, 'rgba(212, 166, 74, 0)');
    ctx.fillStyle = glow;
    ctx.fillRect(0, 0, width, height);
}

/** A gold double rule around the edge with diamonds at the corners. */
function frame(ctx, width, height, inset) {
    ctx.save();
    ctx.lineWidth = 3;
    ctx.strokeStyle = gold(ctx, 0, 0, width, height);
    ctx.strokeRect(inset, inset, width - inset * 2, height - inset * 2);

    const inner = inset + 12;
    ctx.lineWidth = 1;
    ctx.strokeStyle = GOLD.faint;
    ctx.strokeRect(inner, inner, width - inner * 2, height - inner * 2);
    ctx.restore();

    for (const [x, y] of [[inner, inner], [width - inner, inner], [inner, height - inner], [width - inner, height - inner]]) {
        diamond(ctx, x, y, 9, gold(ctx, x - 9, y - 9, x + 9, y + 9));
    }
}

function seal(ctx, image, cx, cy, radius, { brand = false, ring = null } = {}) {
    ctx.beginPath();
    ctx.arc(cx, cy, radius, 0, Math.PI * 2);
    ctx.fillStyle = brand ? COLOR.purple : COLOR.white;
    ctx.fill();
    ctx.lineWidth = 3;
    ctx.strokeStyle = ring ?? (brand ? COLOR.amber : 'rgba(255, 255, 255, 0.35)');
    ctx.stroke();

    if (image) {
        const size = radius * (brand ? 1.42 : 1.66);
        ctx.drawImage(image, cx - size / 2, cy - size / 2, size, size);
    }
}

/** Crisp QR modules on a white square (with its own quiet zone). */
function drawQr(ctx, value, x, y, size) {
    const qr = qrcode(0, 'M');
    qr.addData(value);
    qr.make();

    const count = qr.getModuleCount();
    const quiet = 2;
    const cell = Math.floor(size / (count + quiet * 2));
    const offset = Math.round((size - cell * (count + quiet * 2)) / 2) + quiet * cell;

    ctx.fillStyle = COLOR.white;
    ctx.fillRect(x, y, size, size);
    ctx.fillStyle = COLOR.ink;

    for (let row = 0; row < count; row++) {
        for (let col = 0; col < count; col++) {
            if (qr.isDark(row, col)) {
                ctx.fillRect(x + offset + col * cell, y + offset + row * cell, cell, cell);
            }
        }
    }
}

/** The three organisers, equal weight, across the top of the card. */
function organisers(ctx, logos, { ring = null, color = COLOR.soft } = {}) {
    for (const { x, logo, label: name, brand } of ORGANISERS) {
        seal(ctx, logos[logo], x, 132, 58, { brand, ring });
        const size = fit(ctx, name, 600, 21, 236, 16);
        text(ctx, name, x, 228, { weight: 600, size, color });
    }
}

/** The guest's name (up to two lines) with a short accent rule beneath; returns the y below the rule. */
function guest(ctx, name, right, y, column, { color, accent }) {
    const nameSize = fit(ctx, name, 800, 62, column, 40);
    ctx.font = font(800, nameSize);
    const nameLines = ctx.measureText(name).width > column ? wrap(ctx, name, column).slice(0, 2) : [name];
    for (const nameLine of nameLines) {
        text(ctx, nameLine, right, y, { weight: 800, size: nameSize, color, align: 'right' });
        y += nameSize + 12;
    }

    ctx.fillStyle = typeof accent === 'function' ? accent(right - 96, right) : accent;
    roundRect(ctx, right - 96, y - 26, 96, 7, 3.5);
    ctx.fill();

    return y;
}

/** When, where and lunch, as label/value rows starting at y. */
function details(ctx, data, y, step, { labelColor, valueColor }) {
    const rows = [
        { label: 'الموعد', value: [data.date, data.time].filter(Boolean).join(' – ') },
        { label: 'المكان', value: data.location },
        {
            label: 'الضيافة',
            value: 'تشمل الدعوة وجبة غداء — يرجى إبراز رمز QR لاستلام وجبة الغداء',
        },
    ].filter(({ value }) => value);

    for (const row of rows) {
        text(ctx, row.label, 990, y, { weight: 700, size: 24, color: labelColor, align: 'right' });
        const size = fit(ctx, row.value, 700, 30, 780, row.label === 'الضيافة' ? 18 : 22);
        text(ctx, row.value, 880, y, { weight: 700, size, color: valueColor, align: 'right' });
        y += step;
    }
}

function canvas(width, height, scale = 1) {
    const element = document.createElement('canvas');
    element.width = width * scale;
    element.height = height * scale;

    const ctx = element.getContext('2d');
    ctx.scale(scale, scale);
    ctx.direction = 'rtl';
    ctx.textBaseline = 'alphabetic';

    return [element, ctx];
}

/** The invitation itself: 1080×1350 (×scale for print), shown in WhatsApp and on the invitation page. */
function drawCard(data, logos, scale = 1) {
    return data.vip ? drawVipCard(data, logos, scale) : drawStandardCard(data, logos, scale);
}

function drawStandardCard(data, logos, scale = 1) {
    const WIDTH = 1080;
    const HEIGHT = 1350;
    const [element, ctx] = canvas(WIDTH, HEIGHT, scale);

    backdrop(ctx, WIDTH, HEIGHT, 860, 120);
    organisers(ctx, logos);

    // Event.
    pill(ctx, 'دعوة حضور', WIDTH / 2, 272, { centered: true });
    const titleSize = fit(ctx, data.event, 800, 52, 920, 38);
    text(ctx, data.event, WIDTH / 2, 410, { weight: 800, size: titleSize });
    text(ctx, data.brand, WIDTH / 2, 458, { weight: 700, size: 28, color: COLOR.amber });

    // The badge.
    const badge = { x: 90, y: 506, width: 900, height: 500 };
    const right = badge.x + badge.width - 56;

    ctx.save();
    ctx.shadowColor = 'rgba(0, 0, 0, 0.35)';
    // Shadows are measured in device pixels, so they follow the scale by hand.
    ctx.shadowBlur = 40 * scale;
    ctx.shadowOffsetY = 18 * scale;
    roundRect(ctx, badge.x, badge.y, badge.width, badge.height, 32);
    ctx.fillStyle = COLOR.white;
    ctx.fill();
    ctx.restore();

    const band = 100;
    roundRect(ctx, badge.x, badge.y + badge.height - band, badge.width, band, [0, 0, 32, 32]);
    ctx.fillStyle = COLOR.amber;
    ctx.fill();
    text(ctx, 'دعوة شخصية', right, badge.y + badge.height - 38, { weight: 800, size: 30, color: COLOR.amberInk, align: 'right' });
    text(ctx, data.code, badge.x + 56, badge.y + badge.height - 38, { weight: 800, size: 30, color: COLOR.amberInk, align: 'left' });

    // QR on the left, the guest on the right.
    const qr = { x: badge.x + 48, y: badge.y + 48, size: 252 };
    drawQr(ctx, data.url, qr.x, qr.y, qr.size);
    text(ctx, 'امسح الرمز لعرض الدعوة', qr.x + qr.size / 2, qr.y + qr.size + 40, { weight: 600, size: 20, color: COLOR.muted });

    const column = right - (qr.x + qr.size + 48);
    let y = badge.y + 104;
    text(ctx, 'يسرّنا دعوتكم لحضور الملتقى', right, y, { size: 26, color: COLOR.muted, align: 'right' });

    y = guest(ctx, data.name, right, y + 82, column, { color: COLOR.ink, accent: COLOR.amber });

    if (data.title) {
        const titleFit = fit(ctx, data.title, 700, 30, column, 22);
        text(ctx, data.title, right, y + 32, { weight: 700, size: titleFit, color: COLOR.purple, align: 'right' });
    }

    // When and where.
    details(ctx, data, 1104, 64, { labelColor: COLOR.amber, valueColor: COLOR.white });

    ctx.fillStyle = COLOR.faint;
    ctx.fillRect(150, 1264, WIDTH - 300, 2);
    text(ctx, 'يرجى إبراز الدعوة للدخول من البوابة الرئيسية', WIDTH / 2, 1318, { weight: 600, size: 22, color: COLOR.soft });

    return element;
}

/** «VIP» in gold foil between two ruled lines, centred on cx with its baseline at y. */
function vipEmblem(ctx, cx, y) {
    const size = 92;
    const spacing = 24;
    const letters = ['V', 'I', 'P'];

    ctx.font = font(800, size);
    const widths = letters.map((letter) => ctx.measureText(letter).width);
    const total = widths.reduce((sum, width) => sum + width, 0) + spacing * (letters.length - 1);

    const foil = ctx.createLinearGradient(0, y - size * 0.75, 0, y);
    foil.addColorStop(0, '#fbeeb6');
    foil.addColorStop(0.45, '#d9ad52');
    foil.addColorStop(0.55, '#b8862b');
    foil.addColorStop(1, '#f3d98a');

    let x = cx - total / 2;
    letters.forEach((letter, index) => {
        text(ctx, letter, x + widths[index] / 2, y, { weight: 800, size, color: foil });
        x += widths[index] + spacing;
    });

    const middle = y - size * 0.36;
    for (const side of [-1, 1]) {
        const near = cx + side * (total / 2 + 36);
        const far = cx + side * (total / 2 + 210);
        ctx.fillStyle = gold(ctx, Math.min(near, far), 0, Math.max(near, far), 0);
        ctx.fillRect(Math.min(near, far), middle - 1, Math.abs(far - near), 2);
        diamond(ctx, far, middle, 8, GOLD.base);
        diamond(ctx, near, middle, 4, GOLD.base);
    }
}

/** The VIP invitation: the same content in black and gold, under a VIP emblem. */
function drawVipCard(data, logos, scale = 1) {
    const WIDTH = 1080;
    const HEIGHT = 1350;
    const [element, ctx] = canvas(WIDTH, HEIGHT, scale);

    vipBackdrop(ctx, WIDTH, HEIGHT, WIDTH / 2, 0);
    frame(ctx, WIDTH, HEIGHT, 28);
    organisers(ctx, logos, { ring: GOLD.base, color: GOLD.soft });

    // Emblem and event.
    vipEmblem(ctx, WIDTH / 2, 356);
    text(ctx, 'دعوة خاصة', WIDTH / 2, 404, { weight: 700, size: 28, color: GOLD.ivory });
    const titleSize = fit(ctx, data.event, 800, 46, 900, 34);
    text(ctx, data.event, WIDTH / 2, 470, { weight: 800, size: titleSize });
    text(ctx, data.brand, WIDTH / 2, 512, { weight: 700, size: 26, color: GOLD.base });

    // The badge: ivory with a fine gold rule and a gold band.
    const badge = { x: 90, y: 548, width: 900, height: 466 };
    const band = 96;
    const right = badge.x + badge.width - 56;

    ctx.save();
    ctx.shadowColor = 'rgba(0, 0, 0, 0.5)';
    ctx.shadowBlur = 40 * scale;
    ctx.shadowOffsetY = 18 * scale;
    roundRect(ctx, badge.x, badge.y, badge.width, badge.height, 32);
    ctx.fillStyle = GOLD.ivory;
    ctx.fill();
    ctx.restore();

    roundRect(ctx, badge.x + 14, badge.y + 14, badge.width - 28, badge.height - band - 28, 22);
    ctx.lineWidth = 1.5;
    ctx.strokeStyle = GOLD.rule;
    ctx.stroke();

    const bandY = badge.y + badge.height - band;
    roundRect(ctx, badge.x, bandY, badge.width, band, [0, 0, 32, 32]);
    ctx.fillStyle = gold(ctx, badge.x, bandY, badge.x + badge.width, bandY + band);
    ctx.fill();
    text(ctx, 'دعوة VIP', right, bandY + 60, { weight: 800, size: 30, color: GOLD.ink, align: 'right' });
    text(ctx, data.code, badge.x + 56, bandY + 60, { weight: 800, size: 30, color: GOLD.ink, align: 'left' });

    // QR on the left, the guest on the right.
    const qr = { x: badge.x + 48, y: badge.y + 44, size: 252 };
    drawQr(ctx, data.url, qr.x, qr.y, qr.size);
    ctx.lineWidth = 2;
    ctx.strokeStyle = GOLD.rule;
    ctx.strokeRect(qr.x - 1, qr.y - 1, qr.size + 2, qr.size + 2);
    text(ctx, 'امسح الرمز لعرض الدعوة', qr.x + qr.size / 2, qr.y + qr.size + 38, { weight: 600, size: 20, color: GOLD.muted });

    // VIP guests are addressed by name alone, without their professional title.
    const column = right - (qr.x + qr.size + 48);
    const y = badge.y + 124;
    text(ctx, 'يتشرّف منظمو الملتقى بدعوتكم', right, y, { size: 26, color: GOLD.muted, align: 'right' });

    guest(ctx, data.name, right, y + 82, column, {
        color: GOLD.name,
        accent: (left, end) => gold(ctx, left, 0, end, 0),
    });

    details(ctx, data, 1094, 58, { labelColor: GOLD.base, valueColor: GOLD.ivory });

    ctx.fillStyle = GOLD.faint;
    ctx.fillRect(150, 1232, WIDTH - 300, 2);
    text(ctx, 'يرجى إبراز الدعوة للدخول من البوابة الرئيسية', WIDTH / 2, 1280, { weight: 600, size: 22, color: GOLD.soft });

    return element;
}

/** 1200×630 — the size WhatsApp shows as a large link preview. */
function drawPreview(data, logos) {
    const WIDTH = 1200;
    const HEIGHT = 630;
    const [element, ctx] = canvas(WIDTH, HEIGHT);

    const right = WIDTH - 80;
    const column = 640;
    const theme = data.vip
        ? {
            ring: GOLD.base,
            accent: GOLD.base,
            soft: GOLD.soft,
            pill: { fill: (x0, y0, x1, y1) => gold(ctx, x0, y0, x1, y1), ink: GOLD.ink },
        }
        : { ring: null, accent: COLOR.amber, soft: COLOR.soft, pill: {} };

    if (data.vip) {
        vipBackdrop(ctx, WIDTH, HEIGHT, 260, 200);
        frame(ctx, WIDTH, HEIGHT, 18);
    } else {
        backdrop(ctx, WIDTH, HEIGHT, 260, 200);
    }

    seal(ctx, logos.osif, 250, 268, 150, { brand: true, ring: theme.ring });
    seal(ctx, logos.syndicate, 190, 516, 46, { ring: theme.ring });
    seal(ctx, logos.university, 310, 516, 46, { ring: theme.ring });

    pill(ctx, label(data), right, 82, { size: 26, ...theme.pill });

    const nameSize = fit(ctx, data.name, 800, 70, column, 44);
    text(ctx, data.name, right, 250, { weight: 800, size: nameSize, align: 'right' });

    let y = 250;
    if (data.title && ! data.vip) {
        y += 56;
        text(ctx, data.title, right, y, { weight: 600, size: fit(ctx, data.title, 600, 32, column, 22), color: theme.soft, align: 'right' });
    }

    ctx.fillStyle = theme.accent;
    roundRect(ctx, right - 96, y + 28, 96, 7, 3.5);
    ctx.fill();

    y += 100;
    text(ctx, data.event, right, y, { weight: 700, size: fit(ctx, data.event, 700, 34, column, 24), align: 'right' });

    const when = [data.date, data.time].filter(Boolean).join(' – ');
    if (when) {
        y += 56;
        text(ctx, when, right, y, { weight: 700, size: fit(ctx, when, 700, 30, column, 22), color: theme.accent, align: 'right' });
    }

    if (data.location) {
        y += 50;
        text(ctx, data.location, right, y, { size: fit(ctx, data.location, 400, 26, column, 20), color: theme.soft, align: 'right' });
    }

    // A clear benefit note that remains visible in WhatsApp's wide preview.
    pill(ctx, 'تشمل الدعوة وجبة غداء', right, 548, { size: 20, ...theme.pill });

    return element;
}

/** UTF-16BE hex string, the PDF way to store Arabic metadata. */
const pdfText = (value) => `<FEFF${[...Array(value.length)].map((_, i) => value.charCodeAt(i).toString(16).padStart(4, '0')).join('')}>`;

/**
 * A one-page PDF holding the card as a JPEG. Small, prints at 6×7.5 inches,
 * and opens in WhatsApp's document viewer — no PDF library needed.
 */
async function pdfFrom(jpeg, pixelWidth, pixelHeight, title) {
    const image = new Uint8Array(await jpeg.arrayBuffer());
    const encoder = new TextEncoder();
    const chunks = [];
    const offsets = [];
    let length = 0;

    const push = (part) => {
        const bytes = typeof part === 'string' ? encoder.encode(part) : part;
        chunks.push(bytes);
        length += bytes.length;
    };
    const object = (body, stream) => {
        offsets.push(length);
        push(`${offsets.length} 0 obj\n${body}\n`);
        if (stream) {
            push('stream\n');
            push(stream);
            push('\nendstream\n');
        }
        push('endobj\n');
    };

    const width = 432;
    const height = Math.round((width * pixelHeight) / pixelWidth);
    const draw = encoder.encode(`q ${width} 0 0 ${height} 0 0 cm /Card Do Q`);

    push('%PDF-1.4\n');
    push(new Uint8Array([0x25, 0xe2, 0xe3, 0xcf, 0xd3, 0x0a]));
    object('<< /Type /Catalog /Pages 2 0 R >>');
    object('<< /Type /Pages /Kids [3 0 R] /Count 1 >>');
    object(`<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ${width} ${height}] /Resources << /XObject << /Card 4 0 R >> >> /Contents 5 0 R >>`);
    object(`<< /Type /XObject /Subtype /Image /Width ${pixelWidth} /Height ${pixelHeight} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${image.length} >>`, image);
    object(`<< /Length ${draw.length} >>`, draw);
    object(`<< /Title ${pdfText(title)} /Producer (OSIF KARBALA) >>`);

    const xref = length;
    push(`xref\n0 ${offsets.length + 1}\n0000000000 65535 f \n`);
    offsets.forEach((offset) => push(`${String(offset).padStart(10, '0')} 00000 n \n`));
    push(`trailer\n<< /Size ${offsets.length + 1} /Root 1 0 R /Info ${offsets.length} 0 R >>\nstartxref\n${xref}\n%%EOF\n`);

    return new Blob(chunks, { type: 'application/pdf' });
}

const toBlob = (element, type, quality) => new Promise((resolve, reject) => {
    element.toBlob((blob) => (blob ? resolve(blob) : reject(new Error('Could not export the card.'))), type, quality);
});

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/** The card, its print PDF and the wide link preview for one attendee. */
async function drawFiles(data, vip) {
    const [, osif, syndicate, university] = await Promise.all([
        ensureFonts(data.font),
        loadImage(data.logos.osif),
        loadImage(data.logos.syndicate),
        loadImage(data.logos.university),
    ]);
    const logos = { osif, syndicate, university };
    const card = { ...data, vip };

    const image = await toBlob(drawCard(card, logos), 'image/png');
    // The PDF is drawn at twice the size so it stays sharp when printed.
    const print = await toBlob(drawCard(card, logos, 2), 'image/jpeg', 0.9);
    const pdf = await pdfFrom(print, 2160, 2700, `${label(card)} — ${card.name}`);
    const preview = await toBlob(drawPreview(card, logos), 'image/jpeg', 0.88);

    return { card: image, pdf: new File([pdf], data.pdfName, { type: 'application/pdf' }), preview };
}

async function uploadFiles(url, files, vip) {
    const body = new FormData();
    body.append('card', files.card, 'card.png');
    body.append('preview', files.preview, 'preview.jpg');
    body.append('pdf', files.pdf, 'invitation.pdf');
    body.append('vip', vip ? '1' : '0');

    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body,
    });

    if (! response.ok) {
        throw new Error(`Upload failed with ${response.status}`);
    }
}

window.osifInvitation = (data) => ({
    data,
    vip: data.vip,
    status: 'loading', // loading → uploading → ready | error
    uploadFailed: false,
    previewUrl: null,
    blob: null,
    pdf: null,

    get busy() {
        return this.status === 'loading' || this.status === 'uploading';
    },

    get chatUrl() {
        return this.data.chatUrls[this.vip ? 'vip' : 'standard'];
    },

    init() {
        return this.render();
    },

    /** Switches between the standard and VIP card; the upload saves the choice. */
    setVip(vip) {
        if (this.busy || this.vip === vip) {
            return;
        }

        this.vip = vip;
        this.render();
    },

    async render() {
        this.status = 'loading';
        this.uploadFailed = false;
        this.blob = null;
        this.discardPreview();

        try {
            const files = await drawFiles(this.data, this.vip);
            this.blob = files.card;
            this.pdf = files.pdf;
            this.previewUrl = URL.createObjectURL(this.blob);
            this.status = 'uploading';

            // Toggling is locked while busy, so this is the type just drawn.
            await uploadFiles(this.data.uploadUrl, files, this.vip);
        } catch (error) {
            console.error(error);
            this.uploadFailed = Boolean(this.blob);
        }

        this.status = this.blob && ! this.uploadFailed ? 'ready' : 'error';
    },

    destroy() {
        this.discardPreview();
    },

    discardPreview() {
        if (this.previewUrl) {
            URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = null;
        }
    },

    downloadPdf() {
        const url = URL.createObjectURL(this.pdf);
        this.save(url, this.data.pdfName);
        setTimeout(() => URL.revokeObjectURL(url), 10000);
    },

    download() {
        this.save(this.previewUrl, this.data.filename);
    },

    save(href, filename) {
        const link = document.createElement('a');
        link.href = href;
        link.download = filename;
        document.body.append(link);
        link.click();
        link.remove();
    },
});

/** After this many failures in a row the API is likely refusing everything, so the run stops. */
const STOP_AFTER_FAILURES = 3;

/**
 * «إرسال للجميع»: goes through the attendees still waiting for an invitation,
 * drawing and uploading each card here (only the browser shapes the Arabic
 * text) before the server sends it with the WhatsApp template. The server
 * skips anyone already invited, so a stopped run can simply be started again.
 * Used by resources/views/filament/osif/bulk-invitations-modal.blade.php.
 */
window.osifBulkInvitations = ({ queue }) => ({
    queue,
    status: 'idle', // idle → running → done | stopped
    next: 0,
    sent: 0,
    skipped: 0,
    failures: [],
    current: null,
    stopReason: null,

    get percent() {
        return this.queue.length ? Math.round((this.next / this.queue.length) * 100) : 0;
    },

    init() {
        this.warnOnLeave = (event) => {
            if (this.status === 'running') {
                event.preventDefault();
                event.returnValue = '';
            }
        };
        window.addEventListener('beforeunload', this.warnOnLeave);
    },

    destroy() {
        // Closing the dialog ends the run after the attendee in progress.
        this.status = this.status === 'running' ? 'stopped' : this.status;
        window.removeEventListener('beforeunload', this.warnOnLeave);
    },

    stop() {
        this.stopReason = 'إيقاف يدوي';
        this.status = 'stopped';
    },

    async start() {
        this.status = 'running';
        this.stopReason = null;
        let failuresInARow = 0;

        while (this.status === 'running' && this.next < this.queue.length) {
            const item = this.queue[this.next];
            this.current = item.name;

            try {
                const response = await fetch(item.payload, { credentials: 'same-origin', headers: { Accept: 'application/json' } });

                if (! response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                await uploadFiles(data.uploadUrl, await drawFiles(data, false), false);

                const sent = await fetch(data.sendUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                });

                if (sent.status === 409) {
                    this.skipped++;
                } else if (! sent.ok) {
                    const { message } = await sent.json().catch(() => ({}));
                    throw new Error(message || `HTTP ${sent.status}`);
                } else {
                    this.sent++;
                }

                failuresInARow = 0;
            } catch (error) {
                console.error(error);
                this.failures.push({ name: item.name, message: error.message });

                if (++failuresInARow >= STOP_AFTER_FAILURES) {
                    this.stopReason = error.message;
                    this.status = 'stopped';
                }
            }

            this.next++;
        }

        this.current = null;
        this.status = this.next >= this.queue.length ? 'done' : 'stopped';
        // The list behind the dialog shows the new statuses and counts.
        this.$wire.$refresh();
    },
});
