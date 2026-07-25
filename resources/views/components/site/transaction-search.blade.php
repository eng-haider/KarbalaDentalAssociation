@props(['heading' => true])
<section class="section" id="transaction-search">
    <div class="container">
        @if ($heading)
        <div class="text-center mb-5 reveal">
            <span class="eyebrow">الخدمات الإلكترونية</span>
            <h2 class="section-title">البحث عن معاملة</h2>
            <p class="section-subtitle">تحقّق من حالة معاملتك لدى النقابة بكتابة اسمك في حقل البحث.</p>
        </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card trx-card reveal">
                    <div class="trx-field">
                        <i class="bi bi-search trx-field-icon" aria-hidden="true"></i>
                        <label for="trxInput" class="visually-hidden">ابحث باسمك</label>
                        <input type="search" class="trx-input" id="trxInput" autocomplete="off"
                               placeholder="قم بالبحث عن معاملتك يرجى كتابه الاسم الثلاثي..">
                        <button type="button" class="trx-clear" id="trxClear" aria-label="مسح البحث" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="trx-state" id="trxState" role="status" aria-live="polite"></div>
                    <div class="trx-results" id="trxResults"></div>
                </div>

                <div class="trx-stats reveal" id="trxStats" hidden>
                    <div class="trx-stat">
                        <span class="trx-stat-icon trx-i-all"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
                        <strong data-trx-stat="total">—</strong>
                        <small>المعاملات المنجزة</small>
                    </div>
                    <div class="trx-stat">
                        <span class="trx-stat-icon trx-i-clinic"><i class="bi bi-hospital" aria-hidden="true"></i></span>
                        <strong data-trx-stat="clinic">—</strong>
                        <small>تجديد مع عيادة</small>
                    </div>
                    <div class="trx-stat">
                        <span class="trx-stat-icon trx-i-noclinic"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                        <strong data-trx-stat="noclinic">—</strong>
                        <small>تجديد بدون عيادة</small>
                    </div>
                    <div class="trx-stat">
                        <span class="trx-stat-icon trx-i-join"><i class="bi bi-person-plus" aria-hidden="true"></i></span>
                        <strong data-trx-stat="join">—</strong>
                        <small>طلبات انتماء</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="trxModal" tabindex="-1" aria-labelledby="trxModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trxModalLabel">تفاصيل المعاملة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="trx-detail">
                        <div class="detail-field">
                            <label>الاسم</label>
                            <p id="trxDetailName"></p>
                        </div>
                        <div class="detail-field">
                            <label>نوع المعاملة</label>
                            <p id="trxDetailType"></p>
                        </div>
                        <div class="detail-field">
                            <label>الحالة</label>
                            <p><span class="badge bg-success"><i class="bi bi-patch-check-fill"></i> منجزة</span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
</section>
