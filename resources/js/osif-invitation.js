// Draws an attendee's OSIF invitation (a badge-style card with a QR code, the
// same card as a PDF, and a wide preview for WhatsApp's link preview) in the
// browser and uploads them, so the invitation page and WhatsApp can use them. Canvas text is
// shaped by the browser, so Arabic renders correctly without a headless Chrome
// on the server. Used by the "إرسال الدعوة" modal
// (resources/views/filament/osif/invitation-modal.blade.php).

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

/** An amber, AEEDC-style pill label; x is its right edge, or its centre when centred. */
function pill(ctx, label, x, y, { size = 28, centered = false } = {}) {
    ctx.font = font(700, size);
    const width = ctx.measureText(label).width + size * 2.2;
    const height = size * 1.9;
    const left = centered ? x - width / 2 : x - width;

    roundRect(ctx, left, y, width, height, height / 2);
    ctx.fillStyle = COLOR.amber;
    ctx.fill();
    text(ctx, label, left + width / 2, y + height / 2 + size * 0.36, { weight: 700, size, color: COLOR.amberInk });
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

function seal(ctx, image, cx, cy, radius, { brand = false } = {}) {
    ctx.beginPath();
    ctx.arc(cx, cy, radius, 0, Math.PI * 2);
    ctx.fillStyle = brand ? COLOR.purple : COLOR.white;
    ctx.fill();
    ctx.lineWidth = 3;
    ctx.strokeStyle = brand ? COLOR.amber : 'rgba(255, 255, 255, 0.35)';
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
    const WIDTH = 1080;
    const HEIGHT = 1350;
    const [element, ctx] = canvas(WIDTH, HEIGHT, scale);

    backdrop(ctx, WIDTH, HEIGHT, 860, 120);

    // The three organisers, equal weight.
    [
        { x: 800, image: logos.osif, label: 'OSIF KARBALA', brand: true },
        { x: 540, image: logos.syndicate, label: 'نقابة أطباء أسنان كربلاء' },
        { x: 280, image: logos.university, label: 'جامعة السبطين للعلوم الطبية' },
    ].forEach((logo) => {
        seal(ctx, logo.image, logo.x, 132, 58, { brand: logo.brand });
        const size = fit(ctx, logo.label, 600, 21, 236, 16);
        text(ctx, logo.label, logo.x, 228, { weight: 600, size, color: COLOR.soft });
    });

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

    y += 82;
    const nameSize = fit(ctx, data.name, 800, 62, column, 40);
    ctx.font = font(800, nameSize);
    const nameLines = ctx.measureText(data.name).width > column ? wrap(ctx, data.name, column).slice(0, 2) : [data.name];
    for (const nameLine of nameLines) {
        text(ctx, nameLine, right, y, { weight: 800, size: nameSize, color: COLOR.ink, align: 'right' });
        y += nameSize + 12;
    }

    ctx.fillStyle = COLOR.amber;
    roundRect(ctx, right - 96, y - 26, 96, 7, 3.5);
    ctx.fill();

    if (data.title) {
        const titleFit = fit(ctx, data.title, 700, 30, column, 22);
        text(ctx, data.title, right, y + 32, { weight: 700, size: titleFit, color: COLOR.purple, align: 'right' });
    }

    // When and where.
    const rows = [
        ['الموعد', [data.date, data.time].filter(Boolean).join(' – ')],
        ['المكان', data.location],
        ['الضيافة', 'تشمل الدعوة وجبة غداء'],
    ].filter(([, value]) => value);

    let rowY = 1104;
    for (const [label, value] of rows) {
        text(ctx, label, 990, rowY, { weight: 700, size: 24, color: COLOR.amber, align: 'right' });
        const size = fit(ctx, value, 700, 30, 780, 22);
        text(ctx, value, 880, rowY, { weight: 700, size, align: 'right' });
        rowY += 64;
    }

    ctx.fillStyle = COLOR.faint;
    ctx.fillRect(150, 1264, WIDTH - 300, 2);
    text(ctx, 'يرجى إبراز رمز QR عند الدخول', WIDTH / 2, 1318, { weight: 600, size: 22, color: COLOR.soft });

    return element;
}

/** 1200×630 — the size WhatsApp shows as a large link preview. */
function drawPreview(data, logos) {
    const WIDTH = 1200;
    const HEIGHT = 630;
    const [element, ctx] = canvas(WIDTH, HEIGHT);

    backdrop(ctx, WIDTH, HEIGHT, 260, 200);

    seal(ctx, logos.osif, 250, 268, 150, { brand: true });
    seal(ctx, logos.syndicate, 190, 516, 46);
    seal(ctx, logos.university, 310, 516, 46);

    const right = WIDTH - 80;
    const column = 640;
    pill(ctx, 'دعوة حضور', right, 82, { size: 26 });

    const nameSize = fit(ctx, data.name, 800, 70, column, 44);
    text(ctx, data.name, right, 250, { weight: 800, size: nameSize, align: 'right' });

    let y = 250;
    if (data.title) {
        y += 56;
        text(ctx, data.title, right, y, { weight: 600, size: fit(ctx, data.title, 600, 32, column, 22), color: COLOR.soft, align: 'right' });
    }

    ctx.fillStyle = COLOR.amber;
    roundRect(ctx, right - 96, y + 28, 96, 7, 3.5);
    ctx.fill();

    y += 100;
    text(ctx, data.event, right, y, { weight: 700, size: fit(ctx, data.event, 700, 34, column, 24), align: 'right' });

    const when = [data.date, data.time].filter(Boolean).join(' – ');
    if (when) {
        y += 56;
        text(ctx, when, right, y, { weight: 700, size: fit(ctx, when, 700, 30, column, 22), color: COLOR.amber, align: 'right' });
    }

    if (data.location) {
        y += 50;
        text(ctx, data.location, right, y, { size: fit(ctx, data.location, 400, 26, column, 20), color: COLOR.soft, align: 'right' });
    }

    // A clear benefit note that remains visible in WhatsApp's wide preview.
    pill(ctx, 'تشمل الدعوة وجبة غداء', right, 548, { size: 20 });

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

window.osifInvitation = (data) => ({
    data,
    status: 'loading', // loading → uploading → ready | error
    uploadFailed: false,
    previewUrl: null,
    blob: null,
    pdf: null,

    async init() {
        try {
            const [, osif, syndicate, university] = await Promise.all([
                ensureFonts(this.data.font),
                loadImage(this.data.logos.osif),
                loadImage(this.data.logos.syndicate),
                loadImage(this.data.logos.university),
            ]);
            const logos = { osif, syndicate, university };

            this.blob = await toBlob(drawCard(this.data, logos), 'image/png');
            this.previewUrl = URL.createObjectURL(this.blob);

            // The PDF is drawn at twice the size so it stays sharp when printed.
            const print = await toBlob(drawCard(this.data, logos, 2), 'image/jpeg', 0.9);
            const pdf = await pdfFrom(print, 2160, 2700, `دعوة حضور — ${this.data.name}`);
            this.pdf = new File([pdf], this.data.pdfName, { type: 'application/pdf' });
            this.status = 'uploading';

            const preview = await toBlob(drawPreview(this.data, logos), 'image/jpeg', 0.88);
            await this.upload(preview);
        } catch (error) {
            console.error(error);
            this.uploadFailed = Boolean(this.blob);
        }

        this.status = this.blob && ! this.uploadFailed ? 'ready' : 'error';
    },

    destroy() {
        if (this.previewUrl) {
            URL.revokeObjectURL(this.previewUrl);
        }
    },

    async upload(preview) {
        const body = new FormData();
        body.append('card', this.blob, 'card.png');
        body.append('preview', preview, 'preview.jpg');
        body.append('pdf', this.pdf, 'invitation.pdf');

        const response = await fetch(this.data.uploadUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body,
        });

        if (! response.ok) {
            throw new Error(`Upload failed with ${response.status}`);
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
