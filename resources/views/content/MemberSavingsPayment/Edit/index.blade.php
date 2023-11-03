
@section('scripts')
<script>
const form = document.getElementById('kt_member_savings_payment_view_form');
var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'member_no': {
                validators: {
                    notEmpty: {
                        message: 'No. Anggota harus diisi'
                    }
                }
            },
            'member_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Anggota harus diisi'
                    }
                }
            },
            'mutation_id': {
                validators: {
                    notEmpty: {
                        message: 'Sandi harus diisi'
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
            'member_address': {
                validators: {
                    notEmpty: {
                        message: 'Alamat harus diisi'
                    }
                }
            },
            'member_principal_savings_last_balance': {
                validators: {
                    notEmpty: {
                        message: 'Saldo Simp Pokok harus diisi'
                    }
                }
            },
            'member_special_savings_last_balance': {
                validators: {
                    notEmpty: {
                        message: 'Saldo Simp Khusus harus diisi'
                    }
                }
            },
            'member_mandatory_savings_last_balance': {
                validators: {
                    notEmpty: {
                        message: 'Saldo Simp Wajib harus diisi'
                    }
                }
            },
            mutation :{
                selector: '.mutation-input',
                validators: {
                    callback: {
                        message: 'Setidaknya masukan satu mutasi',
                        callback: function(input) {
                            let isEmpty = true;
                            const emailElements = validator.getElements('mutation');
                            for (const i in emailElements) {
                                if (emailElements[i].value !== '') {
                                    isEmpty = false;
                                    break;
                                }
                            }

                            if (!isEmpty) {
                                // Update the status of callback validator for all fields
                                fv.updateFieldStatus('mutation', 'Valid', 'callback');
                                return true;
                            }

                            return false;
                        }
                    },
                },
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

const submitButton = document.getElementById('kt_member_savings_payment_submit');
submitButton.addEventListener('click', function (e) {
    e.preventDefault();

    if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                submitButton.setAttribute('data-kt-indicator', 'on');

                submitButton.disabled = true;

                setTimeout(function () {
                    submitButton.removeAttribute('data-kt-indicator');

                    form.submit(); // Submit form
                }, 2000);
            }
        });
    }
});

function mutation_function(){
    var arr = {  @foreach($acctmutation as $key => $value)  {{$value['mutation_id']}} : '{{$value['mutation_function']}}', @endforeach}
    return arr[$('#mutation_id').val()];
}
var al = true;
function calcMutation(viwe_el,el,last_balance,input_balance){
    if(mutation_function() == '+'){
			new_balance = parseFloat(last_balance) + parseFloat(input_balance==''?0:input_balance);
		} else if(mutation_function() == '-'){
			new_balance = parseFloat(last_balance) - parseFloat(input_balance==''?0:input_balance);
		} else {
            toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-center",
            "preventDuplicates": true,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "30000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
            }
            toastr["error"]("Sandi Masih Kosong !", "Peringatan")
				return false;
		}
        $(viwe_el).val(toRp(new_balance));
		$(el).val(new_balance);

}
    function calPrincipalSavings(member_principal_savings = 0){
		var member_principal_savings_last_balance	= $('#member_principal_savings_last_balance').val();
		var member_principal_savings_last_balance_origin= $('#member_principal_savings_last_balance_origin').val();
        calcMutation('#member_principal_savings_last_balance_view','#member_principal_savings_last_balance',member_principal_savings_last_balance_origin,member_principal_savings);
	}
	function calSpecialSavings(member_special_savings = 0){
		var member_special_savings_last_balance		= $('#member_special_savings_last_balance').val();
		var member_special_savings_last_balance_origin		= $('#member_special_savings_last_balance_origin').val();
        calcMutation('#member_special_savings_last_balance_view','#member_special_savings_last_balance',member_special_savings_last_balance_origin,member_special_savings);
    }

	function calMandatorySavings(member_mandatory_savings = 0){
		var member_mandatory_savings_last_balance	= $('#member_mandatory_savings_last_balance').val();
		var member_mandatory_savings_last_balance_origin	= $('#member_mandatory_savings_last_balance_origin').val();
        calcMutation('#member_mandatory_savings_last_balance_view','#member_mandatory_savings_last_balance',member_mandatory_savings_last_balance_origin,member_mandatory_savings);
	}
    function changeMutation(name= null,value=null){
        function_elements_add(name, value);

    }
