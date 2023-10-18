@section('scripts')
<script>
const form = document.getElementById('kt_mutation_edit_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'mutation_code': {
                validators: {
                    notEmpty: {
                        message: 'Kode Mutasi harus diisi'
                    }
                }
            },
            'mutation_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Mutasi harus diisi'
                    }
                }
            },
            'mutation_function': {
                validators: {
                    notEmpty: {
                        message: 'User Mutasi harus diisi'
                    }
                }
            },
            'mutation_status': {
                validators: {
                    notEmpty: {
                        message: 'Status Mutasi harus diisi'
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


$(form.querySelector('[name="mutation_status"]')).on('change', function () {
    validator.revalidateField('mutation_status');
});

const submitButton = document.getElementById('kt_mutation_edit_submit');
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
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Mutasi') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('mutation.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_mutation_edit_view">
            <form id="kt_mutation_edit_view_form" class="form" method="POST" action="{{ route('mutation.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kode Mutasi') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="hidden" name="mutation_id" class="form-control form-control-lg form-control-solid" placeholder="Kode Mutasi" value="{{ old('mutation_id', $mutation->mutation_id ?? '') }}" autocomplete="off"/>
                            <input type="text" name="mutation_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Mutasi" value="{{ old('mutation_code', $mutation->mutation_code ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Mutasi') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="mutation_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Mutasi" value="{{ old('mutation_name', $mutation->mutation_name ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Fungsi Mutasi') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="mutation_function" class="form-control form-control-lg form-control-solid" placeholder="+/-" value="{{ old('mutation_function', $mutation->mutation_function ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Status Mutasi') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="mutation_status" id="mutation_status" aria-label="{{ __('Pilih Status Mutasi') }}" data-control="select2" data-placeholder="{{ __('Pilih status mutasi..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih status mutasi..') }}</option>
                                @foreach($accountstatus as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('mutation_status', $mutation->mutation_status ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_mutation_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

