@php
    /** @var \App\Models\Company $company */
    use App\Models\CompanyProduct;
    use App\Support\Arabic;

    $summary = $company->description ?: 'منتجات وعروض '.$company->name.' في ملتقى OSIF KARBALA 2026.';
@endphp

<x-layouts.osif :title="$company->name.' — الشركات المشاركة | OSIF KARBALA 2026'" :description="$summary">
    <x-slot:head>
        @if ($company->coverUrl() || $company->logoUrl())
            <meta property="og:image" content="{{ url($company->coverUrl() ?? $company->logoUrl()) }}">
        @endif
        <meta property="og:description" content="{{ $summary }}">
        @vite(['resources/css/osif-shop.css', 'resources/js/osif-shop.js'])
    </x-slot:head>

    <main id="main" class="shop-page" x-data="osifShop(@js($shop))" x-on:keydown.escape.window="cartOpen = false; detail = null">
        <header @class(['shop-hero', 'has-cover' => $company->coverUrl()])>
            @if ($company->coverUrl())
                <img class="shop-hero-cover" src="{{ $company->coverUrl() }}" alt="">
            @endif
            <div class="osif-container">
                <nav class="shop-crumbs" aria-label="مسار التصفح">
                    <a href="{{ route('osif') }}">OSIF 2026</a>
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    <a href="{{ route('osif.companies') }}">الشركات المشاركة</a>
                </nav>

                <div class="shop-company">
                    <div class="shop-company-logo">
                        @if ($company->logoUrl())
                            <img src="{{ $company->logoUrl() }}" alt="شعار {{ $company->name }}">
                        @else
                            <span>{{ $company->initials() }}</span>
                        @endif
                    </div>
                    <div class="shop-company-copy">
                        <h1>{{ $company->name }}</h1>
                        <div class="shop-company-meta">
                            @if ($company->booth)
                                <span><i class="bi bi-geo-alt" aria-hidden="true"></i> جناح {{ $company->booth }}</span>
                            @endif
                            @if ($products->isNotEmpty())
                                <span><i class="bi bi-box-seam" aria-hidden="true"></i> {{ Arabic::products($products->count()) }}</span>
                            @endif
                            @if ($offersCount)
                                <span class="is-offer"><i class="bi bi-tag-fill" aria-hidden="true"></i> {{ Arabic::offers($offersCount) }}</span>
                            @endif
                        </div>
                        @if ($company->description)
                            <p>{{ $company->description }}</p>
                        @endif
                    </div>
                    @if ($company->whatsappNumber())
                        <a class="shop-contact" href="https://wa.me/{{ $company->whatsappNumber() }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i> تواصل مع الشركة
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <section class="shop-catalog" aria-labelledby="shop-products-title">
            <div class="osif-container">
                <h2 id="shop-products-title" class="visually-hidden">المنتجات</h2>

                @if ($products->isEmpty())
                    <div class="shop-empty">
                        <i class="bi bi-box-seam" aria-hidden="true"></i>
                        <h3>لم تُضف الشركة منتجاتها بعد</h3>
                        <p>تابع الصفحة لاحقاً، أو تواصل مع الشركة مباشرة على واتساب.</p>
                    </div>
                @else
                    @php
                        $hasSearch = $products->count() > 6;
                        // Worth a filter only when it splits the products: two categories, or one plus the rest.
                        $hasCategories = $categories->count() > 1 || ($categories->isNotEmpty() && $uncategorizedCount);
                    @endphp

                    @if ($offersCount || $hasSearch || $hasCategories)
                    <div class="shop-toolbar">
                        @if ($offersCount)
                            <div class="shop-tabs" role="tablist" aria-label="عرض المنتجات">
                                <button type="button" role="tab" x-bind:aria-selected="filter === 'all'" x-on:click="filter = 'all'">
                                    كل المنتجات <span>{{ $products->count() }}</span>
                                </button>
                                <button type="button" role="tab" x-bind:aria-selected="filter === 'offers'" x-on:click="filter = 'offers'">
                                    <i class="bi bi-tag-fill" aria-hidden="true"></i> العروض <span>{{ $offersCount }}</span>
                                </button>
                            </div>
                        @endif
                        @if ($hasSearch)
                            <label class="shop-search">
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <span class="visually-hidden">ابحث عن منتج</span>
                                <input type="search" x-model="query" placeholder="ابحث عن منتج…" autocomplete="off">
                            </label>
                        @endif
                        @if ($hasCategories)
                            <div class="shop-categories" role="group" aria-label="تصنيفات المنتجات">
                                <button type="button" x-bind:aria-pressed="category === null" x-on:click="category = null">كل التصنيفات</button>
                                @foreach ($categories as $name => $count)
                                    <button type="button" x-bind:aria-pressed="category === @js($name)" x-on:click="category = @js($name)">
                                        {{ $name }} <span>{{ $count }}</span>
                                    </button>
                                @endforeach
                                @if ($uncategorizedCount)
                                    <button type="button" x-bind:aria-pressed="category === ''" x-on:click="category = ''">
                                        أخرى <span>{{ $uncategorizedCount }}</span>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                    @endif

                    <div class="shop-grid">
                        @foreach ($products as $product)
                            <article class="shop-product" x-show="visible({{ $product->id }})" x-bind:class="{ 'is-in-cart': qty({{ $product->id }}) > 0 }">
                                <button type="button" class="shop-product-media" x-on:click="show({{ $product->id }})" aria-label="تفاصيل {{ $product->name }}">
                                    @if ($product->imageUrl())
                                        <img src="{{ $product->imageUrl() }}" alt="" loading="lazy">
                                    @else
                                        <i class="bi bi-box-seam" aria-hidden="true"></i>
                                    @endif
                                    @if ($product->hasDiscount())
                                        <span class="shop-badge">خصم {{ $product->discountPercent() }}٪</span>
                                    @elseif ($product->hasOffer())
                                        <span class="shop-badge">عرض</span>
                                    @endif
                                </button>

                                <div class="shop-product-body">
                                    <h3><button type="button" x-on:click="show({{ $product->id }})">{{ $product->name }}</button></h3>
                                    @if ($product->offer_note)
                                        <p class="shop-offer-note"><i class="bi bi-gift" aria-hidden="true"></i> {{ $product->offer_note }}</p>
                                    @elseif ($product->description)
                                        <p class="shop-product-desc">{{ $product->description }}</p>
                                    @endif

                                    <div class="shop-price">
                                        <strong @class(['is-request' => $product->currentPrice() === null])>{{ CompanyProduct::formatPrice($product->currentPrice()) }}</strong>
                                        @if ($product->hasDiscount())
                                            <del>{{ CompanyProduct::formatPrice($product->price) }}</del>
                                        @endif
                                    </div>

                                    <div class="shop-buy">
                                        <button type="button" class="shop-add" x-show="qty({{ $product->id }}) === 0" x-on:click="add({{ $product->id }})">
                                            <i class="bi bi-cart-plus" aria-hidden="true"></i> أضف إلى السلة
                                        </button>
                                        <div class="shop-stepper" x-show="qty({{ $product->id }}) > 0" x-cloak>
                                            <button type="button" x-on:click="add({{ $product->id }})" aria-label="زيادة الكمية"><i class="bi bi-plus-lg"></i></button>
                                            <output x-text="qty({{ $product->id }})" aria-live="polite"></output>
                                            <button type="button" x-on:click="remove({{ $product->id }})" x-bind:aria-label="qty({{ $product->id }}) === 1 ? 'إزالة من السلة' : 'إنقاص الكمية'">
                                                <i class="bi" x-bind:class="qty({{ $product->id }}) === 1 ? 'bi-trash3' : 'bi-dash-lg'"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="shop-empty is-filtered" x-show="visibleCount === 0" x-cloak>
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <h3>لا توجد منتجات مطابقة</h3>
                        <button type="button" class="shop-link" x-on:click="query = ''; filter = 'all'; category = null">عرض كل المنتجات</button>
                    </div>
                @endif
            </div>
        </section>

        {{-- Always-visible cart bar once something is in the cart. --}}
        <div class="shop-cartbar" x-show="count > 0 && ! cartOpen" x-transition.opacity x-cloak>
            <button type="button" x-on:click="openCart()">
                <span class="shop-cartbar-icon"><i class="bi bi-cart3" aria-hidden="true"></i><b x-text="count"></b></span>
                <span class="shop-cartbar-label">عرض السلة وإرسال الطلب</span>
                <span class="shop-cartbar-total" x-text="total > 0 ? money(total) : ''"></span>
            </button>
        </div>

        <div class="shop-toast" x-show="toast" x-transition.opacity x-cloak role="status">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i> <span x-text="toast"></span>
            <button type="button" x-on:click="openCart()">عرض السلة</button>
        </div>

        {{-- Product details --}}
        <div class="shop-overlay" x-show="detailProduct" x-transition.opacity x-cloak x-on:click.self="detail = null">
            <template x-if="detailProduct">
                <div class="shop-dialog" role="dialog" aria-modal="true" aria-labelledby="shop-detail-title" x-trap.noscroll="detailProduct">
                    <button type="button" class="shop-close" x-on:click="detail = null" aria-label="إغلاق"><i class="bi bi-x-lg"></i></button>
                    <div class="shop-dialog-media">
                        <template x-if="detailProduct.image"><img x-bind:src="detailProduct.image" alt=""></template>
                        <template x-if="! detailProduct.image"><i class="bi bi-box-seam" aria-hidden="true"></i></template>
                    </div>
                    <div class="shop-dialog-body">
                        <h2 id="shop-detail-title" x-text="detailProduct.name"></h2>
                        <p class="shop-offer-note" x-show="detailProduct.offerNote"><i class="bi bi-gift" aria-hidden="true"></i> <span x-text="detailProduct.offerNote"></span></p>
                        <p class="shop-dialog-desc" x-show="detailProduct.description" x-text="detailProduct.description"></p>
                        <div class="shop-price">
                            <strong x-text="priceLabel(detailProduct.price)" x-bind:class="{ 'is-request': detailProduct.price === null }"></strong>
                            <del x-show="detailProduct.original" x-text="detailProduct.original ? money(detailProduct.original) : ''"></del>
                            <span class="shop-badge is-inline" x-show="detailProduct.discount" x-text="'خصم ' + detailProduct.discount + '٪'"></span>
                        </div>
                        <div class="shop-buy">
                            <button type="button" class="shop-add" x-show="qty(detail) === 0" x-on:click="add(detail)">
                                <i class="bi bi-cart-plus" aria-hidden="true"></i> أضف إلى السلة
                            </button>
                            <div class="shop-stepper is-large" x-show="qty(detail) > 0">
                                <button type="button" x-on:click="add(detail)" aria-label="زيادة الكمية"><i class="bi bi-plus-lg"></i></button>
                                <output x-text="qty(detail)"></output>
                                <button type="button" x-on:click="remove(detail)" aria-label="إنقاص الكمية"><i class="bi" x-bind:class="qty(detail) === 1 ? 'bi-trash3' : 'bi-dash-lg'"></i></button>
                            </div>
                        </div>
                        <button type="button" class="shop-link" x-show="qty(detail) > 0" x-on:click="openCart()">الانتقال إلى السلة <i class="bi bi-arrow-left"></i></button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Cart and checkout --}}
        <div class="shop-overlay is-drawer" x-show="cartOpen" x-transition.opacity x-cloak x-on:click.self="cartOpen = false">
            <aside class="shop-cart" role="dialog" aria-modal="true" aria-labelledby="shop-cart-title" x-show="cartOpen" x-transition:enter-start="is-closed" x-transition:leave-end="is-closed" x-trap.noscroll="cartOpen">
                <header class="shop-cart-head">
                    <div>
                        <h2 id="shop-cart-title">سلة المشتريات</h2>
                        <p>من {{ $company->name }}</p>
                    </div>
                    <button type="button" class="shop-close" x-on:click="cartOpen = false" aria-label="إغلاق السلة"><i class="bi bi-x-lg"></i></button>
                </header>

                <div class="shop-cart-body">
                    <div class="shop-empty is-cart" x-show="count === 0 && ! sent">
                        <i class="bi bi-cart3" aria-hidden="true"></i>
                        <h3>سلتك فارغة</h3>
                        <p>اضغط «أضف إلى السلة» على أي منتج.</p>
                        <button type="button" class="shop-link" x-on:click="cartOpen = false">تصفّح المنتجات</button>
                    </div>

                    {{-- After WhatsApp opens: the order still has to be sent from there. --}}
                    <div class="shop-sent" x-show="sent">
                        <i class="bi bi-whatsapp" aria-hidden="true"></i>
                        <h3>طلبك جاهز في واتساب</h3>
                        <p>اضغط زر <strong>«إرسال»</strong> داخل واتساب لتصل رسالة الطلب إلى {{ $company->name }}، وستتواصل معك الشركة لتأكيده.</p>
                        <a class="shop-whatsapp is-ghost" x-bind:href="whatsappUrl" target="_blank" rel="noopener">لم يُفتح واتساب؟ افتحه مرة أخرى</a>
                        <button type="button" class="shop-link" x-on:click="clear(); cartOpen = false">إفراغ السلة والعودة للمنتجات</button>
                    </div>

                    <ul class="shop-lines" x-show="count > 0 && ! sent">
                        <template x-for="line in lines" x-bind:key="line.id">
                            <li class="shop-line">
                                <div class="shop-line-media">
                                    <template x-if="line.image"><img x-bind:src="line.image" alt=""></template>
                                    <template x-if="! line.image"><i class="bi bi-box-seam" aria-hidden="true"></i></template>
                                </div>
                                <div class="shop-line-info">
                                    <strong x-text="line.name"></strong>
                                    <span x-text="priceLabel(line.price)"></span>
                                </div>
                                <div class="shop-stepper is-small">
                                    <button type="button" x-on:click="add(line.id)" aria-label="زيادة الكمية"><i class="bi bi-plus-lg"></i></button>
                                    <output x-text="line.qty"></output>
                                    <button type="button" x-on:click="remove(line.id)" x-bind:aria-label="line.qty === 1 ? 'إزالة من السلة' : 'إنقاص الكمية'"><i class="bi" x-bind:class="line.qty === 1 ? 'bi-trash3' : 'bi-dash-lg'"></i></button>
                                </div>
                            </li>
                        </template>
                    </ul>

                    <div class="shop-checkout-fields" x-show="count > 0 && ! sent">
                        <label class="shop-field">
                            <span>اسمك <small>(اختياري)</small></span>
                            <input type="text" x-model="buyer" maxlength="80" autocomplete="name" placeholder="مثال: د. علي حسين">
                        </label>
                        <label class="shop-field">
                            <span>ملاحظات للشركة <small>(اختياري)</small></span>
                            <textarea x-model="note" rows="2" maxlength="300" placeholder="مثال: التوصيل إلى العيادة، أو الاستلام من الجناح"></textarea>
                        </label>
                    </div>
                </div>

                <footer class="shop-cart-foot" x-show="count > 0 && ! sent">
                    <div class="shop-total">
                        <span>المجموع <small x-text="'· عدد القطع: ' + count"></small></span>
                        <strong x-text="total > 0 ? money(total) : 'يُحدَّد من الشركة'"></strong>
                    </div>
                    <p class="shop-total-note" x-show="hasUnpriced">بعض الأصناف سعرها عند الطلب، وستؤكده الشركة معك.</p>
                    @if ($company->whatsappNumber())
                        <a class="shop-whatsapp" x-bind:href="whatsappUrl" target="_blank" rel="noopener" x-on:click="sent = true">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i> إرسال الطلب عبر واتساب
                        </a>
                        <p class="shop-total-note">يُفتح واتساب برسالة جاهزة تحتوي طلبك؛ لا يُدفع أي مبلغ الآن.</p>
                    @else
                        <p class="shop-total-note">لا يتوفر رقم واتساب لهذه الشركة حالياً.</p>
                    @endif
                </footer>
            </aside>
        </div>
    </main>

    <footer class="osif-footer">
        <div class="osif-container">
            <div class="osif-footer-wordmark"><strong>OSIF</strong> KARBALA <span>2026</span></div>
            <p>ملتقى كربلاء لجراحة الفم وزراعة الأسنان</p>
            <div>
                <a href="{{ route('osif.companies') }}">كل الشركات المشاركة <i class="bi bi-arrow-up-left"></i></a>
                <a href="{{ route('osif') }}">صفحة الملتقى <i class="bi bi-arrow-up-left"></i></a>
            </div>
        </div>
    </footer>
</x-layouts.osif>
