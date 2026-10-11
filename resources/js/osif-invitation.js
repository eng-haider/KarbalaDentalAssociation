// Draws an attendee's OSIF invitation card in the browser and hands it to
// WhatsApp. Canvas text is shaped by the browser, so Arabic renders correctly
// without a headless Chrome on the server. Used by the "إرسال الدعوة" modal
// (resources/views/filament/osif/invitation-modal.blade.php).

const WIDTH = 1080;
const HEIGHT = 1350;
const COLOR = {
    page: '#f4eefa',
    card: '#ffffff',
    ink: '#16021f',
    heading: '#2f1640',
    purple: '#5f168f',
    violet: '#8f32d1',
    muted: '#745e7e',
    line: '#e6dbee',
    pale: '#f7efff',
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
    ctx.beginPath();
    ctx.moveTo(x + radius, y);
    ctx.arcTo(x + width, y, x + width, y + height, radius);
    ctx.arcTo(x + width, y + height, x, y + height, radius);
    ctx.arcTo(x, y + height, x, y, radius);
    ctx.arcTo(x, y, x + width, y, radius);
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

function text(ctx, value, x, y, { weight = 400, size = 28, color = COLOR.ink, align = 'center' } = {}) {
    ctx.font = font(weight, size);
    ctx.fillStyle = color;
    ctx.textAlign = align;
    ctx.fillText(value, x, y);
}

function line(ctx, x1, y1, x2, y2, color = COLOR.line, width = 2) {
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.strokeStyle = color;
    ctx.lineWidth = width;
    ctx.stroke();
}

function drawLogo(ctx, image, cx, cy, { brand = false } = {}) {
    const radius = 80;

    ctx.beginPath();
    ctx.arc(cx, cy, radius, 0, Math.PI * 2);
    ctx.fillStyle = brand ? COLOR.purple : COLOR.card;
    ctx.fill();

    if (! brand) {
        ctx.strokeStyle = COLOR.line;
        ctx.lineWidth = 2;
        ctx.stroke();
    }

    if (image) {
        const size = brand ? 116 : 136;
        ctx.drawImage(image, cx - size / 2, cy - size / 2, size, size);
    }
}

async function draw(data) {
    const [, osif, syndicate, university] = await Promise.all([
        ensureFonts(data.font),
        loadImage(data.logos.osif),
        loadImage(data.logos.syndicate),
        loadImage(data.logos.university),
    ]);

    const canvas = document.createElement('canvas');
    canvas.width = WIDTH;
    canvas.height = HEIGHT;

    const ctx = canvas.getContext('2d');
    ctx.direction = 'rtl';
    ctx.textBaseline = 'alphabetic';

    // Page and card.
    ctx.fillStyle = COLOR.page;
    ctx.fillRect(0, 0, WIDTH, HEIGHT);

    const card = { x: 48, y: 48, width: WIDTH - 96, height: HEIGHT - 96 };
    roundRect(ctx, card.x, card.y, card.width, card.height, 36);
    ctx.fillStyle = COLOR.card;
    ctx.fill();
    ctx.strokeStyle = COLOR.line;
    ctx.lineWidth = 2;
    ctx.stroke();

    ctx.save();
    roundRect(ctx, card.x, card.y, card.width, card.height, 36);
    ctx.clip();
    ctx.fillStyle = COLOR.violet;
    ctx.fillRect(card.x, card.y, card.width, 14);
    ctx.restore();

    // The three organisers, equal weight, OSIF first (right-hand side).
    const logos = [
        { x: 840, image: osif, label: 'OSIF KARBALA', brand: true },
        { x: 540, image: syndicate, label: 'نقابة أطباء أسنان كربلاء' },
        { x: 240, image: university, label: 'جامعة السبطين للعلوم الطبية' },
    ];

    for (const logo of logos) {
        drawLogo(ctx, logo.image, logo.x, 205, { brand: logo.brand });
        const size = fit(ctx, logo.label, 700, 24, 270, 18);
        text(ctx, logo.label, logo.x, 330, { weight: 700, size, color: COLOR.heading });
    }

    line(ctx, 690, 140, 690, 340);
    line(ctx, 390, 140, 390, 340);
    line(ctx, 150, 385, WIDTH - 150, 385);

    // Heading.
    ctx.font = font(700, 30);
    const kicker = 'دعوة حضور';
    const kickerHalf = ctx.measureText(kicker).width / 2;
    text(ctx, kicker, WIDTH / 2, 455, { weight: 700, size: 30, color: COLOR.violet });
    line(ctx, WIDTH / 2 + kickerHalf + 24, 445, WIDTH / 2 + kickerHalf + 84, 445, COLOR.violet, 3);
    line(ctx, WIDTH / 2 - kickerHalf - 84, 445, WIDTH / 2 - kickerHalf - 24, 445, COLOR.violet, 3);

    ctx.font = font(800, 44);
    let y = 525;
    for (const titleLine of wrap(ctx, data.event, 860).slice(0, 2)) {
        text(ctx, titleLine, WIDTH / 2, y, { weight: 800, size: 44, color: COLOR.heading });
        y += 60;
    }

    text(ctx, data.brand, WIDTH / 2, y - 6, { weight: 700, size: 26, color: COLOR.purple });

    // The guest.
    y += 76;
    text(ctx, 'يسرّنا دعوتكم لحضور فعاليات الملتقى', WIDTH / 2, y, { size: 30, color: COLOR.muted });

    y += 92;
    const nameSize = fit(ctx, data.name, 800, 66, 880, 42);
    ctx.font = font(800, nameSize);
    const nameLines = ctx.measureText(data.name).width > 880 ? wrap(ctx, data.name, 880).slice(0, 2) : [data.name];
    for (const nameLine of nameLines) {
        text(ctx, nameLine, WIDTH / 2, y, { weight: 800, size: nameSize, color: COLOR.purple });
        y += nameSize + 14;
    }

    if (data.title) {
        text(ctx, data.title, WIDTH / 2, y - 4, { weight: 600, size: 30, color: COLOR.muted });
        y += 40;
    }

    // Date, time and place.
    const rows = [
        ['التاريخ', data.date],
        ['الوقت', data.time],
        ['المكان', data.location],
    ].filter(([, value]) => value);

    const panel = { x: 128, y: Math.max(y + 10, 860), width: WIDTH - 256, rowHeight: 82 };
    roundRect(ctx, panel.x, panel.y, panel.width, panel.rowHeight * rows.length, 24);
    ctx.fillStyle = COLOR.pale;
    ctx.fill();

    rows.forEach(([label, value], index) => {
        const rowY = panel.y + panel.rowHeight * index;
        const baseline = rowY + panel.rowHeight / 2 + 11;
        const right = panel.x + panel.width - 40;

        if (index > 0) {
            line(ctx, panel.x + 32, rowY, panel.x + panel.width - 32, rowY, '#ffffff', 3);
        }

        text(ctx, label, right, baseline, { weight: 600, size: 24, color: COLOR.muted, align: 'right' });
        const size = fit(ctx, value, 700, 30, panel.width - 210, 22);
        text(ctx, value, right - 140, baseline, { weight: 700, size, color: COLOR.ink, align: 'right' });
    });

    // Footer.
    const footerY = HEIGHT - 150;
    text(ctx, `رقم الدعوة: ${data.code}`, WIDTH / 2, footerY, { weight: 700, size: 28, color: COLOR.heading });
    text(ctx, 'يرجى إبراز هذه البطاقة عند الحضور', WIDTH / 2, footerY + 46, { size: 24, color: COLOR.muted });

    return canvas;
}

window.osifInvitation = (data) => ({
    data,
    status: 'loading',
    previewUrl: null,
    blob: null,
    file: null,
    canShare: false,
    canCopy: false,
    touch: false,
    onPhone: false,
    copied: false,

    async init() {
        try {
            const canvas = await draw(this.data);

            this.blob = await new Promise((resolve, reject) => {
                canvas.toBlob((blob) => (blob ? resolve(blob) : reject(new Error('Could not export the card.'))), 'image/png');
            });
            this.file = new File([this.blob], this.data.filename, { type: 'image/png' });
            this.previewUrl = URL.createObjectURL(this.blob);
            this.canShare = Boolean(navigator.canShare?.({ files: [this.file] }));
            // Desktop browsers can share files too, but WhatsApp Web is reached by pasting.
            this.touch = window.matchMedia('(pointer: coarse)').matches;
            this.onPhone = this.canShare && this.touch;
            this.canCopy = Boolean(navigator.clipboard?.write && window.ClipboardItem);
            this.status = 'ready';
        } catch (error) {
            console.error(error);
            this.status = 'error';
        }
    },

    destroy() {
        if (this.previewUrl) {
            URL.revokeObjectURL(this.previewUrl);
        }
    },

    async share() {
        try {
            await navigator.share({ files: [this.file] });
        } catch (error) {
            if (error?.name !== 'AbortError') {
                this.download();
            }
        }
    },

    async copy() {
        try {
            await navigator.clipboard.write([new ClipboardItem({ 'image/png': this.blob })]);
            this.copied = true;
            setTimeout(() => (this.copied = false), 4000);
        } catch {
            this.download();
        }
    },

    download() {
        const link = document.createElement('a');
        link.href = this.previewUrl;
        link.download = this.data.filename;
        document.body.append(link);
        link.click();
        link.remove();
    },
});
