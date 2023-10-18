@php
    $chartColor = $chartColor ?? 'primary';
    $chartHeight = $chartHeight ?? '175px';
@endphp

<!--begin::Mixed Widget 2-->
<div class="card {{ $class }}">
    <!--begin::Header-->
    <div class="card-header border-0 bg-{{ $chartColor }} py-5">
        <h3 class="card-title fw-bolder text-white">Rugi Laba</h3>
    </div>
    <!--end::Header-->

    <!--begin::Body-->
    <div class="card-body p-0">
        <!--begin::Chart-->
        <div class="mixed-widget-2-chart card-rounded-bottom bg-{{ $chartColor }}" data-kt-color="{{ $chartColor }}" data-kt-chart-url="{{ route('profits') }}" style="height: {{ $chartHeight }}"></div>
        <!--end::Chart-->

        <!--begin::Stats-->
        <div class="card-p mt-n20 position-relative">
            <!--begin::Row-->
            <div class="row g-0">
                <!--begin::Col-->
                <a href="{{ route('savings-account.index') }}" id="dataaa" class="col border-muted bg-light-warning px-6 py-8 rounded-2 me-7 mb-7">
                    {!! theme()->getSvgIcon("icons/duotune/finance/fin008.svg", "svg-icon-3x svg-icon-warning d-block my-2") !!}
                    <div class="text-warning fw-bold fs-6">
                        Tabungan
                    </div>
                </a>
                <!--end::Col-->

                <!--begin::Col-->
                <a href="{{ route('deposito-account.index') }}" class="col border-muted bg-light-primary px-6 py-8 rounded-2 mb-7">
                    {!! theme()->getSvgIcon("icons/duotune/finance/fin005.svg", "svg-icon-3x svg-icon-primary d-block my-2") !!}
                    <div class="text-primary fw-bold fs-6">
                        Simp Berjangka
                    </div>
                </a>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row g-0">
                <!--begin::Col-->
                <a href="{{ route('credits-account.index') }}" class="col border-muted bg-light-danger px-6 py-8 rounded-2 me-7">
                    {!! theme()->getSvgIcon("icons/duotune/finance/fin010.svg", "svg-icon-3x svg-icon-danger d-block my-2") !!}
                    <div class="text-danger fw-bold fs-6 mt-2">
                        Pinjaman
                    </div>
                </a>
                <!--end::Col-->

                <!--begin::Col-->
                <a href="{{ route('credits-payment-cash.index') }}" class="col border-muted bg-light-success px-6 py-8 rounded-2">
                    {!! theme()->getSvgIcon("icons/duotune/finance/fin004.svg", "svg-icon-3x svg-icon-success d-block my-2") !!}
                    <div class="text-success fw-bold fs-6 mt-2">
                        Angsuran
                    </div>
                </a>
                <!--end::Col-->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Stats-->
    </div>
    <!--end::Body-->
</div>
<!--end::Mixed Widget 2-->
