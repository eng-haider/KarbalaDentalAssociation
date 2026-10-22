// Article photos open full size in Fancybox: the main photo and any placed in
// the text, as one gallery to swipe through.

import { Fancybox } from '@fancyapps/ui/dist/fancybox/fancybox.js';
import '@fancyapps/ui/dist/fancybox/fancybox.css';

const arabic = {
    CLOSE: 'إغلاق',
    NEXT: 'التالي',
    PREV: 'السابق',
    ZOOM_IN: 'تكبير',
    ZOOM_OUT: 'تصغير',
    TOGGLE_FULL: 'تبديل التكبير',
    TOGGLE_1TO1: 'تبديل التكبير',
    ITERATE_ZOOM: 'تبديل التكبير',
    TOGGLE_FS: 'ملء الشاشة',
    TOGGLE_FULLSCREEN: 'ملء الشاشة',
    TOGGLE_THUMBS: 'الصور المصغّرة',
    TOGGLE_AUTOPLAY: 'عرض تلقائي',
    DOWNLOAD: 'تنزيل',
    IMAGE_ERROR: 'تعذّر تحميل الصورة.',
    ERROR: 'حدث خطأ، حاول مرة أخرى.',
    MODAL: 'يمكنك الإغلاق بزر Esc',
};

// Photos in the article text join the gallery, unless they already link somewhere.
document.querySelectorAll('.article-body img').forEach((img) => {
    if (!img.closest('a')) {
        img.dataset.fancybox = 'article';
    }
});

Fancybox.bind('[data-fancybox]', { l10n: arabic });
