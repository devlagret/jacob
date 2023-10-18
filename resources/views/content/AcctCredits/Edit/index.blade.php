@section('scripts')
<script>
const form = document.getElementById('kt_credits_edit_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'credits_code': {
                validators: {
                    notEmpty: {
                        message: 'Kode Pinjaman harus diisi'
                    }
                }
            },
            'credits_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Pinjaman harus diisi'
                    }
                }
            },
            'receivable_account_id': {
                validators: {
                    notEmpty: {
                        message: 'Nomor Perkiraan harus diisi'
                    }
                }
            },
            'income_account_id': {
                validators: {
                    notEmpty: {
                        message: 'Nomor Perkiraan Bunga harus diisi'
                    }
                }
            },
            'credits_fine': {
                validators: {
                    notEmpty: {
                        message: 'Presentase Denda harus diisi'
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


$(form.querySelector('[name="income_account_id"]')).on('change', function () {
    validator.revalidateField('income_account_id');
});

$(form.querySelector('[name="receivable_account_id"]')).on('change', function () {
    validator.revalidateField('receivable_account_id');
});

const submitButton = document.getElementById('kt_credits_edit_submit');
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
</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Kode Pinjaman') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('credits.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_credits_edit_view">
            <form id="kt_credits_edit_view_form" class="form" method="POST" action="{{ route('credits.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kode Pinjaman') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="hidden" name="credits_id" class="form-control form-control-lg form-control-solid" placeholder="ID Pinjaman" value="{{ old('credits_id', $credits->credits_id ?? '') }}" autocomplete="off"/>
                            <input type="text" name="credits_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Pinjaman" value="{{ old('credits_code', $credits->credits_code ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Pinjaman') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="credits_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Pinjaman" value="{{ old('credits_name', $credits->credits_name ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nomor Perkiraan Pinjaman') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="receivable_account_id" id="receivable_account_id" aria-label="{{ __('Nomor Perkiraan Pinjaman') }}" data-control="select2" data-placeholder="{{ __('Pilih nomor perkiraan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih nomor perkiraan..') }}</option>
                                @foreach($acctaccount as $key => $value)
                                    <option data-kt-flag="{{ $value['account_id'] }}" value="{{ $value['account_id'] }}" {{ $value['account_id'] === old('receivable_account_id', $credits->receivable_account_id ?? '') ? 'selected' :'' }}>{{ $value['full_account'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nomor Perkiraan Bunga') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="income_account_id" id="income_account_id" aria-label="{{ __('Nomor Perkiraan Bunga Pinjaman') }}" data-control="select2" data-placeholder="{{ __('Pilih nomor perkiraan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih nomor perkiraan..') }}</option>
                                @foreach($acctaccount as $key => $value)
                                    <option data-kt-flag="{{ $value['account_id'] }}" value="{{ $value['account_id'] }}" {{ $value['account_id'] === old('income_account_id', $credits->income_account_id ?? '') ? 'selected' :'' }}>{{ $value['full_account'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Presentase Denda') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="credits_fine" class="form-control form-control-lg form-control-solid" placeholder="%" value="{{ old('credits_fine', $credits->credits_fine ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_credits_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>