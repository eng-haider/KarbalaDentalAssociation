<?php

namespace App\Support;

/**
 * نقابة أطباء الأسنان – كربلاء المقدسة
 * Central provider of realistic placeholder content for the public website.
 *
 * Every method returns plain arrays so the front-end can render fully-populated
 * pages today, and the same shapes can later be swapped for Eloquent models /
 * API resources without touching the Blade views.
 */
class SiteData
{
    /** Brand / institution identity used across the layout & SEO. */
    public static function org(): array
    {
        return [
            'name'        => 'نقابة أطباء الأسنان – فرع كربلاء المقدسة',
            'short'       => 'نقابة أطباء الأسنان',
            'name_en'     => 'Karbala Dental Association',
            'tagline'     => 'بوابة رقمية رسمية لأطباء الأسنان في كربلاء المقدسة',
            'phone'       => '+964 32 123 4567',
            'email'       => 'info@karbala-dental.iq',
            'address'     => 'كربلاء المقدسة، شارع الإسكان، مجمع النقابات المهنية',
            'maps'        => 'https://www.openstreetmap.org/export/embed.html?bbox=44.00%2C32.59%2C44.08%2C32.64&layer=mapnik',
            'hours'       => [
                ['day' => 'الأحد – الخميس', 'time' => '٨:٣٠ ص – ٢:٣٠ م'],
                ['day' => 'السبت',          'time' => '٩:٠٠ ص – ١:٠٠ م'],
                ['day' => 'الجمعة',         'time' => 'عطلة رسمية'],
            ],
            'socials'     => [
                ['name' => 'فيسبوك',   'icon' => 'facebook',  'url' => '#'],
                ['name' => 'تيليجرام', 'icon' => 'telegram',  'url' => '#'],
                ['name' => 'إنستغرام', 'icon' => 'instagram', 'url' => '#'],
                ['name' => 'يوتيوب',   'icon' => 'youtube',   'url' => '#'],
            ],
        ];
    }

    /** Primary navigation links (anchor-based on the single landing page). */
    public static function nav(): array
    {
        return [
            ['label' => 'الرئيسية',          'href' => '#hero'],
            ['label' => 'الأخبار',           'href' => '#news'],
            ['label' => 'الإعلانات',         'href' => '#announcements'],
            ['label' => 'التعليم المستمر',   'href' => '#education'],
            ['label' => 'الخدمات الرقمية',   'href' => '#services'],
            ['label' => 'الفعاليات',         'href' => '#events'],
            ['label' => 'المركز الإعلامي',   'href' => '#media'],
            ['label' => 'عن النقابة',        'href' => '#about'],
            ['label' => 'تواصل معنا',        'href' => '#contact'],
        ];
    }

    /** Hero statistics + section counters. */
    public static function stats(): array
    {
        return [
            ['value' => 4280, 'suffix' => '+', 'label' => 'طبيب أسنان منتسب', 'icon' => 'users'],
            ['value' => 96,   'suffix' => '',  'label' => 'عيادة مرخّصة',      'icon' => 'building'],
            ['value' => 312,  'suffix' => '+', 'label' => 'دورة تعليمية',      'icon' => 'academic'],
            ['value' => 27,   'suffix' => '',  'label' => 'عاماً من العطاء',   'icon' => 'flag'],
        ];
    }

