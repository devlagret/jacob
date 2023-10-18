@section('scripts')
<script>
const form = document.getElementById('kt_member_edit_view_form');

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
                'member_character': {
                    validators: {
                        notEmpty: {
                            message: 'Status keanggotaan harus diisi'
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

const submitButton = document.getElementById('kt_member_edit_submit');
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


function changeProvince(){
    var province_id = $("#province_id").val();

    $.ajax({
        type: "POST",
        url : "{{route('member.get-city')}}",
        dataType: "html",
        data: {
            'province_id'   : province_id,
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

function changeCity(){
    var city_id = $("#city_id").val();

    $.ajax({
        type: "POST",
        url : "{{route('member.get-kecamatan')}}",
        dataType: "html",
        data: {
            'city_id'   : city_id,
            '_token'        : '{{csrf_token()}}',
        },
        success: function(return_data){
            $('#kecamatan_id').html(return_data);
            changeKecamatan();
        },
        error: function(data)
        {
            console.log(data);
        }
    });
}

function changeKecamatan(){
    var kecamatan_id = $("#kecamatan_id").val();

    $.ajax({
        type: "POST",
        url : "{{route('member.get-kelurahan')}}",
        dataType: "html",
        data: {
            'kecamatan_id'   : kecamatan_id,
            '_token'        : '{{csrf_token()}}',
        },
        success: function(return_data){
            $('#kelurahan_id').html(return_data);
        },
        error: function(data)
        {
            console.log(data);
        }
    });
}

function changeWorkingType(){
    var member_working_type = $("#member_working_type").val();
    if(member_working_type == 3){
        $('.company').each(function(){$(this).css('display','none')});
        $('.business').each(function(){$(this).css('display','flex')});
    }else{
        $('.company').each(function(){$(this).css('display','flex')});
        $('.business').each(function(){$(this).css('display','none')});
    }
}
function changePartnerWorkingType(){
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
    changeWorkingType();changePartnerWorkingType();
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
                <h3 class="fw-bolder m-0">{{ __('Form Ubah Anggota') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('member.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_member_edit_view">
            <form id="kt_member_edit_view_form" class="form" method="POST" action="{{ route('member.process-edit') }}" enctype="multipart/form-data">
            @csrf
            {{-- @method('PUT') --}}
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-center text-primary">{{ __('Data Anggota') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Lengkap') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="hidden" name="member_id" class="form-control form-control-lg form-control-solid" placeholder="Sesuai KTP" value="{{ old('member_id', $member['member_id'] ?? '') }}" autocomplete="off" />
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Sesuai KTP" value="{{ old('member_name', $member['member_name'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Panggilan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_nick_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Panggilan" value="{{ old('member_nick_name', $member['member_nick_name'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jenis Kelamin') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_gender" id="member_gender" aria-label="{{ __('Pilih Jenis Kelamin') }}" data-control="select2" data-placeholder="{{ __('Pilih jenis kelamin..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" >
                                        <option value="">{{ __('Pilih jenis kelamin..') }}</option>
                                        @foreach($membergender as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_gender', (int)$member['member_gender'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tempat Lahir') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_place_of_birth" class="form-control form-control-lg form-control-solid" placeholder="Tempat Lahir" value="{{ old('member_place_of_birth', $member['member_place_of_birth'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Lahir') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="member_date_of_birth" id="member_date_of_birth" class="date form-control form-control-solid form-select-lg" placeholder="Pilih tanggal" value="{{ old('member_date_of_birth', date('d-m-Y',strtotime($member['member_date_of_birth'])) ?? '') }}" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Provinsi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="province_id" id="province_id" aria-label="{{ __('Provinsi') }}" data-control="select2" data-placeholder="{{ __('Pilih provinsi..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select2-hidden-accessible" onchange="changeProvince()">
                                        <option value="">{{ __('Pilih provinsi..') }}</option>
                                        @foreach($coreprovince as $key => $value)
                                            <option data-kt-flag="{{ $value['province_id'] }}" value="{{ $value['province_id'] }}" {{ $value['province_id'] === old('province_id', (int)$member['province_id'] ?? '') ? 'selected' :'' }}>{{ $value['province_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kabupaten') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="city_id" id="city_id" aria-label="{{ __('Kabupaten') }}" data-control="select2" data-placeholder="{{ __('Pilih kabupaten..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="changeCity()">
                                        <option value="">{{ __('Pilih kabupaten..') }}</option>
                                        @foreach($corecity as $key => $value)
                                            <option data-kt-flag="{{ $value['city_id'] }}" value="{{ $value['city_id'] }}" {{ $value['city_id'] === old('city_id', (int)$member['city_id'] ?? '') ? 'selected' :'' }}>{{ $value['city_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kecamatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="kecamatan_id" id="kecamatan_id" aria-label="{{ __('Kecamatan') }}" data-control="select2" data-placeholder="{{ __('Pilih kecamatan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="changeKecamatan()">
                                        <option value="">{{ __('Pilih kecamatan..') }}</option>
                                        @foreach($corekecamatan as $key => $value)
                                            <option data-kt-flag="{{ $value['kecamatan_id'] }}" value="{{ $value['kecamatan_id'] }}" {{ $value['kecamatan_id'] === old('kecamatan_id', (int)$member['kecamatan_id'] ?? '') ? 'selected' :'' }}>{{ $value['kecamatan_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kelurahan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="kelurahan_id" id="kelurahan_id" aria-label="{{ __('Kelurahan') }}" data-control="select2" data-placeholder="{{ __('Pilih kelurahan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                        <option value="">{{ __('Pilih kelurahan..') }}</option>
                                        @foreach($corekelurahan as $key => $value)
                                            <option data-kt-flag="{{ $value['kelurahan_id'] }}" value="{{ $value['kelurahan_id'] }}" {{ $value['kelurahan_id'] === old('kelurahan_id', (int)$member['kelurahan_id'] ?? '') ? 'selected' :'' }}>{{ $value['kelurahan_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Alamat Sesuai KTP') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="member_address" name="member_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Sesuai KTP" >{{ old('member_address', $member['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Alamat Tinggal Sekarang') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="member_address_now" name="member_address_now" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Tinggal Sekarang" >{{ old('member_address_now', $member['member_address_now'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kode Pos') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_postal_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Pos" value="{{ old('member_postal_code', $member['member_postal_code'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Status Pernikahan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_marital_status" id="member_marital_status" aria-label="{{ __('Pilih Status Pernikahan') }}" data-control="select2" data-placeholder="{{ __('Pilih status pernikahan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" >
                                        <option value="">{{ __('Pilih status pernikahan..') }}</option>
                                        @foreach($maritalstatus as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_marital_status', (int)$member['member_marital_status'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. KTP/SIM') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="KTP / SIM " value="{{ old('member_identity_no', $member['member_identity_no'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. KTP/SIM Pasangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_partner_identity_no" class="form-control form-control-lg form-control-solid" placeholder="KTP / SIM Pasangan" value="{{ old('member_partner_identity_no', $member['member_partner_identity_no'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_phone" class="form-control form-control-lg form-control-solid" placeholder="No Telepon" value="{{ old('member_phone', $member['member_phone'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No HP') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_home_phone" class="form-control form-control-lg form-control-solid" placeholder="No Telepon" value="{{ old('member_home_phone', $member['member_home_phone'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Ibu Kandung') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_mother" class="form-control form-control-lg form-control-solid" placeholder="Nama Ibu Kandung" value="{{ old('member_mother', $member['member_mother'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Status Keanggotaan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_character" id="member_character" aria-label="{{ __('Pilih Status Keanggotaan') }}" data-control="select2" data-placeholder="{{ __('Pilih status keanggotaan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" >
                                        <option value="">{{ __('Pilih status keanggotaan..') }}</option>
                                        @foreach($membercharacter as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_character', (int)$member['member_character'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                 </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Jumlah Tanggungan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_dependent" id="member_dependent" class="form-control form-control-lg form-control-solid" placeholder="Jumlah Tanggungan" value="{{ old('member_dependent', $member['member_dependent']) ?? '' }}" autocomplete="off" />
                               </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Status Rumah') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select data-placeholder="{{ __('Pilih status rumah..') }}" name="member_home_status" id="member_home_status" aria-label="{{ __('Pilih Status Rumah') }}" data-control="select2"  data-allow-clear="true" class="form-select form-select-solid form-select-lg" >
                                        <option value="">{{ __('Pilih status rumah..') }}</option>
                                        @foreach($homestatus as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_home_status', (int)$member['member_home_status'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                 </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Lama Menetap') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" placeholder="Lama Menetap" name="member_long_stay" id="member_long_stay" class="form-control form-control-lg form-control-solid"  value="{{ old('member_long_stay', $member['member_long_stay']) ?? '' }}" autocomplete="off" />
                               </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Pendidikan Terakhir') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select data-placeholder="{{ __('Pilih pendidikan terakhir..') }}" name="member_last_education" id="member_last_education" aria-label="{{ __('Pilih pendidikan terakhir') }}" data-control="select2"  data-allow-clear="true" class="form-select form-select-solid form-select-lg" >
                                        <option value="">{{ __('Pilih pendidikan terakhir..') }}</option>
                                        @foreach($lasteducation as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_last_education', (int)$member['member_last_education'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                 </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Nama Pasangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" placeholder="Nama Pasangan" name="member_partner_name" id="member_partner_name" class="form-control form-control-lg form-control-solid"  value="{{ old('member_partner_name', $member['member_partner_name']) ?? '' }}" autocomplete="off" />
                               </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Tempat Lahir Pasangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" placeholder="Tempat Lahir Pasangan" name="member_partner_place_of_birth" id="member_partner_place_of_birth" class="form-control form-control-lg form-control-solid"  value="{{ old('member_partner_place_of_birth', $member['member_partner_place_of_birth']) ?? '' }}" autocomplete="off" />
                               </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Tanggal Lahir Pasangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input placeholder="Tanggal Lahir Pasangan" name="member_partner_date_of_birth" id="member_partner_date_of_birth" class="date form-control form-control-solid form-select-lg"  value="{{ old('member_partner_date_of_birth', date('d-m-Y',strtotime($member['member_partner_date_of_birth']))) ?? '' }}" autocomplete="off" />
                               </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Email') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_email" class="form-control form-control-lg form-control-solid" placeholder="Email" value="{{ old('member_email', $member['member_email'] ?? '') }}" autocomplete="off" />
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
                                    <select name="member_working_type" id="member_working_type" aria-label="{{ __('Pilih Tipe Pekerjaan') }}" data-control="select2" data-placeholder="{{ __('Pilih tipe pekerjaan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="changeWorkingType()">
                                        <option value="">{{ __('Pilih tipe pekerjaan..') }}</option>
                                        @foreach($workingtype as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_working_type', (int)$memberworking['member_working_type'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Perusahaan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Perusahaan" value="{{ old('member_company_name', $memberworking['member_company_name'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Bidang Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_specialities" class="form-control form-control-lg form-control-solid" placeholder="Bidang Usaha" value="{{ old('member_company_specialities', $memberworking['member_company_specialities'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jabatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_job_title" class="form-control form-control-lg form-control-solid" placeholder="Jabatan" value="{{ old('member_company_job_title', $memberworking['member_company_job_title'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Masa Kerja') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_period" class="form-control form-control-lg form-control-solid" placeholder="Masa Kerja" value="{{ old('member_company_period', $memberworking['member_company_period'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat Perusahaan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat Perusahaan" value="{{ old('member_company_address', $memberworking['member_company_address'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_city" class="form-control form-control-lg form-control-solid" placeholder="Kota" value="{{ old('member_company_city', $memberworking['member_company_city'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kode Pos') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_postal_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Pos" value="{{ old('member_company_postal_code', $memberworking['member_company_postal_code'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_company_phone" class="form-control form-control-lg form-control-solid" placeholder="Telepon" value="{{ old('member_company_phone', $memberworking['member_company_phone'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 business" id="div_bus_name">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sub Bidang Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_name" class="form-control form-control-lg form-control-solid" placeholder="Sub Bidang Usaha" value="{{ old('member_business_name', $memberworking['member_business_name'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 business" id="div_scale">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Skala Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_business_scale" id="member_business_scale" aria-label="{{ __('Pilih Skala Usaha') }}" data-control="select2" data-placeholder="{{ __('Pilih skala usaha..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" >
                                        <option value="">{{ __('Pilih skala usaha..') }}</option>
                                        @foreach($businessscale as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_business_scale', (int)$memberworking['member_business_scale'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                             <div class="row mb-6 business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Lama Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_period" class="form-control form-control-lg form-control-solid" placeholder="Lama Usaha" value="{{ old('member_business_period', $memberworking['member_business_period'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 business" id="div_owner">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kepemilikan Tempat Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_business_owner" id="member_business_owner" aria-label="{{ __('Pilih Kepemilikan Tempat Usaha') }}" data-control="select2" data-placeholder="{{ __('Pilih kepemilikan tempat usaha..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" >
                                        <option value="">{{ __('Pilih kepemilikan tempat usaha..') }}</option>
                                        @foreach($businessowner as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_business_owner', (int)$memberworking['member_business_owner'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6 business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat Tempat Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat Tempat Usaha" value="{{ old('member_business_address', $memberworking['member_business_address'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_city" class="form-control form-control-lg form-control-solid" placeholder="Kota" value="{{ old('member_business_city', $memberworking['member_business_city'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kode Pos') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_postal_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Pos" value="{{ old('member_business_postal_code', $memberworking['member_business_postal_code'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_business_phone" class="form-control form-control-lg form-control-solid" placeholder="Telepon" value="{{ old('member_business_phone', $memberworking['member_business_phone'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Penghasilan Perbulan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_monthly_income_view" id="member_monthly_income_view" class="form-control form-control-lg form-control-solid" placeholder="Penghasilan Perbulan" value="{{ old('member_monthly_income_view', number_format((float)$memberworking['member_monthly_income'], 2) ?? '') }}" autocomplete="off" />
                                    <input type="hidden" name="member_monthly_income" id="member_monthly_income" class="form-control form-control-lg form-control-solid" placeholder="Penghasilan Perbulan" value="{{ old('member_monthly_income', $memberworking['member_monthly_income'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 ">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tipe Pekerjaan Pasangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="partner_working_type" id="partner_working_type" aria-label="{{ __('Pilih Tipe Pekerjaan Pasangan') }}" data-control="select2" data-placeholder="{{ __('Pilih tipe pekerjaan pasangan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="changePartnerWorkingType()">
                                        <option value="">{{ __('Pilih tipe pekerjaan pasangan..') }}</option>
                                        @foreach($workingtype as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('partner_working_type', (int)$memberworking['partner_working_type'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6 partner-company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Perusahaan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_company_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Perusahaan" value="{{ old('partner_company_name', $memberworking['partner_company_name'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 partner-company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jabatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_company_job_title" class="form-control form-control-lg form-control-solid" placeholder="Jabatan" value="{{ old('partner_company_job_title', $memberworking['partner_company_job_title'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 partner-company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Bidang Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_company_specialities" class="form-control form-control-lg form-control-solid" placeholder="Bidang Usaha" value="{{ old('partner_company_specialities', $memberworking['partner_company_specialities'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 partner-company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat Perusahaan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_company_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat Perusahaan" value="{{ old('partner_company_address', $memberworking['partner_company_address'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 partner-company">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_company_phone" class="form-control form-control-lg form-control-solid" placeholder="Telepon" value="{{ old('partner_company_phone', $memberworking['partner_company_phone'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 partner-business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sub Bidang Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_business_name" class="form-control form-control-lg form-control-solid" placeholder="Sub Bidang Usaha" value="{{ old('partner_business_name', $memberworking['partner_business_name'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 partner-business" id="div_scale_partner">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Skala Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="partner_business_scale" id="partner_business_scale" aria-label="{{ __('Pilih Skala Usaha') }}" data-control="select2" data-placeholder="{{ __('Pilih skala usaha..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" >
                                        <option value="">{{ __('Pilih skala usaha..') }}</option>
                                        @foreach($businessscale as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('partner_business_scale', (int)$memberworking['partner_business_scale'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                             <div class="row mb-6 partner-business">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Lama Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="partner_business_period" class="form-control form-control-lg form-control-solid" placeholder="Lama Usaha" value="{{ old('partner_business_period', $memberworking['partner_business_period'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6 partner-business" id="div_owner_partner">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kepemilikan Tempat Usaha') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="partner_business_owner" id="partner_business_owner" aria-label="{{ __('Pilih Kepemilikan Tempat Usaha') }}" data-control="select2" data-placeholder="{{ __('Pilih kepemilikan tempat usaha..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" >
                                        <option value="">{{ __('Pilih kepemilikan tempat usaha..') }}</option>
                                        @foreach($businessowner as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('partner_business_owner', (int)$memberworking['partner_business_owner'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
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
                                    <input type="text" name="member_heir" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_heir', $member['member_heir'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Hubungan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_heir_relationship" id="member_heir_relationship" aria-label="{{ __('Pilih Hubungan') }}" data-control="select2" data-placeholder="{{ __('Pilih hubungan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" >
                                        <option value="">{{ __('Pilih hubungan..') }}</option>
                                        @foreach($familyrelationship as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('member_heir_relationship', (int)$member['member_heir_relationship'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_heir_mobile_phone" class="form-control form-control-lg form-control-solid" placeholder="No Telepon" value="{{ old('member_heir_mobile_phone', $member['member_heir_mobile_phone'] ?? '') }}" autocomplete="off" />
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="member_heir_address" name="member_heir_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat" >{{ old('member_heir_address', $member['member_heir_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>

                    <button type="submit" class="btn btn-primary" id="kt_member_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

