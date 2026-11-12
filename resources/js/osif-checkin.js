// The conference-day scanner at a hall's door (resources/views/osif/checkin.blade.php):
// checks a guest into that hall from the QR code on their invitation, read either by the phone's
// camera or by a USB/Bluetooth QR reader that types like a keyboard.
// The camera uses the browser's own BarcodeDetector where there is one
// (Android Chrome), and jsQR everywhere else (iPhone Safari).

import jsQR from 'jsqr';

const root = document.querySelector('[data-checkin]');

if (root) {
    init(root);
}

const TITLES = {
    checked_in: 'تم تسجيل الدخول',
    already: 'سبق تسجيل دخوله إلى هذه القاعة',
    invalid: 'بطاقة غير صالحة',
    error: 'لم يتم التسجيل',
};

/** An invitation link is complete once its 40-character token has arrived. */
const INVITATION = /\/osif\/invitation\/[A-Za-z0-9]{40}/;

/** A QR reader types far faster than a person; a longer pause ends its burst. */
const READER_GAP = 80;

/**
 * A fast run this long is a reader, not a person. A reader set to type only
 * the end of the link (its last 10 characters, say) sends about this many.
 */
const READER_MIN = 8;

/** A reader that sends no Enter has finished once it pauses this long. */
const READER_DONE = 150;

