
@section('scripts')
<script>
const form = document.getElementById('kt_bank_account_edit_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'bank_account_code': {
                validators: {
                    notEmpty: {
                        message: 'Kode Bank harus diisi'
                    }
                }
            },
            'bank_account_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Bank harus diisi'
                    }
                }
            },
            'bank_account_no': {
                validators: {
                    notEmpty: {
                        message: 'No. Rekening harus diisi'
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

const submitButton = document.getElementById('kt_bank_account_edit_submit');
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
                <h3 class="fw-bolder m-0">{{ __('Form Ubah Kode Bank') }}</h3>
            </div>

            <a href="{{ route('bank-account.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_bank_account_edit_view_form" class="form" method="POST" action="{{ route('bank-account.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kode Bank') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="hidden" name="bank_account_id" class="form-control form-control-lg form-control-solid" value="{{ old('bank_account_id', $bankaccount->bank_account_id ?? '') }}"/>
                            <input type="text" name="bank_account_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Bank" value="{{ old('bank_account_code', $bankaccount->bank_account_code ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Bank') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="bank_account_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Bank" value="{{ old('bank_account_name', $bankaccount->bank_account_name ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Rekening') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="bank_account_no" class="form-control form-control-lg form-control-solid" placeholder="No. Rekening" value="{{ old('bank_account_no', $bankaccount->bank_account_no ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Perkiraan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="account_id" id="account_id" aria-label="{{ __('Pilih') }}" data-control="select2" data-placeholder="{{ __('Pilih No. Perkiraan') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($acctacount as $key => $value)
                                    <option data-kt-flag="{{ $value->account_id }}" value="{{ $value->account_id }}" {{ $value->account_id === old('account_id', $bankaccount->account_id ?? '') ? 'selected' :'' }}>{{ $value['account_code'] }} - {{ $value['account_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_bank_account_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

