@props(['heading' => true])

<section class="section bg-light-2" id="marketplace-add">
    <div class="container">
        @if ($heading)
            <div class="text-center mb-5 reveal">
                <span class="eyebrow">سوق النقابة</span>
                <h2 class="section-title">أضف إعلانك</h2>
                <p class="section-subtitle">اعرض ما ترغب ببيعه أو اطلب ما تحتاج شراءه، وسيُنشر بعد مراجعة النقابة.</p>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-9 reveal">
                <div class="card listing-form-card">
                    @if (session('marketplace_ok'))
                        <div class="alert alert-success" role="status">
                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                            تم استلام إعلانك، وسيظهر في السوق بعد مراجعته من قبل النقابة.
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                            يرجى تصحيح الحقول المحدّدة أدناه.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('marketplace.store') }}" enctype="multipart/form-data" novalidate>
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <span class="form-label">نوع الإعلان</span>
                                <div class="listing-type-choice">
                                    @foreach (\App\Models\MarketplaceListing::types() as $value => $label)
                                        <label class="listing-type-option">
                                            <input type="radio" name="type" value="{{ $value }}"
                                                   @checked(old('type', \App\Models\MarketplaceListing::TYPE_SALE) === $value)>
                                            <span>
                                                <i class="bi {{ $value === \App\Models\MarketplaceListing::TYPE_SALE ? 'bi-tag' : 'bi-search' }}" aria-hidden="true"></i>
                                                {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-8">
                                <label for="lTitle" class="form-label">عنوان الإعلان</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="lTitle" name="title" value="{{ old('title') }}"
                                       placeholder="مثال: جهاز أشعة بانوراما مستعمل" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="lCategory" class="form-label">التصنيف</label>
                                <select class="form-select @error('category') is-invalid @enderror" id="lCategory" name="category">
                                    <option value="">— اختر —</option>
                                    @foreach (\App\Models\MarketplaceListing::categories() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="lDescription" class="form-label">وصف الإعلان</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="lDescription" name="description" rows="5"
                                          placeholder="اذكر الحالة والمواصفات وأي تفاصيل مهمة..." required>{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="lPrice" class="form-label">السعر بالدينار <span class="text-muted-2">(اختياري)</span></label>
                                <input type="number" min="0" step="1" class="form-control @error('price') is-invalid @enderror"
                                       id="lPrice" name="price" value="{{ old('price') }}" placeholder="500000" dir="ltr">
                                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="lName" class="form-label">اسم صاحب الإعلان</label>
                                <input type="text" class="form-control @error('contact_name') is-invalid @enderror"
                                       id="lName" name="contact_name" value="{{ old('contact_name') }}" required>
                                @error('contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="lPhone" class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control @error('contact_phone') is-invalid @enderror"
                                       id="lPhone" name="contact_phone" value="{{ old('contact_phone') }}"
                                       placeholder="07XX XXX XXXX" dir="ltr" required>
                                @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="lCity" class="form-label">المدينة / المنطقة <span class="text-muted-2">(اختياري)</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                       id="lCity" name="city" value="{{ old('city') }}" placeholder="كربلاء المقدسة">
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="lImage" class="form-label">صورة <span class="text-muted-2">(اختياري)</span></label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                       id="lImage" name="image" accept="image/jpeg,image/png,image/webp">
                                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <div class="listing-form-foot">
                                    <span class="listing-form-hint">
                                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                                        تُراجع الإعلانات قبل نشرها، والنقابة غير مسؤولة عن الاتفاقات بين الطرفين.
                                    </span>
                                    <button type="submit" class="btn btn-gov btn-lg">
                                        <i class="bi bi-plus-circle" aria-hidden="true"></i> نشر الإعلان
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