$(document).ready(function(){
    if(mutation_function() == '+' || mutation_function() == '-'){
    calPrincipalSavings($('#member_principal_savings').val());
    calSpecialSavings($('#member_special_savings').val());
    calMandatorySavings( $('#member_mandatory_savings').val());
    }
    $('#button_modal').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('member-savings-payment.modal-member')}}",
                success: function(msg){
                    $('#kt_modal_core_member').modal('show');
                    $('#modal-body').html(msg);
            }

        });
    });

    $('#member_principal_savings_view').change(function(){
        var member_principal_savings = $('#member_principal_savings_view').val();
        if(member_principal_savings!=''&&Number.isInteger(parseInt(member_principal_savings))){
            calPrincipalSavings(member_principal_savings);
            calSpecialSavings($('#member_special_savings').val());
            calMandatorySavings( $('#member_mandatory_savings').val());
            function_elements_add('member_principal_savings', member_principal_savings);
            $('#member_principal_savings').val(member_principal_savings);
            $('#member_principal_savings_view').val(toRp(member_principal_savings));
        }else{
            $('#member_principal_savings_view').val('');
            $('#member_principal_savings').val('');
        }
    });

    $('#member_special_savings_view').change(function(){
        var member_special_savings = $('#member_special_savings_view').val();
        if(member_special_savings!=''&&Number.isInteger(parseInt(member_special_savings))){
        calSpecialSavings(member_special_savings);
        calPrincipalSavings($('#member_principal_savings').val());
        calMandatorySavings( $('#member_mandatory_savings').val());
        function_elements_add('member_special_savings', member_special_savings);
        $('#member_special_savings').val(member_special_savings);
        $('#member_special_savings_view').val(toRp(member_special_savings));
        }else{
            $('#member_special_savings_view').val('');
            $('#member_special_savings').val('');
        }
    });

    $('#member_mandatory_savings_view').change(function(){
        var member_mandatory_savings = $('#member_mandatory_savings_view').val();
        if(member_mandatory_savings!=''&&Number.isInteger(parseInt(member_mandatory_savings))){
        calMandatorySavings(member_mandatory_savings);
        calPrincipalSavings($('#member_principal_savings').val());
        calSpecialSavings($('#member_special_savings').val());
        function_elements_add('member_mandatory_savings', member_mandatory_savings);
        $('#member_mandatory_savings').val(member_mandatory_savings);
        $('#member_mandatory_savings_view').val(toRp(member_mandatory_savings));
        }else{
            $('#member_mandatory_savings_view').val('');
            $('#member_mandatory_savings').val('');
        }
    });

    $('#kt_member_savings_payment_reset').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('member-savings-payment.reset-elements-add')}}",
                success: function(msg){
                    location.reload();
            }

        });
    });

    var province_id     = <?php echo json_encode(empty($memberses['province_id']) ? '' : $memberses['province_id']) ?>;
    var city_id         = <?php echo json_encode(empty($memberses['city_id']) ? '' : $memberses['city_id']) ?>;
    var kecamatan_id    = <?php echo json_encode(empty($memberses['kecamatan_id']) ? '' : $memberses['kecamatan_id']) ?>;
    var kelurahan_id    = <?php echo json_encode(empty($memberses['kelurahan_id']) ? '' : $memberses['kelurahan_id']) ?>;

    if (province_id != '') {
        $.ajax({
                type: "POST",
                url : "{{route('dropdown.dropdown-city')}}",
                data : {
                    'province_id'   : province_id,
                    'city_id'       : city_id,
                    '_token'        : '{{csrf_token()}}'
                },
                success: function(msg){
                    $('#city_id').html(msg);
                }
        });
    }

    if (city_id != '') {
        $.ajax({
            type: "POST",
            url : "{{route('dropdown.dropdown-kecamatan')}}",
            data : {
                'kecamatan_id'  : kecamatan_id,
                'city_id'       : city_id,
                '_token'        : '{{csrf_token()}}'
            },
            success: function(msg){
                $('#kecamatan_id').html(msg);
            }
        });
    }
    if (kecamatan_id != '') {
        $.ajax({
            type: "POST",
            url : "{{route('dropdown.dropdown-kelurahan')}}",
            data : {
                'kecamatan_id'   : kecamatan_id,
                'kelurahan_id'   : kelurahan_id,
                '_token'         : '{{csrf_token()}}'
            },
            success: function(msg){
                $('#kelurahan_id').html(msg);
            }
        });
    }

    var message    = <?php echo json_encode(empty(session('message')) ? '' : session('message')) ?>;

    if (message.alert == 'success') {
        window.open("{{ url('member-savings-payment/process-printing') }}"+"/"+message.member_id,'_blank');
    }
});

