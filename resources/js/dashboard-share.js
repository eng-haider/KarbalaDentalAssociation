// The dashboards' «مشاركة» buttons (company page, products): share, or say the link was copied.

import { shareLink } from './share';

window.shareLink = async (data) => {
    if (await shareLink(data) === 'copied') {
        new window.FilamentNotification()
            .title('تم نسخ الرابط')
            .body('الصقه في واتساب أو أي تطبيق لمشاركته.')
            .success()
            .send();
    }
};
