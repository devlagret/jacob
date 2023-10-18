@section('scripts')
<script>
const form = document.getElementById('kt_savings_edit_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'savings_code': {
                validators: {
                    notEmpty: {
                        message: 'Kode Tabungan harus diisi'
                    }
                }
            },
            'savings_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Tabungan harus diisi'
                    }
                }
            },
            'account_id': {
                validators: {
                    notEmpty: {
                        message: 'Nomor Perkiraan harus diisi'
                    }
                }
            },
            'savings_profit_sharing': {
                validators: {
                    notEmpty: {
                        message: 'Status Tabungan harus diisi'
                    }
                }
            },
            'savings_interest_rate': {
                validators: {
                    notEmpty: {
                        message: 'Bunga harus diisi'
                    }
                }
            },
            'account_basil_id': {
                validators: {
                    notEmpty: {
                        message: 'Nomor Perkiraan Bunga Tabungan harus diisi'
                    }
                }
            },
            'min_saving': {
                validators: {
                    notEmpty: {
                        message: 'Minimal Setoran Tabungan harus diisi'
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


$(form.querySelector('[name="savings_profit_sharing"]')).on('change', function () {
    validator.revalidateField('savings_profit_sharing');
});

$(form.querySelector('[name="account_id"]')).on('change', function () {
    validator.revalidateField('account_id');
});

$(form.querySelector('[name="account_basil_id"]')).on('change', function () {
    validator.revalidateField('account_basil_id');
});

const submitButton = document.getElementById('kt_savings_edit_submit');
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

function check_all(){
    $(':checkbox').each(function() {
        this.checked = true;                        
    });
}
function uncheck_all(){
    $(':checkbox').each(function() {
        this.checked = false;                        
    });
}
$(document).ready(function(){
$("#min_saving_view").change(function(){
        var min_saving                                    = $("#min_saving_view").val();
        document.getElementById("min_saving").value       = min_saving;
        document.getElementById("min_saving_view").value  = toRp(min_saving);
    });

});
</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Edit Kode Tabungan') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('savings.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_savings_edit_view">
            <form id="kt_savings_edit_view_form" class="form" method="POST" action="{{ route('savings.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kode Tabungan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="hidden" name="savings_id" class="form-control form-control-lg form-control-solid" placeholder="Kode Tabungan" value="{{ old('savings_id', $savings->savings_id ?? '') }}" autocomplete="off"/>
                            <input type="text" name="savings_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Tabungan" value="{{ old('savings_code', $savings->savings_code ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Tabungan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="savings_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Tabungan" value="{{ old('savings_name', $savings->savings_name ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nomor Perkiraan Tabungan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="account_id" id="account_id" aria-label="{{ __('Nomor Perkiraan Tabungan') }}" data-control="select2" data-placeholder="{{ __('Pilih nomor perkiraan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih nomor perkiraan..') }}</option>
                                @foreach($acctaccount as $key => $value)
                                    <option data-kt-flag="{{ $value['account_id'] }}" value="{{ $value['account_id'] }}" {{ $value['account_id'] === old('account_id', $savings->account_id ?? '') ? 'selected' :'' }}>{{ $value['full_account'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Status Tabungan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="savings_profit_sharing" id="savings_profit_sharing" aria-label="{{ __('Pilih Status Tabungan') }}" data-control="select2" data-placeholder="{{ __('Pilih status tabungan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih status tabungan..') }}</option>
                                @foreach($savingsprofitsharing as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('savings_profit_sharing', (int)$savings->savings_profit_sharing ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nomor Perkiraan Bunga Tabungan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="account_basil_id" id="account_basil_id" aria-label="{{ __('Nomor Perkiraan Bunga Tabungan') }}" data-control="select2" data-placeholder="{{ __('Pilih nomor perkiraan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih nomor perkiraan..') }}</option>
                                @foreach($acctaccount as $key => $value)
                                    <option data-kt-flag="{{ $value['account_id'] }}" value="{{ $value['account_id'] }}" {{ $value['account_id'] === old('account_basil_id', $savings->account_basil_id ?? '') ? 'selected' :'' }}>{{ $value['full_account'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Bunga Tabungaan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="savings_interest_rate" class="form-control form-control-lg form-control-solid" placeholder="%" value="{{ old('savings_interest_rate', $savings->savings_interest_rate ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Minimal Setoran Awal') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="min_saving_view" id="min_saving_view" class="form-control form-control-lg form-control-solid" placeholder="Minimal Setoran Awal" value="{{ old('min_saving_view', number_format($savings->minimum_first_deposit_amount, 2) ?? '') }}" autocomplete="off"/>
                            <input type="hidden" name="min_saving" id="min_saving" class="form-control form-control-lg form-control-solid" placeholder="Minimal Setoran Awal" value="{{ old('min_saving', $savings->minimum_first_deposit_amount ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_savings_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