function change_dropdown(name, id)
{
    var province_id     = <?php echo json_encode(empty($memberses['province_id']) ? '' : $memberses['province_id']) ?>;
    var city_id         = <?php echo json_encode(empty($memberses['city_id']) ? '' : $memberses['city_id']) ?>;
    var kecamatan_id    = <?php echo json_encode(empty($memberses['kecamatan_id']) ? '' : $memberses['kecamatan_id']) ?>;
    var kelurahan_id    = <?php echo json_encode(empty($memberses['kelurahan_id']) ? '' : $memberses['kelurahan_id']) ?>;

    if (name == 'province_id') {
        $('#city_id').html('');
        $('#kecamatan_id').html('');
        $('#kelurahan_id').html('');
        $.ajax({
                type: "POST",
                url : "{{route('dropdown.dropdown-city')}}",
                data : {
                    'province_id'    : id,
                    'city_id'        : city_id,
                    '_token'         : '{{csrf_token()}}',
                },
                success: function(msg){
                    $('#city_id').html(msg);
            }

        });
    } else if (name == 'city_id') {
        $('#kecamatan_id').html('');
        $('#kelurahan_id').html('');
        $.ajax({
                type: "POST",
                url : "{{route('dropdown.dropdown-kecamatan')}}",
                data : {
                    'city_id'        : id,
                    'kecamatan_id'   : kecamatan_id,
                    '_token'         : '{{csrf_token()}}'
                },
                success: function(msg){
                    $('#kecamatan_id').html(msg);
            }

        });
    } else if (name = 'kecamatan_id') {
        $('#kelurahan_id').html('');
        $.ajax({
                type: "POST",
                url : "{{route('dropdown.dropdown-kelurahan')}}",
                data : {
                    'kecamatan_id'   : id,
                    'kelurahan_id'   : kelurahan_id,
                    '_token'         : '{{csrf_token()}}'
                },
                success: function(msg){
                    $('#kelurahan_id').html(msg);
            }

        });
    }
}

function function_elements_add(name, value){
    $.ajax({
            type: "POST",
            url : "{{route('member-savings-payment.elements-add')}}",
            data : {
                'name'      : name,
                'value'     : value,
                '_token'    : '{{csrf_token()}}'
            },
            success: function(msg){
        }
    });
}

