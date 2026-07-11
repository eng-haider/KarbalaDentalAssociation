@props(['org'])

<section id="contact" class="container-px scroll-mt-24 py-20 sm:py-24">
    <div x-data="reveal()" x-intersect.once="show()" class="reveal">
        <x-section-heading
            center
            eyebrow="تواصل معنا"
            title="نحن هنا للإجابة على استفساراتكم"
            subtitle="تواصل مع النقابة عبر القنوات الرسمية أو زر مقرّنا خلال أوقات الدوام." />
    </div>

    <div class="mt-12 grid gap-6 lg:grid-cols-12">
        {{-- ===== Contact cards + form ===== --}}
        <div class="lg:col-span-5">
            <div class="grid gap-4 sm:grid-cols-2">
                {{-- phone --}}
                <a href="tel:{{ $org['phone'] }}" class="group card card-hover p-5">
                    <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-700 group-hover:text-white"><x-icon name="phone" class="size-5" /></span>
                    <p class="mt-3 text-sm font-bold text-brand-900">الهاتف</p>
                    <p class="mt-0.5 text-sm text-slate-500" dir="ltr">{{ $org['phone'] }}</p>
                </a>
                {{-- email --}}
                <a href="mailto:{{ $org['email'] }}" class="group card card-hover p-5">
                    <span class="grid size-11 place-items-center rounded-xl bg-sky-50 text-sky-600 transition group-hover:bg-sky-accent group-hover:text-white"><x-icon name="mail" class="size-5" /></span>
                    <p class="mt-3 text-sm font-bold text-brand-900">البريد الإلكتروني</p>
                    <p class="mt-0.5 break-all text-sm text-slate-500">{{ $org['email'] }}</p>
                </a>
                {{-- address --}}
                <div class="card p-5 sm:col-span-2">
                    <span class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><x-icon name="pin" class="size-5" /></span>
                    <p class="mt-3 text-sm font-bold text-brand-900">العنوان</p>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $org['address'] }}</p>
                </div>
            </div>

            {{-- working hours --}}
            <div class="mt-4 card p-5">
                <div class="flex items-center gap-2.5">
                    <x-icon name="clock" class="size-5 text-brand-700" />
                    <p class="font-bold text-brand-900">أوقات الدوام الرسمي</p>
                </div>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach($org['hours'] as $h)
                        <li class="flex items-center justify-between border-b border-cloud/60 pb-2.5 last:border-0 last:pb-0">
                            <span class="font-medium text-slate-600">{{ $h['day'] }}</span>
                            <span class="font-semibold {{ $h['time'] === 'عطلة رسمية' ? 'text-rose-500' : 'text-brand-700' }}">{{ $h['time'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- ===== Form + Map ===== --}}
        <div class="lg:col-span-7">
            <div class="card overflow-hidden">
                {{-- form --}}
                <form class="p-6 sm:p-8"
                      x-data="{ sent: false }" @submit.prevent="sent = true">
                    <h3 class="text-lg font-extrabold text-brand-900">أرسل لنا رسالة</h3>
                    <p class="mt-1 text-sm text-slate-500">سنردّ على استفسارك في أقرب وقت ممكن.</p>

                    <div x-show="!sent" class="mt-6 grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-1">
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700">الاسم الكامل</span>
                            <input type="text" required placeholder="د. أحمد محمد"
                                   class="w-full rounded-xl border-0 bg-mist px-4 py-3 text-sm text-brand-900 ring-1 ring-inset ring-cloud transition focus:bg-white focus:ring-2 focus:ring-brand-500">
                        </label>
                        <label class="block sm:col-span-1">
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700">رقم الهاتف</span>
                            <input type="tel" required placeholder="07XX XXX XXXX" dir="ltr"
                                   class="w-full rounded-xl border-0 bg-mist px-4 py-3 text-right text-sm text-brand-900 ring-1 ring-inset ring-cloud transition focus:bg-white focus:ring-2 focus:ring-brand-500">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700">الموضوع</span>
                            <input type="text" placeholder="موضوع الرسالة"
                                   class="w-full rounded-xl border-0 bg-mist px-4 py-3 text-sm text-brand-900 ring-1 ring-inset ring-cloud transition focus:bg-white focus:ring-2 focus:ring-brand-500">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700">نص الرسالة</span>
                            <textarea rows="4" required placeholder="اكتب رسالتك هنا…"
                                      class="w-full resize-none rounded-xl border-0 bg-mist px-4 py-3 text-sm text-brand-900 ring-1 ring-inset ring-cloud transition focus:bg-white focus:ring-2 focus:ring-brand-500"></textarea>
                        </label>
                        <div class="sm:col-span-2">
                            <button type="submit" class="btn-primary w-full sm:w-auto">
                                <x-icon name="send" class="size-4" /> إرسال الرسالة
                            </button>
                        </div>
                    </div>

                    {{-- success state --}}
                    <div x-show="sent" x-transition class="mt-6 flex flex-col items-center gap-3 rounded-2xl bg-emerald-50 p-8 text-center ring-1 ring-emerald-100" style="display:none;">
                        <span class="grid size-14 place-items-center rounded-full bg-emerald-100 text-emerald-600"><x-icon name="check" class="size-7" /></span>
                        <p class="font-bold text-emerald-800">تم استلام رسالتك بنجاح</p>
                        <p class="text-sm text-emerald-700">شكراً لتواصلك معنا، سنردّ عليك قريباً.</p>
                        <button @click="sent=false" class="btn-soft mt-1">إرسال رسالة أخرى</button>
                    </div>
                </form>

                {{-- map --}}
                <div class="border-t border-cloud">
                    <div class="relative aspect-[21/9] bg-cloud">
                        <iframe src="{{ $org['maps'] }}" class="absolute inset-0 h-full w-full"
                                style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                title="موقع النقابة على الخريطة"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
