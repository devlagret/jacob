@section('scripts')
<script>

const form = document.getElementById('kt_deposito_account_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'member_id': {
                validators: {
                    notEmpty: {
                        message: 'Anggota harus diisi'
                    }
                }
            },
            'savings_account_id': {
                validators: {
                    notEmpty: {
                        message: 'Tabungan harus diisi'
                    }
                }
            },
            'deposito_id': {
                validators: {
                    notEmpty: {
                        message: 'Jenis Simpanan Berjangka harus diisi'
                    }
                }
            },
            'office_id': {
                validators: {
                    notEmpty: {
                        message: 'BO harus diisi'
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
            'deposito_account_date': {
                validators: {
                    notEmpty: {
                        message: 'Tanggal Buka harus diisi'
                    }
                }
            },
            'deposito_account_due_date': {
                validators: {
                    notEmpty: {
                        message: 'Tanggal Jatuh Tempo harus diisi'
                    }
                }
            },
            'deposito_account_interest': {
                validators: {
                    notEmpty: {
                        message: 'Suku Bunga harus diisi'
                    }
                }
            },
            'deposito_account_amount': {
                validators: {
                    notEmpty: {
                        message: 'Nominal harus diisi'
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

$(form.querySelector('[name="deposito_id"]')).on('change', function () {
    validator.revalidateField('deposito_id');
});

$(form.querySelector('[name="deposito_account_extra_type"]')).on('change', function () {
    validator.revalidateField('deposito_account_extra_type');
});

$(form.querySelector('[name="office_id"]')).on('change', function () {
    validator.revalidateField('office_id');
});

const submitButton = document.getElementById('kt_deposito_account_add_submit');
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

$(document).ready(function(){
    $('#open_modal_button_member').click(function(){
        $.ajax({
            type: "GET",
            url : "{{route('deposito-account.modal-member')}}",
            success: function(msg){
                $('#kt_modal_core_member').modal('show');
                $('#modal-member-body').html(msg);
            }
        });
    });

    $('#open_modal_button_savings_account').click(function(){
        $.ajax({
            type: "GET",
            url : "{{route('deposito-account.modal-savings-account')}}",
            success: function(msg){
                $('#kt_modal_core_savings_account').modal('show');
                $('#modal-savings-account-body').html(msg);
            }
        });
    });
    
    $("#deposito_account_amount_view").change(function(){
        var deposito_account_amount                                    = $("#deposito_account_amount_view").val();
        document.getElementById("deposito_account_amount").value       = deposito_account_amount;
        document.getElementById("deposito_account_amount_view").value  = toRp(deposito_account_amount);
        function_elements_add('deposito_account_amount', deposito_account_amount);
    });
});

function function_elements_add(name, value){
    $.ajax({
        type: "POST",
        url : "{{route('deposito-account.elements-add')}}",
        data : {
            'name'      : name, 
            'value'     : value,
            '_token'    : '{{csrf_token()}}'
        },
        success: function(msg){
        }
    });
}

function changeDeposito(){
    var deposito_id = $("#deposito_id").val();
    function_elements_add('deposito_id', deposito_id);

    $.ajax({
        type: "POST",
        url : "{{route('deposito-account.get-deposito-detail')}}",
        dataType: "html",
        data: {
            'deposito_id'    : deposito_id,
            '_token'        : '{{csrf_token()}}',
        },
        success: function(return_data){ 
            return_data = JSON.parse(return_data);

            $('#deposito_period').val(return_data.deposito_period);
            function_elements_add('deposito_period', return_data.deposito_period);
            $('#deposito_account_interest').val(return_data.deposito_interest_rate);
            function_elements_add('deposito_account_interest', return_data.deposito_interest_rate);
            $('#deposito_account_due_date').val(return_data.deposito_account_due_date);
            function_elements_add('deposito_account_due_date', return_data.deposito_account_due_date);
        },
        error: function(data)
        {
            console.log(data);
        }
    });
}
</script>
@endsection
<?php 
if(empty($sessiondata)){
    $sessiondata['deposito_id']                             = null;
    $sessiondata['deposito_account_extra_type']             = null;
    $sessiondata['deposito_period']                         = null;
    $sessiondata['office_id']                               = null;
    $sessiondata['deposito_account_due_date']               = null;
    $sessiondata['deposito_account_amount']                 = 0;
}
if(!isset($coremember['member_heir_relationship'])){
    $coremember['member_heir_relationship'] = null;
}
if(!isset($coremember['member_gender'])){
    $coremember['member_gender'] = null;
}
?>
<x-base-layout>
    <div class="card">
        <div class="card-header">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Simpanan Berjangka') }}</h3>
            </div>
            <a href="{{ route('deposito-account.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}
            </a>
        </div>

        <div id="kt_deposito_account_view">
            <form id="kt_deposito_account_add_view_form" class="form" method="POST" action="{{ route('deposito-account.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Anggota') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No Anggota') }}</label>
                                <div class="col-lg-5 fv-row">
                                    <input type="hidden" name="member_id" class="form-control form-control-lg form-control-solid" placeholder="No Anggota" value="{{ old('member_id', $coremember['member_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="text" name="member_no" class="form-control form-control-lg form-control-solid" placeholder="No Anggota" value="{{ old('member_no', $coremember['member_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                                <div class="col-lg-3 fv-row">
                                    <button type="button" id="open_modal_button_member" class="btn btn-primary">
                                        {{ __('Cari Anggota') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_name', $coremember['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Lahir') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_date_of_birth" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Lahir" value="{{ old('member_date_of_birth', $coremember['member_date_of_birth'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Kelamin') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_gender" class="form-control form-control-lg form-control-solid" placeholder="Jenis Kelamin" value="{{ old('member_gender', $membergender[$coremember['member_gender']] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="member_address" name="member_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Sesuai KTP" readonly>{{ old('member_address', $coremember['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kabupaten') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="city_name" class="form-control form-control-lg form-control-solid" placeholder="Kabupaten" value="{{ old('city_name', $coremember['city_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kecamatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="kecamatan_name" class="form-control form-control-lg form-control-solid" placeholder="Kecamatan" value="{{ old('kecamatan_name', $coremember['kecamatan_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_phone" class="form-control form-control-lg form-control-solid" placeholder="No Telepon" value="{{ old('member_phone', $coremember['member_phone'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Ibu') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_mother" class="form-control form-control-lg form-control-solid" placeholder="Nama Ibu" value="{{ old('member_mother', $coremember['member_mother'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Identitas') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="No Identitas" value="{{ old('member_identity_no', $coremember['member_identity_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Ahli Waris') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_member_heir" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('deposito_member_heir', $coremember['member_heir'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Hub Keluarga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="deposito_member_heir_relationship" id="deposito_member_heir_relationship" aria-label="{{ __('Pilih Hubungan') }}" data-control="select2" data-placeholder="{{ __('Pilih hubungan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                        <option value="">{{ __('Pilih hubungan..') }}</option>
                                        @foreach($familyrelationship as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('deposito_member_heir_relationship', (int)$coremember['member_heir_relationship'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="deposito_member_heir_address" name="deposito_member_heir_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Sesuai KTP">{{ old('deposito_member_heir_address', $coremember['member_heir_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Simpanan Berjangka') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No Tabungan') }}</label>
                                <div class="col-lg-5 fv-row">
                                    <input type="hidden" name="savings_account_id" class="form-control form-control-lg form-control-solid" placeholder="No Tabungan" value="{{ old('savings_account_id', $savingsaccount['savings_account_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="text" name="savings_account_no" class="form-control form-control-lg form-control-solid" placeholder="No Tabungan" value="{{ old('savings_account_no', $savingsaccount['savings_account_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                                <div class="col-lg-3 fv-row">
                                    <button type="button" id="open_modal_button_savings_account" class="btn btn-primary">
                                        {{ __('Cari Tabungan') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jenis Simpanan Berjangka') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="deposito_id" id="deposito_id" aria-label="{{ __('Jenis Simpanan Berjangka') }}" data-control="select2" data-placeholder="{{ __('Pilih simpanan berjangka..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select2-hidden-accessible" onchange="changeDeposito()">
                                        <option value="">{{ __('Pilih simpanan berjangka..') }}</option>
                                        @foreach($acctdeposito as $key => $value)
                                            <option data-kt-flag="{{ $value['deposito_id'] }}" value="{{ $value['deposito_id'] }}" {{ $value['deposito_id'] === old('deposito_id', (int)$sessiondata['deposito_id'] ?? '') ? 'selected' :'' }}>{{ $value['deposito_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jangka Waktu') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_period" id="deposito_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka Waktu" value="{{ old('deposito_period', $sessiondata['deposito_period'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('BO') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="office_id" id="office_id" aria-label="{{ __('BO') }}" data-control="select2" data-placeholder="{{ __('Pilih bo..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select2-hidden-accessible" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih bo..') }}</option>
                                        @foreach($coreoffice as $key => $value)
                                            <option data-kt-flag="{{ $value['office_id'] }}" value="{{ $value['office_id'] }}" {{ $value['office_id'] === old('office_id', (int)$sessiondata['office_id'] ?? '') ? 'selected' :'' }}>{{ $value['office_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Buka') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Buka" value="{{ date('d-m-Y') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Jatuh Tempo') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_due_date" id="deposito_account_due_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Jatuh Tempo" value="{{ old('deposito_account_due_date', $sessiondata['deposito_account_due_date'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Suku Bunga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_interest" id="deposito_account_interest" class="form-control form-control-lg form-control-solid" placeholder="%" value="{{ old('deposito_account_interest', $sessiondata['deposito_account_interest'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nominal') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_amount_view" id="deposito_account_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Setoran" value="{{ old('deposito_account_amount_view', number_format($sessiondata['deposito_account_amount'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="deposito_account_amount" id="deposito_account_amount" class="form-control form-control-lg form-control-solid" placeholder="Setoran" value="{{ old('deposito_account_amount', $sessiondata['deposito_account_amount'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_deposito_account_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_modal_core_member">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Daftar Anggota</h3>
    
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="bi bi-x-lg"></span>
                    </div>
                </div>
    
                <div class="modal-body" id="modal-member-body">
                </div>
    
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_modal_core_savings_account">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Daftar Tabungan</h3>
    
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="bi bi-x-lg"></span>
                    </div>
                </div>
    
                <div class="modal-body" id="modal-savings-account-body">
                </div>
    
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>