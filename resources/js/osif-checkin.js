// The conference-day gate (resources/views/osif/checkin.blade.php): reads the
// QR code on a guest's invitation with the phone's camera and checks them in.
// Uses the browser's own BarcodeDetector where there is one (Android Chrome),
// and jsQR everywhere else (iPhone Safari).

import jsQR from 'jsqr';

const root = document.querySelector('[data-checkin]');

if (root) {
    init(root);
}

const TITLES = {
    checked_in: 'تم تسجيل الدخول للمؤتمر',
    already: 'سبق تسجيل دخوله',
    invalid: 'بطاقة غير صالحة',
    error: 'لم يتم التسجيل',
};

function init(root) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const camera = root.querySelector('[data-camera]');
    const video = camera.querySelector('video');
    const cameraMessage = root.querySelector('[data-camera-message]');
    const startButton = root.querySelector('[data-camera-start]');
    const manual = root.querySelector('[data-manual]');
    const result = document.querySelector('[data-result]');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d', { willReadFrequently: true });

    let detector = null;
    let scanning = false;
    let lastFrame = 0;
    // While a request is out or a result is on screen, nothing else is scanned.
    let busy = false;
    // The card just handled stays in front of the camera for a moment; don't read it twice.
    let recent = { code: null, until: 0 };
    let closeTimer = null;

    setupDetector().finally(startCamera);

    startButton.addEventListener('click', startCamera);

    manual.addEventListener('submit', (event) => {
        event.preventDefault();
        const input = manual.elements.code;
        input.blur();
        handle(input.value, true).then((status) => {
            if (status === 'checked_in') {
                input.value = '';
            }
        });
    });

    result.addEventListener('click', close);

    // Phones stop the camera in the background; start it again on return.
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopCamera();
        } else {
            startCamera();
        }
    });

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
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });

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

            if (!busy && now - lastFrame > 150 && video.readyState >= 2) {
                lastFrame = now;
                const code = await read();

                if (code) {
                    handle(code, false);
                }
            }

            requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
    }

    /** The QR code in the middle of the picture (the square the page shows), or null. */
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

        const side = Math.min(width, height);
        const size = Math.min(side, 640);
        canvas.width = size;
        canvas.height = size;
        context.drawImage(video, (width - side) / 2, (height - side) / 2, side, side, 0, 0, size, size);

        const image = context.getImageData(0, 0, size, size);

        return jsQR(image.data, size, size, { inversionAttempts: 'dontInvert' })?.data || null;
    }

    /** @returns {Promise<string|undefined>} The result's status. */
    async function handle(value, typed) {
        const code = String(value || '').trim();

        if (!code || busy) {
            return;
        }

        if (!typed && code === recent.code && Date.now() < recent.until) {
            return;
        }

        busy = true;
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
                body: JSON.stringify({ code }),
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
        }

        // A failed request may be retried at once with the same card.
        recent = data.status === 'error' ? { code: null, until: 0 } : { code, until: Infinity };
        show(data);

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
            root.querySelector('[data-checked-in]').textContent = data.stats.checkedIn;
            root.querySelector('[data-total]').textContent = data.stats.total;
        }

        result.classList.add('is-open');
        result.querySelector('[data-result-close]').focus();
        feedback(ok);

        // A welcome goes by itself; a warning waits for a tap so it is seen.
        if (ok) {
            closeTimer = setTimeout(close, 2500);
        }
    }

    function close() {
        clearTimeout(closeTimer);
        result.classList.remove('is-open');

        if (recent.code) {
            recent.until = Date.now() + 5000;
        }

        busy = false;
    }
}

// Sound needs one tap on the page first (browser rule); vibration works on Android only.
let audio = null;

document.addEventListener('pointerdown', () => {
    try {
        audio ??= new (window.AudioContext || window.webkitAudioContext)();
        audio.resume();
    } catch {
        audio = null;
    }
});

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
