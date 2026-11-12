/**
 * «إرسال رسالة للجميع» (resources/views/filament/osif/broadcast-modal.blade.php):
 * uploads the message's image, then has the server send the WhatsApp template
 * to each number in turn, a few at a time. The server sends each number once,
 * so a stopped run carries on later without repeating anyone.
 */

/** Messages sent at the same time. */
const WORKERS = 3;

/** Failures in a row that stop the run: the key, the template or the network, not one bad number. */
const STOP_AFTER_FAILURES = 3;

const MAX_IMAGE = 5 * 1024 * 1024;

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/** A refused request in words: the server's own message where it gave one. */
async function failure(response) {
    if (response.status === 419) {
        return 'انتهت صلاحية الصفحة. حدّث الصفحة ثم أعد المحاولة.';
    }

    const { message } = await response.json().catch(() => ({}));

    return message || `HTTP ${response.status}`;
}

window.osifBroadcast = ({ storeUrl, total, needsImage, resume }) => ({
    status: 'idle', // idle → preparing → running → done | stopped
    queue: [],
    next: 0,
    done: 0,
    sent: 0,
    skipped: 0,
    failures: [],
    stopReason: null,
    error: null,
    image: null,
    preview: null,

    get percent() {
        return this.queue.length ? Math.round((this.done / this.queue.length) * 100) : 0;
    },

    init() {
        this.warnOnLeave = (event) => {
            if (this.status === 'running' || this.status === 'preparing') {
                event.preventDefault();
                event.returnValue = '';
            }
        };
        window.addEventListener('beforeunload', this.warnOnLeave);
    },

    destroy() {
        // Closing the dialog ends the run after the messages in progress.
        this.status = this.status === 'running' ? 'stopped' : this.status;
        window.removeEventListener('beforeunload', this.warnOnLeave);

        if (this.preview) {
            URL.revokeObjectURL(this.preview);
        }
    },

    pick(event) {
        const file = event.target.files?.[0];
        event.target.value = '';
        this.error = null;

        if (!file) {
            return;
        }

        if (!['image/jpeg', 'image/png'].includes(file.type)) {
            this.error = 'الصورة يجب أن تكون JPG أو PNG.';
            return;
        }

        if (file.size > MAX_IMAGE) {
            this.error = 'حجم الصورة يجب ألا يتجاوز 5 ميغابايت.';
            return;
        }

        if (this.preview) {
            URL.revokeObjectURL(this.preview);
        }

        this.image = file;
        this.preview = URL.createObjectURL(file);
    },

    stop() {
        this.stopReason = 'إيقاف يدوي';
        this.status = 'stopped';
    },

    /** A new broadcast: everyone gets the message, even those a previous one reached. */
    async start() {
        this.error = null;

        if (needsImage && !this.image) {
            this.error = 'اختر الصورة أولاً.';
            return;
        }

        if (!window.confirm(`ستُرسل الرسالة إلى ${total} رقم واتساب، ولا يمكن التراجع بعد الإرسال. هل تريد المتابعة؟`)) {
            return;
        }

        this.status = 'preparing';
        const body = new FormData();

        if (this.image) {
            body.append('image', this.image);
        }

        try {
            const response = await fetch(storeUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body,
            });

            if (!response.ok) {
                throw new Error(await failure(response));
            }

            this.queue = (await response.json()).queue;
            Object.assign(this, { next: 0, done: 0, sent: 0, skipped: 0, failures: [] });
        } catch (error) {
            this.status = 'idle';
            this.error = error.message === 'Failed to fetch' ? 'تعذّر الاتصال بالإنترنت. أعد المحاولة.' : error.message;
            return;
        }

        this.run();
    },

    /** The numbers an earlier broadcast has not reached yet. */
    carryOn() {
        if (!window.confirm(`ستُرسل الرسالة إلى ${resume.length} رقم لم تصلهم بعد. هل تريد المتابعة؟`)) {
            return;
        }

        this.error = null;
        this.queue = resume;
        this.run();
    },

    async run() {
        this.status = 'running';
        this.stopReason = null;
        let failuresInARow = 0;

        const worker = async () => {
            while (this.status === 'running' && this.next < this.queue.length) {
                const item = this.queue[this.next++];

                try {
                    const response = await fetch(item.url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                    });

                    if (response.status === 409) {
                        this.skipped++;
                    } else if (!response.ok) {
                        throw new Error(await failure(response));
                    } else {
                        this.sent++;
                    }

                    failuresInARow = 0;
                } catch (error) {
                    console.error(error);
                    this.failures.push({ name: item.name, message: error.message });

                    if (++failuresInARow >= STOP_AFTER_FAILURES && this.status === 'running') {
                        this.stopReason = error.message;
                        this.status = 'stopped';
                    }
                }

                this.done++;
            }
        };

        await Promise.all(Array.from({ length: WORKERS }, worker));

        if (this.status === 'running') {
            this.status = this.done >= this.queue.length ? 'done' : 'stopped';
        }
    },
});
