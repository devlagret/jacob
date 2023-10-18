
@section('scripts')
<script>
const form = document.getElementById('kt_deposito_edit_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'deposito_code': {
                validators: {
                    notEmpty: {
                        message: 'Kode Deposito harus diisi'
                    }
                }
            },
            'deposito_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama harus diisi'
                    }
                }
            },
            'account_id': {
                validators: {
                    notEmpty: {
                        message: 'No. Perkiraan harus diisi'
                    }
                }
            },
            'account_basil_id': {
                validators: {
                    notEmpty: {
                        message: 'Basil harus diisi'
                    }
                }
            },
            'deposito_period': {
                validators: {
                    notEmpty: {
                        message: 'Jangka Waktu harus diisi'
                    }
                }
            },
            'deposito_interest_rate': {
                validators: {
                    notEmpty: {
                        message: 'Bunga harus diisi'
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

const submitButton = document.getElementById('kt_deposito_edit_submit');
submitButton.addEventListener('click', function (e) {
    e.preventDefault();

    if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                submitButton.setAttribute('data-kt-indicator', 'on');

                submitButton.disabled = true;

                setTimeout(function () {
                    submitButton.removeAttribute('data-kt-indicator');

                    // submitButton.disabled = false;

                    // Swal.fire({
                    //     text: "Form has been successfully submitted!",
                    //     icon: "success",
                    //     buttonsStyling: false,
                    //     confirmButtonText: "Ok, got it!",
                    //     customClass: {
                    //         confirmButton: "btn btn-primary"
                    //     }
                    // });

                    form.submit(); // Submit form
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
                <h3 class="fw-bolder m-0">{{ __('Form Ubah Kode Deposito') }}</h3>
            </div>

            <a href="{{ route('deposito.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_deposito_edit_view_form" class="form" method="POST" action="{{ route('deposito.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kode Deposito') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="hidden" name="deposito_id" class="form-control form-control-lg form-control-solid" value="{{ old('deposito_id', $deposito->deposito_id ?? '') }}"/>
                            <input type="text" name="deposito_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Deposito" value="{{ old('deposito_code', $deposito->deposito_code ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="deposito_name" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('deposito_name', $deposito->deposito_name ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Perkiraan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="account_id" id="account_id" aria-label="{{ __('Pilih') }}" data-control="select2" data-placeholder="{{ __('Pilih No. Perkiraan') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($acctacount as $key => $value)
                                    <option data-kt-flag="{{ $value->account_id }}" value="{{ $value->account_id }}" {{ $value->account_id === old('account_id', $deposito->account_id ?? '') ? 'selected' :'' }}>{{ $value['account_code'] }} - {{ $value['account_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Basil') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="account_basil_id" id="account_basil_id" aria-label="{{ __('Pilih') }}" data-control="select2" data-placeholder="{{ __('Pilih Basil') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($acctacount as $key => $value)
                                    <option data-kt-flag="{{ $value->account_id }}" value="{{ $value->account_id }}" {{ $value->account_id === old('account_basil_id', $deposito->account_basil_id ?? '') ? 'selected' :'' }}>{{ $value['account_code'] }} - {{ $value['account_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jangka Waktu') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="deposito_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka Waktu" value="{{ old('deposito_period', $deposito->deposito_period ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Bunga / Th') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="deposito_interest_rate" class="form-control form-control-lg form-control-solid" placeholder="Pertahun" value="{{ old('deposito_interest_rate', $deposito->deposito_interest_rate ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_deposito_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