function init(root) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const camera = root.querySelector('[data-camera]');
    const video = camera.querySelector('video');
    const cameraMessage = root.querySelector('[data-camera-message]');
    const startButton = root.querySelector('[data-camera-start]');
    const manual = root.querySelector('[data-manual]');
    const input = manual.elements.code;
    const result = document.querySelector('[data-result]');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d', { willReadFrequently: true });

    let detector = null;
    let scanning = false;
    let lastFrame = 0;
    // One check at a time; a result on screen does not stop the next card.
    let pending = false;
    let queued = null;
    let shownAt = 0;
    // The card just handled stays in front of the camera for a moment; don't read it twice.
    let recent = { code: null, until: 0 };
    let closeTimer = null;
    // What a keyboard-style QR reader has typed so far.
    const reader = { text: '', lastAt: 0, swallowUntil: 0, doneTimer: null };

    setupDetector().finally(startCamera);

    startButton.addEventListener('click', startCamera);

    manual.addEventListener('submit', (event) => {
        event.preventDefault();
        input.blur();
        handle(input.value, 'typed').then((status) => {
            if (status === 'checked_in') {
                input.value = '';
            }
        });
    });

    // Capture phase: the reader's keys are seen before they reach the text box.
    document.addEventListener('keydown', onReaderKey, true);

    result.addEventListener('click', close);

    // Phones stop the camera in the background; start it again on return.
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopCamera();
        } else {
            startCamera();
        }
    });

    /**
     * USB/Bluetooth readers type the link the QR code holds, usually followed
     * by Enter. The link is checked the moment its token is complete, so a
     * reader without Enter works too, and keys are read by their position
     * rather than their letter, so an Arabic keyboard does not garble it.
     */
    function onReaderKey(event) {
        if (event.ctrlKey || event.metaKey || event.altKey) {
            return;
        }

        // When the key was pressed, not when this ran: a busy moment on the page must not split a scan.
        const now = event.timeStamp;
        const inBurst = now - reader.lastAt <= READER_GAP;
        reader.lastAt = now;

        // The rest of a scan already handled: its Enter, Tab or anything after the token.
        if (now < reader.swallowUntil && (inBurst || event.key === 'Enter' || event.key === 'Tab')) {
            event.preventDefault();
            return;
        }

        if (event.key === 'Enter') {
            const text = reader.text;
            reader.text = '';
            clearTimeout(reader.doneTimer);

            // Anything shorter is someone typing a code in the box: the form handles that.
            if (inBurst && (text.length >= READER_MIN || (text && document.activeElement !== input))) {
                event.preventDefault();
                fromReader(text, now);
            }

            return;
        }

        const char = keyChar(event);

        if (char === null) {
            return;
        }

        reader.text = (inBurst ? reader.text : '') + char;
        clearTimeout(reader.doneTimer);

        if (INVITATION.test(reader.text)) {
            event.preventDefault();
            fromReader(reader.text, now);
        } else if (reader.text.length >= READER_MIN) {
            // A reader without Enter, typing only the end of the link: done when it stops.
            reader.doneTimer = setTimeout(() => fromReader(reader.text, performance.now()), READER_DONE);
        }
    }

    function fromReader(text, at) {
        clearTimeout(reader.doneTimer);
        reader.text = '';
        reader.swallowUntil = at + 300;

        // A reader that typed into the box leaves no trace there.
        if (document.activeElement === input) {
            input.value = '';
            input.blur();
        }

        handle(text, 'reader');
    }

    async function setupDetector() {
        if (!('BarcodeDetector' in window)) {
            return;
        }

        try {
            const formats = await window.BarcodeDetector.getSupportedFormats();

            if (formats.includes('qr_code')) {
                detector = new window.BarcodeDetector({ formats: ['qr_code'] });
            }
        } catch {
            detector = null;
        }
    }

    async function startCamera() {
        if (video.srcObject) {
            return;
        }

        if (!navigator.mediaDevices?.getUserMedia) {
            cameraOff(window.isSecureContext
                ? 'هذا المتصفح لا يدعم الكاميرا. افتح الصفحة في Chrome أو Safari.'
                : 'الكاميرا تعمل فقط عبر رابط آمن (https).', false);
            return;
        }

        cameraOff('جارٍ تشغيل الكاميرا…', false);

        try {
            // HD instead of the 640×480 many phones give by default: small QR codes read at once.
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false,
            });

            await keepFocusing(stream.getVideoTracks()[0]);
            video.srcObject = stream;
            await video.play();
            camera.classList.remove('is-off');
            loop();
        } catch (error) {
            stopCamera();
            cameraOff(error?.name === 'NotAllowedError'
                ? 'لم يُسمح باستخدام الكاميرا. اسمح بها من إعدادات المتصفح ثم اضغط «تشغيل الكاميرا».'
                : 'تعذّر تشغيل الكاميرا. أغلق أي تطبيق آخر يستخدمها ثم حاول مجدداً.', true);
        }
    }

    /** Continuous autofocus where the phone lets the page ask for it (Android); iPhones do it themselves. */
    async function keepFocusing(track) {
        try {
            if (track?.getCapabilities?.().focusMode?.includes('continuous')) {
                await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] });
            }
        } catch {
            // Focus stays as the camera set it.
        }
    }

    function stopCamera() {
        video.srcObject?.getTracks().forEach((track) => track.stop());
        video.srcObject = null;
    }

    function cameraOff(message, canRetry) {
        camera.classList.add('is-off');
        cameraMessage.textContent = message;
        startButton.hidden = !canRetry;
    }

    function loop() {
        if (scanning) {
            return;
        }

        scanning = true;

        const tick = async (now) => {
            if (!video.srcObject) {
                scanning = false;
                return;
            }

            if (!pending && now - lastFrame > (detector ? 60 : 100) && video.readyState >= 2) {
                lastFrame = now;
                const code = await read();

                if (code) {
                    handle(code, 'camera');
                }
            }

            requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
    }

    /** The QR code inside the frame the page draws over the picture, or null. */
    async function read() {
        if (detector) {
            try {
                const codes = await detector.detect(video);
                return codes[0]?.rawValue || null;
            } catch {
                detector = null;
            }
        }

        const width = video.videoWidth;
        const height = video.videoHeight;

        if (!width || !height) {
            return null;
        }

        // The page shows the middle square; the frame covers most of it.
        const side = Math.min(width, height) * 0.8;
        const size = Math.round(Math.min(side, 600));
        canvas.width = size;
        canvas.height = size;
        context.drawImage(video, (width - side) / 2, (height - side) / 2, side, side, 0, 0, size, size);

        const image = context.getImageData(0, 0, size, size);

        return jsQR(image.data, size, size, { inversionAttempts: 'dontInvert' })?.data || null;
    }

    /**
     * @param {'camera'|'reader'|'typed'} source
     * @returns {Promise<string|undefined>} The result's status.
     */
    async function handle(value, source) {
        const code = String(value || '').trim();

        if (!code) {
            return;
        }

        if (pending) {
            // A card read during the previous check is checked right after it, not dropped.
            if (source !== 'camera') {
                queued = [code, source];
            }

            return;
        }

        if (source === 'camera') {
            if (code === recent.code && Date.now() < recent.until) {
                return;
            }

            // Another card replaces a result once it has been seen for a moment.
            if (shownAt && Date.now() - shownAt < 1000) {
                return;
            }
        }

        close();
        pending = true;
        let data;

        try {
            const response = await fetch(root.dataset.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ code, hall: root.dataset.hall }),
            });

            // Signed out or the page sat open too long: the reload goes through the login.
            if (response.status === 401 || response.status === 419) {
                window.location.reload();
                return;
            }

            if (response.status === 403) {
                data = { status: 'error', message: 'حسابك لا يملك صلاحية تسجيل الحضور.' };
            } else if (response.status === 429) {
                data = { status: 'error', message: 'محاولات كثيرة في وقت قصير. انتظر دقيقة ثم أعد المحاولة.' };
            } else {
                data = await response.json();

                if (!response.ok && response.status !== 404) {
                    data = { status: 'error', message: data.message || 'حدث خطأ في الخادم. حاول مجدداً.' };
                }
            }
        } catch {
            data = { status: 'error', message: 'تعذّر الاتصال بالإنترنت. تأكد من الشبكة ثم أعد المحاولة.' };
        } finally {
            pending = false;
        }

        // A failed request may be retried at once with the same card.
        recent = data.status === 'error' ? { code: null, until: 0 } : { code, until: Infinity };
        show(data);

        if (queued) {
            const [next, nextSource] = queued;
            queued = null;
            handle(next, nextSource);
        }

        return data.status;
    }

    function show(data) {
        const ok = data.status === 'checked_in';
        const icon = { checked_in: 'ok', already: 'warn' }[data.status] || 'bad';

        result.dataset.status = data.status;
        result.querySelectorAll('[data-icon]').forEach((svg) => {
            svg.toggleAttribute('hidden', svg.dataset.icon !== icon);
        });
        result.querySelector('[data-result-title]').textContent = TITLES[data.status] || TITLES.error;
        result.querySelector('[data-result-vip]').hidden = !data.vip;
        result.querySelector('[data-result-name]').textContent = data.name || '';
        result.querySelector('[data-result-meta]').textContent = {
            checked_in: [data.title, data.code].filter(Boolean).join(' · '),
            already: [data.time ? 'دخل الساعة ' + data.time : null, data.code].filter(Boolean).join(' · '),
        }[data.status] ?? (data.message || '');

        if (data.stats) {
            root.querySelector('[data-mine]').textContent = data.stats.mine;
            root.querySelector('[data-hall-total]').textContent = data.stats.hall;
            root.querySelector('[data-total]').textContent = data.stats.total;
        }

        result.classList.add('is-open');
        result.querySelector('[data-result-close]').focus();
        shownAt = Date.now();
        feedback(ok);

        // A welcome goes by itself; a warning stays until a tap or the next card.
        if (ok) {
            closeTimer = setTimeout(close, 1500);
        }
    }

    function close() {
        clearTimeout(closeTimer);

        if (!shownAt) {
            return;
        }

        result.classList.remove('is-open');
        shownAt = 0;

        if (recent.code) {
            recent.until = Date.now() + 4000;
        }
    }
}