</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Ubah') }}</h3>
            </div>

            {{-- <a href="{{ route('member') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a> --}}
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_member_savings_payment_view_form" class="form" method="POST" action="{{ route('member-savings-payment.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Anggota') }}</label>
                                <div class="col-lg-4 fv-row">
                                    <input type="text" name="member_no" class="form-control form-control-lg form-control-solid" placeholder="No. Anggota" value="{{ old('member_no', $memberses['member_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                                <div class="col-lg-4">
                                    <button type="button" id="button_modal" class="btn btn-primary">
                                        {{ __('Cari Anggota') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Anggota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Anggota" value="{{ old('member_name', $memberses['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sifat Anggota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="member_character" id="member_character" data-control="select2" data-placeholder="{{ __('Pilih Sifat Anggota') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                        <option value="">{{ __('Pilih') }}</option>
                                        @foreach($membercharacter as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('member_character', $memberses['member_character'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Provinsi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="province_id" id="province_id" data-control="select2" data-placeholder="{{ __('Pilih Provinsi') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="change_dropdown(this.name, this.value)">
                                        <option value="">{{ __('Pilih') }}</option>
                                        @foreach($coreprovince as $key => $value)
                                            <option data-kt-flag="{{ $value['province_id'] }}" value="{{ $value['province_id'] }}" {{ $value['province_id'] == old('province_id', $memberses['province_id'] ?? '') ? 'selected' :'' }}>{{ $value['province_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kabupaten') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="city_id" id="city_id" data-control="select2" data-placeholder="{{ __('Pilih Kabupaten') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="change_dropdown(this.name, this.value)">
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kecamatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="kecamatan_id" id="kecamatan_id" data-control="select2" data-placeholder="{{ __('Pilih Kecamatan') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="change_dropdown(this.name, this.value)">
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kelurahan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="kelurahan_id" id="kelurahan_id" data-control="select2" data-placeholder="{{ __('Pilih Kelurahan') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea  name="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" autocomplete="off">{{ old('member_address', $memberses['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">Saldo</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Saldo Simp Pokok') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_principal_savings_last_balance_view" id="member_principal_savings_last_balance_view" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Pokok" value="{{ old('member_principal_savings_last_balance', empty($memberses['member_principal_savings_last_balance']) ? '' : number_format($memberses['member_principal_savings_last_balance'],2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="member_principal_savings_last_balance" id="member_principal_savings_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Pokok" value="{{ old('member_principal_savings_last_balance', empty($memberses['member_principal_savings_last_balance']) ? '' : number_format($memberses['member_principal_savings_last_balance'],2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="member_principal_savings_last_balance_origin" id="member_principal_savings_last_balance_origin" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Pokok" value="{{ old('member_principal_savings_last_balance', empty($memberses['member_principal_savings_last_balance']) ? '' : $memberses['member_principal_savings_last_balance'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Saldo Simp Wajib') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_mandatory_savings_last_balance_view" id="member_mandatory_savings_last_balance_view"class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Wajib" value="{{ old('member_mandatory_savings_last_balance', empty($memberses['member_mandatory_savings_last_balance']) ? '' : number_format($memberses['member_mandatory_savings_last_balance'],2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="member_mandatory_savings_last_balance" id="member_mandatory_savings_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Wajib" value="{{ old('member_mandatory_savings_last_balance', empty($memberses['member_mandatory_savings_last_balance']) ? '' : number_format($memberses['member_mandatory_savings_last_balance'],2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="member_mandatory_savings_last_balance_origin" id="member_mandatory_savings_last_balance_origin" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Wajib" value="{{ old('member_mandatory_savings_last_balance', empty($memberses['member_mandatory_savings_last_balance']) ? '' : $memberses['member_mandatory_savings_last_balance'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Saldo Simp Khusus') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_special_savings_last_balance_view" id="member_special_savings_last_balance_view" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Khusus" value="{{ old('member_special_savings_last_balance', empty($memberses['member_special_savings_last_balance']) ? '' : number_format($memberses['member_special_savings_last_balance'],2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="member_special_savings_last_balance" id="member_special_savings_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Khusus" value="{{ old('member_special_savings_last_balance', empty($memberses['member_special_savings_last_balance']) ? '' : number_format($memberses['member_special_savings_last_balance'],2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="member_special_savings_last_balance_origin" id="member_special_savings_last_balance_origin" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Khusus" value="{{ old('member_special_savings_last_balance', empty($memberses['member_special_savings_last_balance']) ? '' : $memberses['member_special_savings_last_balance'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">Input Simpanan</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Sandi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="mutation_id" id="mutation_id" data-control="select2" data-placeholder="{{ __('Pilih Sandi') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg " required onchange="changeMutation(this.name, this.value)">
                                        <option value="">{{ __('Pilih') }}</option>
                                        @foreach($acctmutation as $key => $value)
                                            <option data-kt-flag="{{ $value['mutation_id'] }}" value="{{ $value['mutation_id'] }}" {{ $value['mutation_id'] == old('mutation_id', empty($datases['mutation_id']) ? '' : $datases['mutation_id'] ?? '') ? 'selected' :'' }}>{{ $value['mutation_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Simpanan Pokok') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_principal_savings_view" id="member_principal_savings_view" class="form-control mutation-input form-control-lg form-control-solid" placeholder="Simpanan Pokok" value="{{ old('member_principal_savings_view', empty($datases['member_principal_savings']) ? '' : number_format($datases['member_principal_savings'],2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="member_principal_savings" id="member_principal_savings" class="form-control form-control-lg form-control-solid" placeholder="Simpanan Pokok" value="{{ old('member_principal_savings', empty($datases['member_principal_savings']) ? '' : $datases['member_principal_savings'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Simpanan Wajib') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_mandatory_savings_view" id="member_mandatory_savings_view" class="form-control mutation-input form-control-lg form-control-solid" placeholder="Simpanan Wajib" value="{{ old('member_mandatory_savings_view', empty($datases['member_mandatory_savings']) ? '' : number_format($datases['member_mandatory_savings'],2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="member_mandatory_savings" id="member_mandatory_savings" class="form-control form-control-lg form-control-solid" placeholder="Simpanan Wajib" value="{{ old('member_mandatory_savings', empty($datases['member_mandatory_savings']) ? '' : $datases['member_mandatory_savings'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Simpanan Khusus') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_special_savings_view" id="member_special_savings_view" class="form-control mutation-input form-control-lg form-control-solid" placeholder="Simpanan Khusus" value="{{ old('member_special_savings_view', empty($datases['member_special_savings']) ? '' : number_format($datases['member_special_savings'],2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="member_special_savings" id="member_special_savings" class="form-control form-control-lg form-control-solid" placeholder="Simpanan Khusus" value="{{ old('member_special_savings', empty($datases['member_special_savings']) ? '' : $datases['member_special_savings'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" id="kt_member_savings_payment_reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>

                    <button type="submit" class="btn btn-primary" id="kt_member_savings_payment_submit">
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

                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="bi bi-x-lg"></span>
                    </div>
                    <!--end::Close-->
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