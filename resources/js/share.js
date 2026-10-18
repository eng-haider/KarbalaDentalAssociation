// Sharing a link the way doctors and companies actually share: the phone's own
// share sheet (WhatsApp, Telegram…) where there is one, otherwise the link is
// copied, and as a last resort WhatsApp opens with the link ready to send.

/** @returns {Promise<'shared'|'cancelled'|'copied'|'whatsapp'>} */
export async function shareLink({ title, text, url }) {
    if (navigator.share) {
        try {
            await navigator.share({ title, text, url });
            return 'shared';
        } catch (error) {
            if (error?.name === 'AbortError') {
                return 'cancelled';
            }
        }
    }

    try {
        await navigator.clipboard.writeText(url);
        return 'copied';
    } catch {
        window.open(`https://wa.me/?text=${encodeURIComponent(`${text ?? title}\n${url}`)}`, '_blank', 'noopener');
        return 'whatsapp';
    }
}
