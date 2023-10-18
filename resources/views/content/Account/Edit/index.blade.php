@section('scripts')
<script>
const form = document.getElementById('kt_account_edit_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'account_code': {
                validators: {
                    notEmpty: {
                        message: 'No. Perkiraan harus diisi'
                    }
                }
            },
            'account_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Perkiraan harus diisi'
                    }
                }
            },
            'account_group': {
                validators: {
                    notEmpty: {
                        message: 'Golongan Perkiraan harus diisi'
                    }
                }
            },
            'account_status': {
                validators: {
                    notEmpty: {
                        message: 'Status Perkiraan harus diisi'
                    }
                }
            },
            'account_type_id': {
                validators: {
                    notEmpty: {
                        message: 'Kelompok Perkiraan diisi'
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

const submitButton = document.getElementById('kt_account_edit_submit');
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
                <h3 class="fw-bolder m-0">{{ __('Form Edit Perkiraan') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('account.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_account_edit_view">
            <form id="kt_account_edit_view_form" class="form" method="POST" action="{{ route('account.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Perkiaraan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="account_code" class="form-control form-control-lg form-control-solid" placeholder="No. Perkiaraan" value="{{ old('account_code', $account['account_code'] ?? '') }}" autocomplete="off"/>
                            <input type="hidden" name="account_id" class="form-control form-control-lg form-control-solid" placeholder="No. Perkiaraan" value="{{ old('account_id', $account['account_id'] ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Perkiraan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="account_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Perkiraan" value="{{ old('account_name', $account['account_name'] ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Golongan Perkiraan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="account_group" class="form-control form-control-lg form-control-solid" placeholder="Golongan Perkiraan" value="{{ old('account_group', $account['account_group'] ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Status Perkiraan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="account_status" id="account_status" aria-label="{{ __('Status Perkiraan') }}" data-control="select2" data-placeholder="{{ __('Pilih Status Perkiraan') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($accountstatus as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('account_status', $account['account_status'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kelompok Perkiraan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="account_type_id" id="account_type_id" aria-label="{{ __('Pilih Kelompok Perkiraan') }}" data-control="select2" data-placeholder="{{ __('Pilih Kelompok Perkiraan') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($kelompokperkiraan as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('account_type_id', $account['account_type_id'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_account_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

