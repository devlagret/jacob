@section('bladeScripts')
<script>
const form = document.getElementById('income-form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'income_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Pendapatan harus diisi'
                    }
                }
            },
            'income_group': {
                validators: {
                    notEmpty: {
                        message: 'Kelompok Pendapatan harus diisi'
                    }
                }
            },
            'account_id': {
                validators: {
                    notEmpty: {
                        message: 'No. Perkiraan harus diisi'
                    }
                }
            }
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

const submitButton = document.getElementById('kt_income_add_submit');
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

function function_elements_add(name, value){
    $.ajax({
            type: "POST",
            url : "{{route('preference-income.elements-add')}}",
            data : {
                'name'      : name,
                'value'     : value,
                '_token'    : '{{csrf_token()}}'
            },
            success: function(msg){
        }
    });
}

$(document).ready(function(){
    $('#income_percentage').change(function (e) { 
        if($(this).val()>100){
            $(this).val(100);
        }
        if($(this).val()<0){
            $(this).val(0);
        }
    });
});
</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Dusun') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('preference-income.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="preference-income">
            <form id="income-form" class="form" method="POST" action="{{ route('preference-income.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <div class="row">
                            <div class="col mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama') }}</label>
                                <div class="col-lg-12 fv-row">
                                    <input type="text" name="income_name" id="income_name" class="form-control form-control-solid form-select-lg" placeholder="Masukan Nama Pendapatan" value="{{ old('income_name', $sessiondata['income_name'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)" />
                                </div>
                            </div>
                            <div class="col mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kelompok') }}</label>
                                <div class="col-lg-12 fv-row">
                                    <select name="income_group" id="income_group" aria-label="{{ __('Kelompok') }}" data-control="select2" data-placeholder="{{ __('Pilih Kelompok..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih Kelompok...') }}</option>
                                        @foreach($kp as $key => $value)
                                        <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('account_id', $sessiondata['account_id'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Persen') }}</label>
                                <div class="col-lg-12 fv-row">
                                    <input type="number" min="0" max="100" name="income_percentage" id="income_percentage" class="form-control form-control-solid form-select-lg" placeholder="Masukan Nama Pendapatan" autocomplete="off" value="{{ old('income_percentage', $sessiondata['income_percentage'] ?? '') }}" onchange="function_elements_add(this.name, this.value)" />
                                </div>
                            </div>
                            <div class="col mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Perkiraan') }}</label>
                                <div class="col-lg-12 fv-row">
                                    <select name="account_id" id="account_id" aria-label="{{ __('No. Perkiraan') }}" data-control="select2" data-placeholder="{{ __('Pilih No. Perkiraan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih No. Perkiraan...') }}</option>
                                        @foreach($akun as $key => $value)
                                        <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('account_id', $sessiondata['account_id'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>

                    <button type="submit" class="btn btn-primary" id="kt_income_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Tambah')])
                    </button>
                </div>
            </form>
        </div>
        <form id="income-table-form" class="form" method="POST" action="{{ route('preference-income.process-edit') }}" enctype="multipart/form-data">
        @csrf
        @include('content.PreferenceIncome._table')
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <button type="submit" class="btn btn-primary" id="kt_income_save">
                @include('partials.general._button-indicator', ['label' => __('Simpan Perubahan')])
            </button>
        </div>
        </form>
    </div>
</x-base-layout>