    /** Hero slider — rotating banners for the top of the home page. */
    public static function heroSlides(): array
    {
        return [
            [
                'eyebrow'      => 'بوابة رقمية رسمية — محدّثة باستمرار',
                'title_main'   => 'نقابة أطباء الأسنان',
                'title_accent' => 'في كربلاء المقدسة',
                'desc'         => 'مرحباً بكم في المنصة الرسمية للنقابة. خدمات إلكترونية متكاملة، أخبار موثّقة، وتعليم طبي مستمر — كل ذلك في مكان واحد يخدم الطبيب والمجتمع.',
                'icon'         => 'verify',
                'ctas'         => [
                    ['label' => 'آخر الأخبار',     'href' => '#news',      'icon' => 'sparkle',  'style' => 'light'],
                    ['label' => 'الخدمات',         'href' => '#services',  'icon' => 'verify',   'style' => 'accent'],
                    ['label' => 'التعليم المستمر', 'href' => '#education', 'icon' => 'academic', 'style' => 'ghost'],
                ],
            ],
            [
                'eyebrow'      => 'الخدمات الإلكترونية',
                'title_main'   => 'أنجز معاملاتك',
                'title_accent' => 'دون عناء الانتظار',
                'desc'         => 'جدّد عضويتك، وتحقّق من ترخيص الأطباء، واطلب شهاداتك المهنية إلكترونياً على مدار الساعة ومن أي جهاز.',
                'icon'         => 'renew',
                'ctas'         => [
                    ['label' => 'تجديد العضوية',    'href' => '#services', 'icon' => 'renew',  'style' => 'accent'],
                    ['label' => 'التحقق من طبيب',   'href' => '#services', 'icon' => 'verify', 'style' => 'ghost'],
                ],
            ],
            [
                'eyebrow'      => 'التعليم الطبي المستمر',
                'title_main'   => 'تعلّم وتطوّر مهاراتك',
                'title_accent' => 'بدورات معتمدة',
                'desc'         => 'مكتبة متنامية من المحاضرات وقوائم التشغيل المتخصصة بإشراف نخبة من الأطباء، مع ساعات تعليم مستمر معتمدة.',
                'icon'         => 'academic',
                'ctas'         => [
                    ['label' => 'تصفّح الدورات',     'href' => '#education', 'icon' => 'academic', 'style' => 'accent'],
                    ['label' => 'الدرس المميز',      'href' => '#education', 'icon' => 'play',     'style' => 'ghost'],
                ],
            ],
            [
                'eyebrow'      => 'فعالية قادمة',
                'title_main'   => 'المؤتمر العلمي السنوي',
                'title_accent' => 'الثامن لطب الأسنان',
                'desc'         => 'ثلاثة أيام تجمع نخبة الأطباء والباحثين لمناقشة أحدث تقنيات زراعة الأسنان وطب الأسنان الرقمي. سجّل حضورك مبكراً.',
                'icon'         => 'users',
                'ctas'         => [
                    ['label' => 'سجّل الآن',          'href' => '#events', 'icon' => 'check',    'style' => 'accent'],
                    ['label' => 'تفاصيل المؤتمر',     'href' => '#news',   'icon' => 'arrow-left', 'style' => 'ghost'],
                ],
            ],
        ];
    }

    /** Latest news cards. */
    public static function news(): array
    {
        return [
            [
                'category' => 'مؤتمرات',
                'tone'     => 'brand',
                'date'     => '٢٤ حزيران ٢٠٢٦',
                'title'    => 'انطلاق المؤتمر العلمي السنوي الثامن لطب الأسنان في كربلاء',
                'excerpt'  => 'يجمع المؤتمر نخبة من الأطباء والباحثين لمناقشة أحدث تقنيات زراعة الأسنان وطب الأسنان الرقمي على مدى ثلاثة أيام.',
                'image'    => 'conference',
                'reading'  => '٤ دقائق',
            ],
            [
                'category' => 'تعاون',
                'tone'     => 'sky',
                'date'     => '١٨ حزيران ٢٠٢٦',
                'title'    => 'مذكرة تفاهم بين النقابة وكلية طب الأسنان لتطوير التدريب السريري',
                'excerpt'  => 'تهدف المذكرة إلى فتح برامج تدريبية مشتركة وتبادل الخبرات بين الكوادر الأكاديمية والمهنية في المحافظة.',
                'image'    => 'handshake',
                'reading'  => '٣ دقائق',
            ],
            [
                'category' => 'مجتمع',
                'tone'     => 'green',
                'date'     => '١٠ حزيران ٢٠٢٦',
                'title'    => 'حملة صحية مجانية لفحص أسنان الأطفال في مدارس المحافظة',
                'excerpt'  => 'شملت الحملة أكثر من ١٢ مدرسة وقدمت خدمات الفحص والتثقيف الصحي لما يزيد عن ١٥٠٠ طالب.',
                'image'    => 'kids',
                'reading'  => '٢ دقيقة',
            ],
        ];
    }

