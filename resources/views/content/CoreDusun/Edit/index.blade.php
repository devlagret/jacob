@section('scripts')
<script>
const form = document.getElementById('dusun-form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'dusun_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Dusun harus diisi'
                    }
                }
            },
            'city_id': {
                validators: {
                    notEmpty: {
                        message: 'Kabupaten harus diisi'
                    }
                }
            },
            'kecamatan_id': {
                validators: {
                    notEmpty: {
                        message: 'Kecamatan harus diisi'
                    }
                }
            },
            'kelurahan_id': {
                validators: {
                    notEmpty: {
                        message: 'Kelurahan harus diisi'
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

$(form.querySelector('[name="city_id"]')).on('change', function () {
    validator.revalidateField('city_id');
});

$(form.querySelector('[name="kecamatan_id"]')).on('change', function () {
    validator.revalidateField('kecamatan_id');
});

$(form.querySelector('[name="kelurahan_id"]')).on('change', function () {
    validator.revalidateField('kelurahan_id');
});

const submitButton = document.getElementById('kt_member_add_submit');
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
            url : "{{route('dusun.elements-add')}}",
            data : {
                'name'      : name,
                'value'     : value,
                '_token'    : '{{csrf_token()}}'
            },
            success: function(msg){
        }
    });
}

function changeCity(name = null,value = null){
    var city_id = $("#city_id").val();
    $.ajax({
        type: "POST",
        url : "{{route('dusun.get-kecamatan')}}",
        dataType: "html",
        data: {
            'city_id'   : city_id,
            'last_kecamatan_id'   : "{{old('kecamatan_id', $sessiondata['kecamatan_id'] ?? $data->kelurahan->kecamatan_id)}}",
            '_token'        : '{{csrf_token()}}',
        },
        success: function(return_data){
            $('#kecamatan_id').html(return_data);
            function_elements_add('city_id', city_id);
            changeKecamatan();
        },
        error: function(data)
        {
            console.log(data);
        }
    });
}

function changeKecamatan(name = null,value = null){
    var kecamatan_id = $("#kecamatan_id").val();
    $.ajax({
        type: "POST",
        url : "{{route('dusun.get-kelurahan')}}",
        dataType: "html",
        data: {
            'kecamatan_id'   : kecamatan_id,
            'last_kelurahan_id' :  "{{old('kelurahan_id', $sessiondata['kelurahan_id'] ?? $data->kelurahan->kelurahan_id)}}",
            '_token'        : '{{csrf_token()}}',
        },
        success: function(return_data){
            $('#kelurahan_id').html(return_data);
            function_elements_add('kecamatan_id', kecamatan_id);
            function_elements_add('kelurahan_id', $("#kelurahan_id").val());
        },
        error: function(data)
        {
            console.log(data);
        }
    });
}

$(document).ready(function(){
    changeCity();
});
</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Dusun') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('dusun.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_dusun_add_view">
            <form id="dusun-form" class="form" method="POST" action="{{ route('dusun.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <input type="hidden" name="dusun_id" id="dusun_id" value="{{$data->dusun_id}}"/>
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <div class="row">
                            <div class="col mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kabupaten') }}</label>
                                <div class="col-lg-12 fv-row">
                                    <select name="city_id" id="city_id" aria-label="{{ __('Kabupaten') }}" data-control="select2" data-placeholder="{{ __('Pilih Kabupaten..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select2-hidden-accessible" onchange="changeCity()">
                                        <option value="">{{ __('Pilih Kabupaten..') }}</option>
                                        @foreach($corekabupaten as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('city_id', $sessiondata['city_id'] ?? $data->kelurahan->kecamatan->city_id) ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kecamatan') }}</label>
                                <div class="col-lg-12 fv-row">
                                    <select name="kecamatan_id" id="kecamatan_id" aria-label="{{ __('Kecamatan') }}" data-control="select2" data-placeholder="{{ __('Pilih kecamatan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="changeKecamatan()">
                                        <option value="">{{ __('Pilih kecamatan..') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kelurahan') }}</label>
                                <div class="col-lg-12 fv-row">
                                    <select name="kelurahan_id" id="kelurahan_id" aria-label="{{ __('Kelurahan') }}" value="{{$data->kelurahan->kelurahan_id}}" data-control="select2" data-placeholder="{{ __('Pilih kelurahan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih kelurahan..') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Dusun') }}</label>
                                <div class="col-lg-12 fv-row">
                                    <input name="dusun_name" id="dusun_name" type="text" class="form-control form-control-solid form-select-lg" placeholder="Masukan Nama Dusun" value="{{ old('dusun_name', $sessiondata['dusun_name'] ?? $data->dusun_name) }}" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>

                    <button type="submit" class="btn btn-primary" id="kt_member_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

