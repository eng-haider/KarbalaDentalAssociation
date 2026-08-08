@props(['analytics', 'heading' => true])

@php
    $slices = $analytics['slices'];
    $total = max($analytics['total'], 1);

    // Donut geometry: r=80 on a 200x200 viewBox, drawn as dashes on one circle.
    $circumference = 2 * M_PI * 80;
    $gap = 2.5;
    $offset = 0;
@endphp

<section class="section bg-light-2" id="clinic-analytics">
    <div class="container">
        @if ($heading)
            <div class="text-center mb-5 reveal">
                <span class="eyebrow">النقابة بالأرقام</span>
                <h2 class="section-title">التحليل الإحصائي للعيادات</h2>
                <p class="section-subtitle">توزيع معاملات العيادات المسجّلة لدى النقابة حسب نوع المعاملة.</p>
            </div>
        @endif

        <div class="card analytics-card reveal">
            <div class="analytics-head">
                <h3 class="analytics-title">توزيع المعاملات حسب النوع</h3>
                <span class="analytics-head-icon"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span>
            </div>

            <div class="analytics-body">
                <div class="analytics-chart">
                    <svg viewBox="0 0 200 200" role="img"
                         aria-label="مخطط دائري لتوزيع {{ number_format($analytics['total']) }} معاملة على {{ count($slices) }} فئات، والتفصيل مذكور في القائمة المجاورة.">
                        <circle cx="100" cy="100" r="80" fill="none" stroke="#EEF2F7" stroke-width="26" />
                        @foreach ($slices as $slice)
                            @php
                                $length = $slice['count'] / $total * $circumference;
                                $dash = max($length - $gap, 1);
                            @endphp
                            <circle cx="100" cy="100" r="80" fill="none"
                                    stroke="{{ $slice['color'] }}" stroke-width="26"
                                    stroke-dasharray="{{ round($dash, 2) }} {{ round($circumference - $dash, 2) }}"
                                    stroke-dashoffset="{{ round(-$offset, 2) }}"
                                    transform="rotate(-90 100 100)" />
                            @php($offset += $length)
                        @endforeach
                    </svg>
                    <div class="analytics-chart-center">
                        <strong>{{ number_format($analytics['total']) }}</strong>
                        <small>إجمالي المعاملات</small>
                    </div>
                </div>

                <ul class="analytics-legend">
                    @foreach ($slices as $slice)
                        <li>
                            <span class="analytics-dot" style="background:{{ $slice['color'] }}"></span>
                            <span class="analytics-legend-label">{{ $slice['label'] }}</span>
                            <span class="analytics-legend-pct">{{ $slice['percent'] }}%</span>
                            <span class="analytics-legend-count">{{ number_format($slice['count']) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="analytics-totals">
                <div class="analytics-total">
                    <span class="analytics-total-icon analytics-i-doctors"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                    <div>
                        <strong>{{ number_format($analytics['doctors']) }}</strong>
                        <small>الأطباء المشمولون</small>
                    </div>
                </div>
                <div class="analytics-total">
                    <span class="analytics-total-icon analytics-i-done"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
                    <div>
                        <strong>{{ number_format($analytics['completed']) }}</strong>
                        <small>معاملات منجزة</small>
                    </div>
                </div>
                <div class="analytics-total">
                    <span class="analytics-total-icon analytics-i-pending"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
                    <div>
                        <strong>{{ number_format($analytics['pending']) }}</strong>
                        <small>قيد الإنجاز</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
