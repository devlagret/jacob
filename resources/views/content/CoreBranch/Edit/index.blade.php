
@section('scripts')
<script>
const form = document.getElementById('kt_core_branch_edit_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'branch_code': {
                validators: {
                    notEmpty: {
                        message: 'Kode Cabang harus diisi'
                    }
                }
            },
            'branch_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Cabang harus diisi'
                    }
                }
            },
            'branch_city': {
                validators: {
                    notEmpty: {
                        message: 'Kota harus diisi'
                    }
                }
            },
            'branch_contact_person': {
                validators: {
                    notEmpty: {
                        message: 'Orang yang dapat dihubungi harus diisi'
                    }
                }
            },
            'branch_email': {
                validators: {
                    notEmpty: {
                        message: 'Email harus diisi'
                    }
                }
            },
            'branch_phone1': {
                validators: {
                    notEmpty: {
                        message: 'No. Telp harus diisi'
                    }
                }
            },
            'branch_manager': {
                validators: {
                    notEmpty: {
                        message: 'Kepala Cabang harus diisi'
                    }
                }
            },
            'account_rak_id': {
                validators: {
                    notEmpty: {
                        message: 'Rak Cabang harus diisi'
                    }
                }
            },
            'account_aka_id': {
                validators: {
                    notEmpty: {
                        message: 'Aka Cabang harus diisi'
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

const submitButton = document.getElementById('kt_core_branch_edit_submit');
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
                <h3 class="fw-bolder m-0">{{ __('Form Ubah Kode Cabang') }}</h3>
            </div>

            <a href="{{ route('branch.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_core_branch_edit_view_form" class="form" method="POST" action="{{ route('branch.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kode Cabang') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="hidden" name="branch_id" class="form-control form-control-lg form-control-solid" value="{{ old('branch_id', $corebranch->branch_id ?? '') }}"/>
                            <input type="text" name="branch_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Cabang" value="{{ old('branch_code', $corebranch->branch_code ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Cabang') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="branch_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Cabang" value="{{ old('branch_name', $corebranch->branch_name ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kepala Cabang') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="branch_manager" class="form-control form-control-lg form-control-solid" placeholder="Kepala Cabang" value="{{ old('branch_manager', $corebranch->branch_manager ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="branch_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" value="{{ old('branch_address', $corebranch->branch_address ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kota') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="branch_city" class="form-control form-control-lg form-control-solid" placeholder="Kota" value="{{ old('branch_city', $corebranch->branch_city ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Email') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="branch_email" class="form-control form-control-lg form-control-solid" placeholder="Email" value="{{ old('branch_email', $corebranch->branch_email ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">
                            {{ __('No. Telp') }}
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="{{ __('No telepon harus aktif') }}"></i>
                        </label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="branch_phone1" class="form-control form-control-lg form-control-solid" placeholder="No. Telp" value="{{ old('branch_phone1', $corebranch->branch_phone1 ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Orang yang dapat dihubungi') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="branch_contact_person" class="form-control form-control-lg form-control-solid" placeholder="Orang yang dapat dihubungi" value="{{ old('branch_contact_person', $corebranch->branch_contact_person ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Rak Cabang') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="account_rak_id" id="account_rak_id" aria-label="{{ __('Pilih') }}" data-control="select2" data-placeholder="{{ __('Pilih Rak Cabang') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($acctacount as $key => $value)
                                    <option data-kt-flag="{{ $value->account_id }}" value="{{ $value->account_id }}" {{ $value->account_id === old('account_id', $corebranch->account_rak_id ?? '') ? 'selected' :'' }}>{{ $value['account_code'] }} - {{ $value['account_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Aka Cabang') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="account_aka_id" id="account_aka_id" aria-label="{{ __('Pilih') }}" data-control="select2" data-placeholder="{{ __('Pilih Aka Cabang') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($acctacount as $key => $value)
                                    <option data-kt-flag="{{ $value->account_id }}" value="{{ $value->account_id }}" {{ $value->account_id === old('account_id', $corebranch->account_aka_id ?? '') ? 'selected' :'' }}>{{ $value['account_code'] }} - {{ $value['account_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_core_branch_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

