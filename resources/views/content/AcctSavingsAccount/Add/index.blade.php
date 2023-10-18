@section('scripts')
<script>

const form = document.getElementById('kt_savings_account_add_view_form');

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
            'savings_id': {
                validators: {
                    notEmpty: {
                        message: 'Jenis Tabungan harus diisi'
                    }
                }
            },
            'savings_account_date': {
                validators: {
                    notEmpty: {
                        message: 'Tanggal Buka harus diisi'
                    }
                }
            },
            'savings_interest_rate': {
                validators: {
                    notEmpty: {
                        message: 'Bunga Per Bulan harus diisi'
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
            'savings_account_first_deposit_amount': {
                validators: {
                    notEmpty: {
                        message: 'Setoran harus diisi'
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
var needPeriod = !!false; 
	function showPeriod(){
		needPeriod = !!true;
		$('#period_date_input').removeAttr("hidden");
		$('#pickup_date_input').removeAttr("hidden");
	}
	function hidePeriod(){
		needPeriod = !!false;
		$('#period_date_input').prop("hidden",true);
		$('#pickup_date_input').prop("hidden",true);
	}
var validator2 = FormValidation.formValidation(
    form,
    {
        fields: {
            'saving_account_period': {
                validators: {
                    notEmpty: {
                        message: 'Masa Tabungan harus diisi'
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


$(form.querySelector('[name="savings_id"]')).on('change', function () {
    validator.revalidateField('savings_id');
});

$(form.querySelector('[name="office_id"]')).on('change', function () {
    validator.revalidateField('office_id');
});

const submitButton = document.getElementById('kt_savings_account_add_submit');
submitButton.addEventListener('click', function (e) {
    e.preventDefault();

    if (validator) {
        validator.validate().then(function (status) {
        if(needPeriod){
            validator2.validate().then(function (status2) {
                if (status == 'Valid' && status2 == 'Valid') {
                    submitButton.setAttribute('data-kt-indicator', 'on');

                    submitButton.disabled = true;

                    setTimeout(function () {
                        submitButton.removeAttribute('data-kt-indicator');

                        form.submit();
                    }, 2000);
                }
            });
        } else {
            if (status == 'Valid') {
                submitButton.setAttribute('data-kt-indicator', 'on');

                submitButton.disabled = true;

                setTimeout(function () {
                    submitButton.removeAttribute('data-kt-indicator');

                    form.submit();
                }, 2000);
            }
        }
    })
}
});

$(document).ready(function(){
    $('#open_modal_button').click(function(){
        $.ajax({
            type: "GET",
            url : "{{route('savings-account.modal-member')}}",
            success: function(msg){
                $('#kt_modal_core_member').modal('show');
                $('#modal-body').html(msg);
            }
        });
    });
    
    $("#savings_account_first_deposit_amount_view").change(function(){
        var savings_account_first_deposit_amount                                    = $("#savings_account_first_deposit_amount_view").val();
        document.getElementById("savings_account_first_deposit_amount").value       = savings_account_first_deposit_amount;
        document.getElementById("savings_account_first_deposit_amount_view").value  = toRp(savings_account_first_deposit_amount);
        function_elements_add('savings_account_first_deposit_amount', savings_account_first_deposit_amount);
    });
});

function function_elements_add(name, value){
    $.ajax({
        type: "POST",
        url : "{{route('savings-account.elements-add')}}",
        data : {
            'name'      : name, 
            'value'     : value,
            '_token'    : '{{csrf_token()}}'
        },
        success: function(msg){
        }
    });
}
const index = ['25','26','27'];
function changeSavings(){
    var savings_id = $("#savings_id").val();
    function_elements_add('savings_id', savings_id);
    if(index.includes($('#savings_id').val())){
            doDateCalc();
			showPeriod();	
			} else {
			hidePeriod();
			}
            console.log(needPeriod);
    $.ajax({
        type: "POST",
        url : "{{route('savings-account.get-savings-interest-rate')}}",
        dataType: "html",
        data: {
            'savings_id'    : savings_id,
            '_token'        : '{{csrf_token()}}',
        },
        success: function(return_data){ 
            $('#savings_interest_rate').val(return_data);
            function_elements_add('savings_interest_rate', return_data);
        },
        error: function(data)
        {
            console.log(data);
        }
    });
}
function doDateCalc(){
    var date2 	= document.getElementById("savings_account_date").value;
    var day2 	= date2.substring(0, 2);
    var month2 	= date2.substring(3, 5);
    var year2 	= date2.substring(6, 10);
    var date 	= year2 + '-' + month2 + '-' + day2;
    var date1	= new Date(date);
    var period 	= document.getElementById("saving_account_period").value;
    function_elements_add('saving_account_period', period);
			var a 		= moment(date1); 
			var b 		= a.add(period, 'month'); 
			var tmp 	= date1.setMonth(date1.getMonth() + period);
			var endDate = new Date(tmp);
			var name 	= 'savings_account_pickup_date';
			var value 	= b.format('DD-MM-YYYY');			
			var testDate 	= new Date(date);
			var tmp2 		= testDate.setMonth(testDate.getMonth() + 1);
			var date_first 	= testDate.toISOString();
			var day2 		= date_first.substring(8, 10);
			var month2 		= date_first.substring(5, 7);
			var year2 		= date_first.substring(0, 4); 
			var first 		= day2 + '-' + month2 + '-' + year2;
			$('#savings_account_pickup_date').val(b.format('DD-MM-YYYY'));
			function_elements_add(name, value);
}
$(document).ready(function(){
		if(index.includes($('#savings_id').val())){
            doDateCalc();
			showPeriod();	
			} else {
			hidePeriod();
			}
	});

$('#saving_account_period').change(function(){doDateCalc();});
</script>
@endsection
<?php 
if(empty($sessiondata)){
    $sessiondata['savings_id']                              = null;
    $sessiondata['savings_interest_rate']                   = null;
    $sessiondata['office_id']                               = null;
    $sessiondata['savings_account_first_deposit_amount']    = 0;
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
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Tabungan') }}</h3>
            </div>
            <a href="{{ route('savings-account.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}
            </a>
        </div>

        <div id="kt_savings_account_view">
            <form id="kt_savings_account_add_view_form" class="form" method="POST" action="{{ route('savings-account.process-add') }}" enctype="multipart/form-data">
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
                                    <button type="button" id="open_modal_button" class="btn btn-primary">
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
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Ahli Waris') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_member_heir" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('savings_member_heir', $coremember['member_heir'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Hub Keluarga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="savings_member_heir_relationship" id="savings_member_heir_relationship" aria-label="{{ __('Pilih Hubungan') }}" data-control="select2" data-placeholder="{{ __('Pilih hubungan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                        <option value="">{{ __('Pilih hubungan..') }}</option>
                                        @foreach($familyrelationship as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('savings_member_heir_relationship', (int)$coremember['member_heir_relationship'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="savings_member_heir_address" name="savings_member_heir_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Sesuai KTP">{{ old('savings_member_heir_address', $coremember['member_heir_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Tabungan') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jenis Tabungan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="savings_id" id="savings_id" aria-label="{{ __('Jenis Tabungan') }}" data-control="select2" data-placeholder="{{ __('Pilih tabungan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select2-hidden-accessible" onchange="changeSavings()">
                                        <option value="">{{ __('Pilih tabungan..') }}</option>
                                        @foreach($acctsavings as $key => $value)
                                            <option data-kt-flag="{{ $value['savings_id'] }}" value="{{ $value['savings_id'] }}" {{ $value['savings_id'] === old('savings_id', (int)$sessiondata['savings_id'] ?? '') ? 'selected' :'' }}>{{ $value['savings_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Buka') }}</label>
                                <div class="col-lg-8 fv-row">
                                    {{-- <input name="savings_account_date" id="savings_account_date" class="date form-control form-control-solid form-select-lg" placeholder="Pilih tanggal" value="{{ old('savings_account_date', $sessiondata['savings_account_date'] ?? '') }}"/> --}}
                                    <input type="text" name="savings_account_date" id="savings_account_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Buka" value="{{ date('d-m-Y') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6" id="period_date_input" hidden>
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Masa Tabungan (Bulan)') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="number" min="0" name="saving_account_period" id="saving_account_period" class="form-control form-control-lg form-control-solid" placeholder="Masa Tabungan" value="{{ old('saving_account_period', $sessiondata['saving_account_period'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6" id="pickup_date_input" >
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Ambill') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_account_pickup_date" id="savings_account_pickup_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Buka" value="{{ date('d-m-Y') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Bunga Per Bulan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_interest_rate" id="savings_interest_rate" class="form-control form-control-lg form-control-solid" placeholder="%" value="{{ old('savings_interest_rate', $sessiondata['savings_interest_rate'] ?? '') }}" autocomplete="off" readonly/>
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
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Setoran') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_account_first_deposit_amount_view" id="savings_account_first_deposit_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Setoran" value="{{ old('savings_account_first_deposit_amount_view', number_format($sessiondata['savings_account_first_deposit_amount'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="savings_account_first_deposit_amount" id="savings_account_first_deposit_amount" class="form-control form-control-lg form-control-solid" placeholder="Setoran" value="{{ old('savings_account_first_deposit_amount', $sessiondata['savings_account_first_deposit_amount'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_savings_account_add_submit">
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
    
                <div class="modal-body" id="modal-body">
                </div>
    
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>