const SHIFTED_DIGITS = ')!@#$%^&*(';

/** The other keys a link can hold: [plain, with Shift], as on an English keyboard. */
const SYMBOLS = {
    Minus: '-_',
    Equal: '=+',
    BracketLeft: '[{',
    BracketRight: ']}',
    Backslash: '\\|',
    Semicolon: ';:',
    Quote: '\'"',
    Backquote: '`~',
    Comma: ',<',
    Period: '.>',
    Slash: '/?',
    Space: '  ',
    NumpadDecimal: '..',
    NumpadDivide: '//',
    NumpadMultiply: '**',
    NumpadSubtract: '--',
    NumpadAdd: '++',
};

/**
 * The character a reader meant by this key. Readers press the keys of an
 * English keyboard, so the key's position is read rather than the letter the
 * device's own keyboard language (often Arabic) would put there.
 */
function keyChar(event) {
    const { code, key, shiftKey } = event;

    if (/^Key[A-Z]$/.test(code)) {
        // An English keyboard gives the letter itself, its case included.
        if (key?.length === 1 && key.toUpperCase() === code[3]) {
            return key;
        }

        return shiftKey ? code[3] : code[3].toLowerCase();
    }

    if (/^Digit\d$/.test(code)) {
        if (/^\d$/.test(key)) {
            return key;
        }

        return shiftKey ? SHIFTED_DIGITS[code[5]] : code[5];
    }

    if (/^Numpad\d$/.test(code)) {
        return code[6];
    }

    if (SYMBOLS[code]) {
        return SYMBOLS[code].includes(key) ? key : SYMBOLS[code][shiftKey ? 1 : 0];
    }

    // Keyboards that report no key position (some phones): the character itself.
    return event.key?.length === 1 ? event.key : null;
}

// Sound needs a tap or a key on the page first (browser rule); vibration works on Android only.
let audio = null;

function unlockAudio() {
    // Afterwards, so setting up sound never holds up a reader's keys.
    setTimeout(() => {
        try {
            audio ??= new (window.AudioContext || window.webkitAudioContext)();
            audio.resume();
        } catch {
            audio = null;
        }
    });
}

document.addEventListener('pointerdown', unlockAudio);
document.addEventListener('keydown', unlockAudio);

function feedback(ok) {
    navigator.vibrate?.(ok ? 120 : [220, 90, 220]);

    if (!audio) {
        return;
    }

    const tones = ok ? [[880, 0, 0.16]] : [[300, 0, 0.2], [300, 0.28, 0.2]];

    for (const [frequency, at, length] of tones) {
        const oscillator = audio.createOscillator();
        const gain = audio.createGain();
        const start = audio.currentTime + at;

        oscillator.frequency.value = frequency;
        gain.gain.setValueAtTime(0.25, start);
        gain.gain.exponentialRampToValueAtTime(0.001, start + length);
        oscillator.connect(gain).connect(audio.destination);
        oscillator.start(start);
        oscillator.stop(start + length);
    }
}
