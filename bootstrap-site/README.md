# نقابة أطباء الأسنان – فرع كربلاء المقدسة

الموقع الرسمي (واجهة أمامية) — بوابة حكومية بأسلوب مؤسسي.
Official-style government portal front-end for the Karbala Dental Association.

## التقنيات / Tech stack

- HTML5 (semantic, RTL / Arabic-first)
- CSS3 (custom design system in `css/styles.css`)
- Bootstrap 5.3 (RTL build, via CDN)
- Bootstrap Icons 1.11 (via CDN)
- Vanilla JavaScript (`js/main.js`) — **no jQuery, no framework**
- Google Font: Cairo

No Tailwind · No React · No Vue · No jQuery · No inline CSS · No inline JS.

## البنية / Structure

```
bootstrap-site/
├── index.html            # كامل الصفحة (جميع الأقسام)
├── css/styles.css        # نظام التصميم والأنماط
├── js/main.js            # التفاعلات (عدّادات، فلاتر، تحقق النموذج…)
└── assets/
    ├── logo.svg, favicon.svg, avatar.svg
    └── partners/*.svg     # شعارات الجهات الشريكة
```

## التشغيل / Run

It's a static site — open `index.html` directly, or serve locally:

```bash
cd bootstrap-site
python3 -m http.server 8080
# ثم افتح / then open:  http://localhost:8080
```

> An internet connection is required for the Bootstrap, Bootstrap Icons and
> Cairo font CDNs, and for the photographic placeholder images
> (`picsum.photos`). To go fully offline, download those assets locally and
> update the `<link>`/`<img>` references.

## الأقسام / Sections

الهيدر · الهيرو (سلايدر) · الخدمات الإلكترونية · النشاطات · عن النقابة ·
مجلس النقابة · الإحصائيات · النشاطات (معرض بفلاتر) · الفيديوهات ·
الفعاليات القادمة · الإعلانات · مركز التحميل · الشركاء · وسائل التواصل ·
اتصل بنا (نموذج + خريطة) · الفوتر.

## الألوان / Design tokens

| Token            | Value     |
| ---------------- | --------- |
| Primary          | `#0C2D6B` |
| Secondary (gold) | `#C89A2B` |
| Background       | `#ffffff` |
| Light section    | `#F8FAFC` |
| Border           | `#E5E7EB` |
| Radius           | `16px`    |

## ملاحظات / Notes

- المحتوى النصي عربي واقعي (بدون Lorem Ipsum) وهو **بيانات تجريبية** يجب
  استبدالها بالمحتوى الرسمي الفعلي قبل النشر.
- الصور الفوتوغرافية عبارة عن عناصر نائبة (placeholders) من `picsum.photos`.
- روابط `href="#"` ونماذج الإرسال هي واجهة أمامية فقط (لا يوجد باك-إند).
