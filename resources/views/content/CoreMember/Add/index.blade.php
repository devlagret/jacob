<?php
if (empty($sessiondata)){
    $sessiondata['member_name']                   = '';
    $sessiondata['member_gender']                 = '';
    $sessiondata['member_place_of_birth']         = '';
    $sessiondata['member_date_of_birth']          = '';
    $sessiondata['member_address']                = '';
    $sessiondata['member_address_now']            = '';
    $sessiondata['province_id']                   = '';
    $sessiondata['city_id']                       = '';
    $sessiondata['kecamatan_id']                  = '';
    $sessiondata['kelurahan_id']                  = '';
    $sessiondata['member_nick_name']              = '';
    $sessiondata['member_postal_code']            = '';
    $sessiondata['member_marital_status']         = '';
    $sessiondata['member_home_status']            = '';
    $sessiondata['member_long_stay']              = '';
    $sessiondata['member_last_education']         = '';
    $sessiondata['member_identity_no']            = '';
    $sessiondata['member_partner_identity_no']    = '';
    $sessiondata['member_phone']                  = '';
    $sessiondata['member_partner_name']           = '';
    $sessiondata['member_partner_place_of_birth'] = '';
    $sessiondata['member_partner_date_of_birth']  = '';
    $sessiondata['member_email']                  = '';
    $sessiondata['member_dependent']              = '';
    $sessiondata['member_home_status']            = '';
    $sessiondata['member_heir']                   = '';
    $sessiondata['member_heir_relationship']      = '';
    $sessiondata['member_heir_mobile_phone']      = '';
    $sessiondata['member_heir_address']           = '';
    $sessiondata['member_working_type']           = '';
    $sessiondata['member_company_name']           = '';
    $sessiondata['member_company_specialities']   = '';
    $sessiondata['member_company_address']        = '';
    $sessiondata['member_company_phone']          = '';
    $sessiondata['member_business_scale']         = '';
    $sessiondata['member_business_owner']         = '';
    $sessiondata['member_monthly_income']         = 0;
    $sessiondata['partner_working_type']          = '';
    $sessiondata['partner_business_scale']        = '';
    $sessiondata['partner_business_owner']        = '';
}
?>
@section('scripts')
<script>
const form = document.getElementById('kt_member_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'member_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Lengkap harus diisi'
                    }
                }
            },
            'member_gender': {
                validators: {
                    notEmpty: {
                        message: 'Jenis Kelamin harus diisi'
                    }
                }
            },
            'member_place_of_birth': {
                validators: {
                    notEmpty: {
                        message: 'Tempat Lahir harus diisi'
                    }
                }
            },
            'member_date_of_birth': {
                validators: {
                    notEmpty: {
                        message: 'Tanggal Lahir harus diisi'
                    }
                }
            },
            'member_address': {
                validators: {
                    notEmpty: {
                        message: 'Alamat Sesuai KTP harus diisi'
                    }
                }
            },
            'member_address_now': {
                validators: {
                    notEmpty: {
                        message: 'Alamat Tinggal Sekarang harus diisi'
                    }
                }
            },
            'member_mother': {
                validators: {
                    notEmpty: {
                        message: 'Nama Ibu Kandung harus diisi'
                    }
                }
            },
            'member_principal_savings': {
                validators: {
                    notEmpty: {
                        message: 'Simpanan Pokok harus diisi'
                    }
                }
            },
            'province_id': {
                validators: {
                    notEmpty: {
                        message: 'Provinsi harus diisi'
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
            },
            'member_last_education': {
                validators: {
                    notEmpty: {
                        message: 'Pendidikan terakhir harus diisi'
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

$(form.querySelector('[name="province_id"]')).on('change', function () {
    validator.revalidateField('province_id');
});

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
            url : "{{route('member.elements-add')}}",
            data : {
                'name'      : name,
                'value'     : value,
                '_token'    : '{{csrf_token()}}'
            },
            success: function(msg){
        }
    });
}

function changeProvince(){
    var province_id = $("#province_id").val();
    function_elements_add('province_id', province_id);

    $.ajax({
        type: "POST",
        url : "{{route('member.get-city')}}",
        dataType: "html",
        data: {
            'province_id'   : province_id,
            'last_city_id'   : "{{old('city_id', (int)$sessiondata['city_id'] ?? '')}}",
            '_token'        : '{{csrf_token()}}',
        },
        success: function(return_data){
            $('#city_id').html(return_data);
            changeCity();
        },
        error: function(data)
        {
            console.log(data);
        }
    });
}

function changeCity(name = null,value = null){
    var city_id = $("#city_id").val();

    $.ajax({
        type: "POST",
        url : "{{route('member.get-kecamatan')}}",
        dataType: "html",
        data: {
            'city_id'   : city_id,
            'last_kecamatan_id'   : "{{old('kecamatan_id', (int)$sessiondata['kecamatan_id'] ?? '')}}",
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
        url : "{{route('member.get-kelurahan')}}",
        dataType: "html",
        data: {
            'kecamatan_id'   : kecamatan_id,
            'last_kelurahan_id' :  "{{old('kelurahan_id', (int)$sessiondata['kelurahan_id'] ?? '')}}",
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

function changeWorkingType(name = null,value = null){
    var member_working_type = $("#member_working_type").val();
    if(name != null && value != null){
        function_elements_add(name,value);
    }
    if(member_working_type == 3){
        $('.company').each(function(){$(this).css('display','none')});
        $('.business').each(function(){$(this).css('display','flex')});
    }else{
        $('.company').each(function(){$(this).css('display','flex')});
        $('.business').each(function(){$(this).css('display','none')});
    }
}
function changePartnerWorkingType(name = null,value = null){
    if(name != null && value != null){
        function_elements_add(name,value);
    }
    var member_working_type = $("#partner_working_type").val();
    if(member_working_type == 3){
        $('.partner-company').each(function(){$(this).css('display','none')});
        $('.partner-business').each(function(){$(this).css('display','flex')});
    }else{
        $('.partner-company').each(function(){$(this).css('display','flex')});
        $('.partner-business').each(function(){$(this).css('display','none')});
    }
}
$(document).ready(function(){
    changeWorkingType();changePartnerWorkingType();changeProvince();
    $("#member_principal_savings_view").change(function(){
        var member_principal_savings                                    = $("#member_principal_savings_view").val();
        document.getElementById("member_principal_savings").value       = member_principal_savings;
        document.getElementById("member_principal_savings_view").value  = toRp(member_principal_savings);
        function_elements_add('member_principal_savings', member_principal_savings);
    });

    $("#member_monthly_income_view").change(function(){
        var member_monthly_income                                    = $("#member_monthly_income_view").val();
        document.getElementById("member_monthly_income").value       = member_monthly_income;
        document.getElementById("member_monthly_income_view").value  = toRp(member_monthly_income);
        function_elements_add('member_monthly_income', member_monthly_income);
    });
});
</script>
@endsection


<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Anggota') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('member.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_member_add_view">
            <form id="kt_member_add_view_form" class="form" method="POST" action="{{ route('member.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-center text-primary">{{ __('Data Anggota') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Lengkap') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Sesuai KTP" value="{{ old('member_name', $sessiondata['member_name'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Panggilan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_nick_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Panggilan" value="{{ old('member_nick_name', $sessiondata['member_nick_name'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jenis Kelamin') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_gender" id="member_gender" aria-label="{{ __('Pilih Jenis Kelamin') }}" data-control="select2" data-placeholder="{{ __('Pilih jenis kelamin..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih jenis kelamin..') }}</option>
                                        @foreach($membergender as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_gender', (int)$sessiondata['member_gender'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tempat Lahir') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_place_of_birth" class="form-control form-control-lg form-control-solid" placeholder="Tempat Lahir" value="{{ old('member_place_of_birth', $sessiondata['member_place_of_birth'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Lahir') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="member_date_of_birth" id="member_date_of_birth" class="date form-control form-control-solid form-select-lg" placeholder="Pilih tanggal" value="{{ old('member_date_of_birth', $sessiondata['member_date_of_birth'] ?? '') }}" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Provinsi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="province_id" id="province_id" aria-label="{{ __('Provinsi') }}" data-control="select2" data-placeholder="{{ __('Pilih provinsi..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select2-hidden-accessible" onchange="changeProvince()">
                                        <option value="">{{ __('Pilih provinsi..') }}</option>
                                        @foreach($coreprovince as $key => $value)
                                            <option data-kt-flag="{{ $value['province_id'] }}" value="{{ $value['province_id'] }}" {{ $value['province_id'] === old('province_id', (int)$sessiondata['province_id'] ?? '') ? 'selected' :'' }}>{{ $value['province_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kabupaten') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="city_id" id="city_id" aria-label="{{ __('Kabupaten') }}" data-control="select2" data-placeholder="{{ __('Pilih kabupaten..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="changeCity()">
                                        <option value="">{{ __('Pilih kabupaten..') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kecamatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="kecamatan_id" id="kecamatan_id" aria-label="{{ __('Kecamatan') }}" data-control="select2" data-placeholder="{{ __('Pilih kecamatan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="changeKecamatan()">
                                        <option value="">{{ __('Pilih kecamatan..') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kelurahan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="kelurahan_id" id="kelurahan_id" aria-label="{{ __('Kelurahan') }}" data-control="select2" data-placeholder="{{ __('Pilih kelurahan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih kelurahan..') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Alamat Sesuai KTP') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="member_address" name="member_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Sesuai KTP" onchange="function_elements_add(this.name, this.value)">{{ old('member_address', $sessiondata['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Alamat Tinggal Sekarang') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="member_address_now" name="member_address_now" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Tinggal Sekarang" onchange="function_elements_add(this.name, this.value)">{{ old('member_address_now', $sessiondata['member_address_now'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kode Pos') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_postal_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Pos" value="{{ old('member_postal_code', $sessiondata['member_postal_code'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Status Pernikahan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_marital_status" id="member_marital_status" aria-label="{{ __('Pilih Status Pernikahan') }}" data-control="select2" data-placeholder="{{ __('Pilih status pernikahan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih status pernikahan..') }}</option>
                                        @foreach($maritalstatus as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_marital_status', (int)$sessiondata['member_marital_status'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. KTP/SIM') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="KTP / SIM " value="{{ old('member_identity_no', $sessiondata['member_identity_no'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. KTP/SIM Pasangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_partner_identity_no" class="form-control form-control-lg form-control-solid" placeholder="KTP / SIM Pasangan" value="{{ old('member_partner_identity_no', $sessiondata['member_partner_identity_no'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_phone" class="form-control form-control-lg form-control-solid" placeholder="No Telepon" value="{{ old('member_phone', $sessiondata['member_phone'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No HP') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_home_phone" class="form-control form-control-lg form-control-solid" placeholder="No Telepon" value="{{ old('member_home_phone', $sessiondata['member_home_phone'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Ibu Kandung') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_mother" class="form-control form-control-lg form-control-solid" placeholder="Nama Ibu Kandung" value="{{ old('member_mother', $sessiondata['member_mother'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah Tanggungan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_dependent" class="form-control form-control-lg form-control-solid" placeholder="Jumlah Tanggungan" value="{{ old('member_dependent', $sessiondata['member_dependent'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Status Rumah') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select data-placeholder="{{ __('Pilih status rumah..') }}" name="member_home_status" id="member_home_status" aria-label="{{ __('Pilih Status Rumah') }}" data-control="select2"  data-allow-clear="true" class="form-select form-select-solid form-select-lg"onchange="function_elements_add(this.name, this.value)" >
                                        <option value="">{{ __('Pilih status rumah..') }}</option>
                                        @foreach($homestatus as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_home_status', (int)$sessiondata['member_home_status'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                 </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Lama Menetap') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" placeholder="Lama Menetap" name="member_long_stay" id="member_long_stay" class="form-control form-control-lg form-control-solid"  value="{{ old('member_long_stay', $sessiondata['member_long_stay']) ?? '' }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                               </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Pendidikan Terakhir') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select data-placeholder="{{ __('Pilih pendidikan terakhir..') }}" name="member_last_education" id="member_last_education" aria-label="{{ __('Pilih pendidikan terakhir') }}" data-control="select2"  data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih pendidikan terakhir..') }}</option>
                                        @foreach($lasteducation as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_last_education', (int)$sessiondata['member_last_education'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                 </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Nama Pasangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" placeholder="Nama Pasangan" name="member_partner_name" id="member_partner_name" class="form-control form-control-lg form-control-solid"  value="{{ old('member_partner_name', $sessiondata['member_partner_name']) ?? '' }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                               </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Tempat Lahir Pasangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" placeholder="Tempat Lahir Pasangan" name="member_partner_place_of_birth" id="member_partner_place_of_birth" class="form-control form-control-lg form-control-solid"  value="{{ old('member_partner_place_of_birth', $sessiondata['member_partner_place_of_birth']) ?? '' }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                               </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Tanggal Lahir Pasangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input placeholder="Tanggal Lahir Pasangan" name="member_partner_date_of_birth" id="member_partner_date_of_birth" class="date form-control form-control-solid form-select-lg"  value="{{ old('member_partner_date_of_birth', date('d-m-Y',strtotime($sessiondata['member_partner_date_of_birth']))) ?? '' }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                               </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Email') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_email" class="form-control form-control-lg form-control-solid" placeholder="Email" value="{{ old('member_email', $sessiondata['member_email'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-center text-primary">{{ __('Data Pekerjaan') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tipe Pekerjaan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_working_type" id="member_working_type" aria-label="{{ __('Pilih Tipe Pekerjaan') }}" data-control="select2" data-placeholder="{{ __('Pilih tipe pekerjaan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="changeWorkingType(this.name, this.value)">
                                        <option value="">{{ __('Pilih tipe pekerjaan..') }}</option>
                                        @foreach($workingtype as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_working_type', (int)$sessiondata['member_working_type'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Perusahaan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Perusahaan" value="{{ old('member_company_name', $sessiondata['member_company_name'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Bidang Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_specialities" class="form-control form-control-lg form-control-solid" placeholder="Bidang Usaha" value="{{ old('member_company_specialities', $sessiondata['member_company_specialities'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jabatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_job_title" class="form-control form-control-lg form-control-solid" placeholder="Jabatan" value="{{ old('member_company_job_title', $sessiondata['member_company_job_title'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)" />
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Masa Kerja') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_period" class="form-control form-control-lg form-control-solid" placeholder="Masa Kerja" value="{{ old('member_company_period', $sessiondata['member_company_period'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" value="{{ old('member_company_address', $sessiondata['member_company_address'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_city" class="form-control form-control-lg form-control-solid" placeholder="Kota" value="{{ old('member_company_city', $sessiondata['member_company_city'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kode Pos') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_postal_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Pos" value="{{ old('member_company_postal_code', $sessiondata['member_company_postal_code'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_phone" class="form-control form-control-lg form-control-solid" placeholder="No Telepon" value="{{ old('member_company_phone', $sessiondata['member_company_phone'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 business" id="div_bus_name">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sub Bidang Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_name" class="form-control form-control-lg form-control-solid" placeholder="Sub Bidang Usaha" value="{{ old('member_business_name', $sessiondata['member_business_name'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 business" id="div_scale">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Skala Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_business_scale" id="member_business_scale" aria-label="{{ __('Pilih Skala Usaha') }}" data-control="select2" data-placeholder="{{ __('Pilih skala usaha..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih skala usaha..') }}</option>
                                        @foreach($businessscale as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_business_scale', (int)$sessiondata['member_business_scale'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                             <div class="row mb-6 business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Lama Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_period" class="form-control form-control-lg form-control-solid" placeholder="Lama Usaha" value="{{ old('member_business_period', $sessiondata['member_business_period'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 business" id="div_owner">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kepemilikan Tempat Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_business_owner" id="member_business_owner" aria-label="{{ __('Pilih Kepemilikan Tempat Usaha') }}" data-control="select2" data-placeholder="{{ __('Pilih kepemilikan tempat usaha..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih kepemilikan tempat usaha..') }}</option>
                                        @foreach($businessowner as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_business_owner', (int)$sessiondata['member_business_owner'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6 business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat Tempat Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat Tempat Usaha" value="{{ old('member_business_address', $sessiondata['member_business_address'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_city" class="form-control form-control-lg form-control-solid" placeholder="Kota" value="{{ old('member_business_city', $sessiondata['member_business_city'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kode Pos') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_postal_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Pos" value="{{ old('member_business_postal_code', $sessiondata['member_business_postal_code'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_phone" class="form-control form-control-lg form-control-solid" placeholder="Telepon" value="{{ old('member_business_phone', $sessiondata['member_business_phone'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Penghasilan Perbulan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_monthly_income_view" id="member_monthly_income_view" class="form-control form-control-lg form-control-solid" placeholder="Penghasilan Perbulan" value="{{ old('member_monthly_income_view', number_format((float)$sessiondata['member_monthly_income'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="member_monthly_income" id="member_monthly_income" class="form-control form-control-lg form-control-solid" placeholder="Penghasilan Perbulan" value="{{ old('member_monthly_income', $sessiondata['member_monthly_income'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6 ">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tipe Pekerjaan Pasangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="partner_working_type" id="partner_working_type" aria-label="{{ __('Pilih Tipe Pekerjaan Pasangan') }}" data-control="select2" data-placeholder="{{ __('Pilih tipe pekerjaan pasangan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="changePartnerWorkingType(this.name, this.value)">
                                        <option value="">{{ __('Pilih tipe pekerjaan pasangan..') }}</option>
                                        @foreach($workingtype as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('partner_working_type', (int)$sessiondata['partner_working_type'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6 partner-company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Perusahaan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_company_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Perusahaan" value="{{ old('partner_company_name', $sessiondata['partner_company_name'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 partner-company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jabatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_company_job_title" class="form-control form-control-lg form-control-solid" placeholder="Jabatan" value="{{ old('partner_company_job_title', $sessiondata['partner_company_job_title'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 partner-company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Bidang Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_company_specialities" class="form-control form-control-lg form-control-solid" placeholder="Bidang Usaha" value="{{ old('partner_company_specialities', $sessiondata['partner_company_specialities'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 partner-company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat Perusahaan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_company_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat Perusahaan" value="{{ old('partner_company_address', $sessiondata['partner_company_address'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 partner-company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_company_phone" class="form-control form-control-lg form-control-solid" placeholder="Telepon" value="{{ old('partner_company_phone', $sessiondata['partner_company_phone'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 partner-business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sub Bidang Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_business_name" class="form-control form-control-lg form-control-solid" placeholder="Sub Bidang Usaha" value="{{ old('partner_business_name', $sessiondata['partner_business_name'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 partner-business" id="div_scale_partner">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Skala Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="partner_business_scale" id="partner_business_scale" aria-label="{{ __('Pilih Skala Usaha') }}" data-control="select2" data-placeholder="{{ __('Pilih skala usaha..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih skala usaha..') }}</option>
                                        @foreach($businessscale as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('partner_business_scale', (int)$sessiondata['partner_business_scale'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                             <div class="row mb-6 partner-business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Lama Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_business_period" class="form-control form-control-lg form-control-solid" placeholder="Lama Usaha" value="{{ old('partner_business_period', $sessiondata['partner_business_period'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6 partner-business" id="div_owner_partner">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kepemilikan Tempat Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="partner_business_owner" id="partner_business_owner" aria-label="{{ __('Pilih Kepemilikan Tempat Usaha') }}" data-control="select2" data-placeholder="{{ __('Pilih kepemilikan tempat usaha..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih kepemilikan tempat usaha..') }}</option>
                                        @foreach($businessowner as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('partner_business_owner', (int)$sessiondata['partner_business_owner'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6 pt-12">
                                <b class="col-lg-12 fw-bold fs-3 text-center text-primary">{{ __('Data Ahli Waris') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_heir" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_heir', $sessiondata['member_heir'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Hubungan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_heir_relationship" id="member_heir_relationship" aria-label="{{ __('Pilih Hubungan') }}" data-control="select2" data-placeholder="{{ __('Pilih hubungan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih hubungan..') }}</option>
                                        @foreach($familyrelationship as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_heir_relationship', (int)$sessiondata['member_heir_relationship'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_heir_mobile_phone" class="form-control form-control-lg form-control-solid" placeholder="No Telepon" value="{{ old('member_heir_mobile_phone', $sessiondata['member_heir_mobile_phone'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="member_heir_address" name="member_heir_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat" onchange="function_elements_add(this.name, this.value)">{{ old('member_heir_address', $sessiondata['member_heir_address'] ?? '') }}</textarea>
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