    /** Official announcements / notices ticker + list. */
    public static function announcements(): array
    {
        return [
            [
                'type'  => 'تعميم',
                'tone'  => 'brand',
                'icon'  => 'document',
                'date'  => '٢٦ حزيران ٢٠٢٦',
                'title' => 'تعميم رقم (١٤) بشأن تجديد إجازات مزاولة المهنة لعام ٢٠٢٦',
                'desc'  => 'يرجى من جميع الأعضاء مراجعة قسم العضوية لإكمال إجراءات التجديد قبل نهاية الشهر.',
                'urgent' => true,
            ],
            [
                'type'  => 'انتخابات',
                'tone'  => 'sky',
                'icon'  => 'ballot',
                'date'  => '٢٠ حزيران ٢٠٢٦',
                'title' => 'فتح باب الترشيح لانتخابات مجلس الفرع للدورة القادمة',
                'desc'  => 'تستقبل اللجنة طلبات الترشيح إلكترونياً وحضورياً خلال الفترة المحددة.',
                'urgent' => false,
            ],
            [
                'type'  => 'تنبيه',
                'tone'  => 'amber',
                'icon'  => 'alert',
                'date'  => '١٥ حزيران ٢٠٢٦',
                'title' => 'تنبيه بخصوص العيادات غير المرخصة ومخاطر التعامل معها',
                'desc'  => 'تدعو النقابة المواطنين للتأكد من ترخيص العيادة عبر خدمة التحقق الرقمية.',
                'urgent' => false,
            ],
            [
                'type'  => 'فعالية',
                'tone'  => 'green',
                'icon'  => 'calendar',
                'date'  => '٠٨ حزيران ٢٠٢٦',
                'title' => 'ورشة عمل حول التعقيم ومكافحة العدوى في عيادات الأسنان',
                'desc'  => 'ورشة معتمدة بساعات تعليم مستمر، الأماكن محدودة والتسجيل متاح الآن.',
                'urgent' => false,
            ],
        ];
    }

    /** Educational center: course categories + featured playlists. */
    public static function eduCategories(): array
    {
        return [
            ['label' => 'الكل',            'count' => 312],
            ['label' => 'زراعة الأسنان',   'count' => 64],
            ['label' => 'تقويم الأسنان',   'count' => 48],
            ['label' => 'طب أسنان الأطفال', 'count' => 39],
            ['label' => 'علاج الجذور',     'count' => 52],
            ['label' => 'التركيبات',       'count' => 41],
        ];
    }

    public static function playlists(): array
    {
        return [
            [
                'title'    => 'أساسيات زراعة الأسنان الحديثة',
                'category' => 'زراعة الأسنان',
                'lessons'  => 18,
                'duration' => '٦ س ٤٢ د',
                'level'    => 'متقدم',
                'youtube'  => 'b1nXZqL3X-A',
                'featured' => true,
            ],
            [
                'title'    => 'تقويم الأسنان للمبتدئين',
                'category' => 'تقويم الأسنان',
                'lessons'  => 12,
                'duration' => '٤ س ١٥ د',
                'level'    => 'مبتدئ',
                'youtube'  => 'M7lc1UVf-VE',
                'featured' => false,
            ],
            [
                'title'    => 'علاج جذور الأسنان خطوة بخطوة',
                'category' => 'علاج الجذور',
                'lessons'  => 22,
                'duration' => '٧ س ٠٨ د',
                'level'    => 'متوسط',
                'youtube'  => 'ScMzIvxBSi4',
                'featured' => false,
            ],
            [
                'title'    => 'إدارة عيادة الأسنان الرقمية',
                'category' => 'إدارة',
                'lessons'  => 9,
                'duration' => '٣ س ٣٠ د',
                'level'    => 'مبتدئ',
                'youtube'  => 'aqz-KE-bpKQ',
                'featured' => false,
            ],
        ];
    }

    /** Digital services grid. */
    public static function services(): array
    {
        return [
            ['icon' => 'verify',    'title' => 'التحقق من العضوية',  'desc' => 'تأكد من حالة عضوية أي طبيب أسنان وترخيص عيادته فوراً.', 'cta' => 'تحقّق الآن', 'tone' => 'brand'],
            ['icon' => 'renew',     'title' => 'تجديد العضوية',      'desc' => 'جدّد اشتراكك السنوي وإجازة مزاولة المهنة إلكترونياً.', 'cta' => 'ابدأ التجديد', 'tone' => 'sky'],
            ['icon' => 'request',   'title' => 'الطلبات والمعاملات', 'desc' => 'قدّم طلباتك الرسمية وتابع حالتها في أي وقت.', 'cta' => 'تقديم طلب', 'tone' => 'green'],
            ['icon' => 'download',  'title' => 'مركز التحميلات',     'desc' => 'استمارات، تعاميم، ولوائح رسمية جاهزة للتحميل.', 'cta' => 'تصفّح الملفات', 'tone' => 'brand'],
            ['icon' => 'calendar',  'title' => 'حجز المواعيد',       'desc' => 'احجز موعداً مع أقسام النقابة دون انتظار.', 'cta' => 'احجز موعداً', 'tone' => 'sky'],
            ['icon' => 'certificate','title' => 'إصدار الشهادات',    'desc' => 'اطلب شهادات الخبرة وحسن السيرة المهنية رقمياً.', 'cta' => 'اطلب شهادة', 'tone' => 'green'],
        ];
    }

