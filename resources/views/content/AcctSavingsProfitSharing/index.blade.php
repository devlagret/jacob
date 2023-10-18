@section('scripts')
<script>

const form = document.getElementById('kt_savings_profit_sharing_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'month_period': {
                validators: {
                    notEmpty: {
                        message: 'Bulan harus diisi'
                    }
                }
            },
            'year_period': {
                validators: {
                    notEmpty: {
                        message: 'Tahun harus diisi'
                    }
                }
            },
            'savings_account_minimum': {
                validators: {
                    notEmpty: {
                        message: 'Saldo Minimum harus diisi'
                    }
                }
            },
        },

        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap: new FormValidation.plugins.Bootstrap5({
                rowSelector: '.fv-row',
                eleInvalidClass: '',
                eleValidClass: ''
            })
        }
    }
);

$(form.querySelector('[name="month_period"]')).on('change', function () {
    validator.revalidateField('month_period');
});

$(form.querySelector('[name="year_period"]')).on('change', function () {
    validator.revalidateField('year_period');
});

const submitButton = document.getElementById('kt_savings_profit_sharing_submit');
submitButton.addEventListener('click', function (e) {
    e.preventDefault();

    if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                submitButton.setAttribute('data-kt-indicator', 'on');

                submitButton.disabled = true;

                setTimeout(function () {
                    submitButton.removeAttribute('data-kt-indicator');

                    form.submit();
                }, 2000);
            }
        });
    }
});
</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Perhitungan Bunga Tabungan') }}</h3>
            </div>
        </div>

        <div id="kt_savings_profit_sharing_view">
            <form id="kt_savings_profit_sharing_view_form" class="form" method="POST" action="{{ route('savings-profit-sharing.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Bulan') }}</label>
                            <input type="text" name="month_period_name" class="form-control form-control-lg form-control-solid" placeholder="Bulan" value="{{ old('month_period_name', $month[$month_period] ?? '') }}" autocomplete="off" readonly/>
                            <input type="hidden" name="month_period" class="form-control form-control-lg form-control-solid" placeholder="Bulan" value="{{ old('month_period', $month_period ?? '') }}" autocomplete="off" readonly/>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tahun') }}</label>
                            <select name="year_period" id="year_period" aria-label="{{ __('Tahun') }}" data-control="select2" data-placeholder="{{ __('Pilih tahun..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih tahun..') }}</option>
                                @foreach($year as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('year_period', (int)$year_period ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Saldo Minimal') }}</label>
                            <input type="text" name="savings_account_minimum" class="form-control form-control-lg form-control-solid" placeholder="Saldo Minimal" value="{{ old('savings_account_minimum', 0 ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('savings-profit-sharing.list-data') }}" class="btn btn-warning me-2" id="kt_savings_profit_sharing_list" name="kt_savings_profit_sharing_list">
                        <i class="bi bi-border-width"></i> {{__('Daftar Bunga')}}
                    </a>
                    <button type="submit" class="btn btn-primary" id="kt_savings_profit_sharing_submit" name="kt_savings_profit_sharing_submit" >
                        <i class="bi bi-check fs-2x"></i> @include('partials.general._button-indicator', ['label' => __('Proses')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>