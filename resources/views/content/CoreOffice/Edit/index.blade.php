
@section('scripts')
<script>
const form = document.getElementById('kt_office_edit_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'office_code': {
                validators: {
                    notEmpty: {
                        message: 'Kode BO harus diisi'
                    }
                }
            },
            'office_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama BO harus diisi'
                    }
                }
            },
            'branch_id': {
                validators: {
                    notEmpty: {
                        message: 'Cabang BO harus diisi'
                    }
                }
            },
            'user_id': {
                validators: {
                    notEmpty: {
                        message: 'Akun BO harus diisi'
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

const submitButton = document.getElementById('kt_office_edit_submit');
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
                <h3 class="fw-bolder m-0">{{ __('Form Ubah Kode Business Office (BO)') }}</h3>
            </div>

            <a href="{{ route('office.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_office_edit_view_form" class="form" method="POST" action="{{ route('office.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kode BO') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="hidden" name="office_id" class="form-control form-control-lg form-control-solid" value="{{ old('office_id', $office->office_id ?? '') }}"/>
                            <input type="text" name="office_code" class="form-control form-control-lg form-control-solid" placeholder="Kode BO" value="{{ old('office_code', $office->office_code ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama BO') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="office_name" class="form-control form-control-lg form-control-solid" placeholder="Nama BO" value="{{ old('office_name', $office->office_name ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Cabang BO') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="branch_id" id="branch_id" aria-label="{{ __('Pilih') }}" data-control="select2" data-placeholder="{{ __('Pilih Cabang BO') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($corebranch as $key => $value)
                                    <option data-kt-flag="{{ $value->branch_id }}" value="{{ $value->branch_id }}" {{ $value->branch_id === old('branch_id', $office->branch_id ?? '') ? 'selected' :'' }}>{{ $value['branch_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Akun BO') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="user_id" id="user_id" aria-label="{{ __('Pilih') }}" data-control="select2" data-placeholder="{{ __('Pilih Cabang BO') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($user as $key => $value)
                                    <option data-kt-flag="{{ $value->user_id }}" value="{{ $value->user_id }}" {{ $value->user_id === old('user_id', $office->user_id ?? '') ? 'selected' :'' }}>{{ $value['username'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_office_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