    /** Upcoming events timeline. */
    public static function events(): array
    {
        return [
            ['day' => '٠٥', 'month' => 'تموز', 'time' => '١٠:٠٠ ص', 'title' => 'ندوة طب الأسنان الرقمي وتطبيقات الذكاء الاصطناعي', 'place' => 'قاعة النقابة الكبرى', 'tag' => 'ندوة', 'tone' => 'brand', 'soon' => true],
            ['day' => '١٢', 'month' => 'تموز', 'time' => '٤:٠٠ م',  'title' => 'ورشة عملية: تقنيات التبييض الحديثة',                'place' => 'مركز التدريب السريري', 'tag' => 'ورشة', 'tone' => 'sky',   'soon' => false],
            ['day' => '٢٠', 'month' => 'تموز', 'time' => '٩:٠٠ ص',  'title' => 'الملتقى السنوي لأطباء الأسنان الشباب',             'place' => 'فندق كربلاء الدولي',   'tag' => 'ملتقى', 'tone' => 'green', 'soon' => false],
            ['day' => '٢٨', 'month' => 'تموز', 'time' => '٦:٠٠ م',  'title' => 'محاضرة: أخلاقيات وأنظمة مهنة طب الأسنان',          'place' => 'بث مباشر عبر الإنترنت', 'tag' => 'محاضرة', 'tone' => 'brand', 'soon' => false],
        ];
    }

    /** Media center: photos, videos, publications. */
    public static function gallery(): array
    {
        return [
            ['title' => 'المؤتمر العلمي السنوي', 'kind' => 'photo', 'count' => 42, 'seed' => 'conf1'],
            ['title' => 'حملة صحة الفم',         'kind' => 'photo', 'count' => 28, 'seed' => 'camp2'],
            ['title' => 'تكريم الأطباء',          'kind' => 'photo', 'count' => 36, 'seed' => 'hon3'],
            ['title' => 'ورش التعقيم',            'kind' => 'video', 'count' => 8,  'seed' => 'ster4'],
        ];
    }

    public static function publications(): array
    {
        return [
            ['title' => 'النشرة الفصلية – العدد ٢٤', 'type' => 'PDF', 'size' => '٣٫٢ م.ب', 'date' => 'حزيران ٢٠٢٦'],
            ['title' => 'دليل ترخيص العيادات الجديد',  'type' => 'PDF', 'size' => '١٫٨ م.ب', 'date' => 'أيار ٢٠٢٦'],
            ['title' => 'لائحة التعليم الطبي المستمر',  'type' => 'PDF', 'size' => '٩٤٠ ك.ب', 'date' => 'نيسان ٢٠٢٦'],
        ];
    }

    /** About: mission / vision / message. */
    public static function about(): array
    {
        return [
            ['icon' => 'target',  'title' => 'رسالتنا', 'text' => 'تنظيم مهنة طب الأسنان والارتقاء بها، وحماية حقوق الأعضاء، وتقديم خدمات رقمية تليق بمكانة المهنة في كربلاء المقدسة.'],
            ['icon' => 'eye',     'title' => 'رؤيتنا',  'text' => 'أن نكون نقابة مهنية رائدة رقمياً، تُسهم في بناء منظومة صحية فموية متقدمة تخدم المجتمع وفق أعلى المعايير.'],
            ['icon' => 'heart',   'title' => 'قيمنا',   'text' => 'النزاهة، والشفافية، والتطوير المستمر، والعمل بروح الفريق لخدمة الأطباء والمرضى على حدٍّ سواء.'],
        ];
    }
}
