@section('scripts')
<script>

const form = document.getElementById('kt_savings_bank_mutation_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'savings_account_id': {
                validators: {
                    notEmpty: {
                        message: 'Tabungan harus diisi'
                    }
                }
            },
            'bank_account_id': {
                validators: {
                    notEmpty: {
                        message: 'Bank Transfer harus diisi'
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
            'savings_bank_mutation_amount_view': {
                validators: {
                    notEmpty: {
                        message: 'Jumlah Transaksi harus diisi'
                    }
                }
            },
            'savings_bank_mutation_date': {
                validators: {
                    notEmpty: {
                        message: 'Tanggal Mutasi harus diisi'
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

$(form.querySelector('[name="mutation_id"]')).on('change', function () {
    validator.revalidateField('mutation_id');
});

$(form.querySelector('[name="bank_account_id"]')).on('change', function () {
    validator.revalidateField('bank_account_id');
});

const submitButton = document.getElementById('kt_savings_bank_mutation_add_submit');
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
    $('#open_modal_button').click(function(){
        $.ajax({
            type: "GET",
            url : "{{route('savings-bank-mutation.modal-savings-account')}}",
            success: function(msg){
                $('#kt_modal_savings_account').modal('show');
                $('#modal-body').html(msg);
            }
        });
    });
    
    $("#savings_bank_mutation_amount_view").change(function(){
        var savings_bank_mutation_amount                                    = $("#savings_bank_mutation_amount_view").val();
        document.getElementById("savings_bank_mutation_amount").value       = savings_bank_mutation_amount;
        document.getElementById("savings_bank_mutation_amount_view").value  = toRp(savings_bank_mutation_amount);
        function_elements_add('savings_bank_mutation_amount', savings_bank_mutation_amount);
        calculate();
    });
    
    $("#savings_bank_mutation_amount_adm_view").change(function(){
        var savings_bank_mutation_amount_adm                                    = $("#savings_bank_mutation_amount_adm_view").val();
        document.getElementById("savings_bank_mutation_amount_adm").value       = savings_bank_mutation_amount_adm;
        document.getElementById("savings_bank_mutation_amount_adm_view").value  = toRp(savings_bank_mutation_amount_adm);
        function_elements_add('savings_bank_mutation_amount_adm', savings_bank_mutation_amount_adm);
        calculate();
    });

    calculate();
});

function function_elements_add(name, value){
    $.ajax({
        type: "POST",
        url : "{{route('savings-bank-mutation.elements-add')}}",
        data : {
            'name'      : name, 
            'value'     : value,
            '_token'    : '{{csrf_token()}}'
        },
        success: function(msg){
        }
    });
}

function calculate(){
    var mutation_id                         = $("#mutation_id").val();
    var savings_bank_mutation_amount        = $("#savings_bank_mutation_amount").val();
    var savings_bank_mutation_amount_adm    = $("#savings_bank_mutation_amount_adm").val();
    var savings_account_last_balance        = $("#savings_account_last_balance").val();

    if(mutation_id == 7){
        var savings_bank_mutation_last_balance  = parseFloat(savings_account_last_balance) + parseFloat(savings_bank_mutation_amount);
    }else{
        var savings_bank_mutation_last_balance  = parseFloat(savings_account_last_balance) - parseFloat(savings_bank_mutation_amount);
    }

    document.getElementById("savings_bank_mutation_last_balance").value = savings_bank_mutation_last_balance;
    document.getElementById("savings_bank_mutation_last_balance_view").value = toRp(savings_bank_mutation_last_balance);
    function_elements_add('savings_bank_mutation_last_balance', savings_bank_mutation_last_balance);
    function_elements_add('mutation_id', mutation_id);
}
</script>
@endsection
<?php 
if(!isset($acctsavingsaccount['savings_account_last_balance'])){
    $acctsavingsaccount['savings_account_last_balance']  = 0;
}
if(empty($sessiondata)){
    $sessiondata['bank_account_id']                     = null;
    $sessiondata['mutation_id']                         = null;
    $sessiondata['savings_bank_mutation_amount']        = 0;
    $sessiondata['savings_bank_mutation_amount_adm']    = 0;
    $sessiondata['savings_bank_mutation_last_balance']  = 0;
}
?>
<x-base-layout>
    <div class="card">
        <div class="card-header">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Mutasi Tabungan Via Bank') }}</h3>
            </div>
            <a href="{{ route('savings-bank-mutation.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}
            </a>
        </div>

        <div id="kt_savings_bank_mutation_view">
            <form id="kt_savings_bank_mutation_add_view_form" class="form" method="POST" action="{{ route('savings-bank-mutation.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Tabssungan') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No Rek Tabungan') }}</label>
                                <div class="col-lg-5 fv-row">
                                    <input type="hidden" name="savings_account_id" class="form-control form-control-lg form-control-solid" placeholder="No Rek Tabungan" value="{{ old('savings_account_id', $acctsavingsaccount['savings_account_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="text" name="savings_account_no" class="form-control form-control-lg form-control-solid" placeholder="No Rek Tabungan" value="{{ old('savings_account_no', $acctsavingsaccount['savings_account_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                                <div class="col-lg-3 fv-row">
                                    <button type="button" id="open_modal_button" class="btn btn-primary">
                                        {{ __('Cari Tabungan') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Tabungan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_name" class="form-control form-control-lg form-control-solid" placeholder="Jenis Tabungan" value="{{ old('savings_name', $acctsavingsaccount['savings_name'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="savings_id" class="form-control form-control-lg form-control-solid" placeholder="Jenis Tabungan" value="{{ old('savings_id', $acctsavingsaccount['savings_id'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Anggota') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_name', $acctsavingsaccount['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="member_id" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_id', $acctsavingsaccount['member_id'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="member_address" name="member_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Sesuai KTP" readonly>{{ old('member_address', $acctsavingsaccount['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kabupaten') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="city_name" class="form-control form-control-lg form-control-solid" placeholder="Kabupaten" value="{{ old('city_name', $acctsavingsaccount['city_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kecamatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="kecamatan_name" class="form-control form-control-lg form-control-solid" placeholder="Kecamatan" value="{{ old('kecamatan_name', $acctsavingsaccount['kecamatan_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Ibu') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_mother" class="form-control form-control-lg form-control-solid" placeholder="Nama Ibu" value="{{ old('member_mother', $acctsavingsaccount['member_mother'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Identitas') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="No Identitas" value="{{ old('member_identity_no', $acctsavingsaccount['member_identity_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Mutasi') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Bank Transfer') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="bank_account_id" id="bank_account_id" aria-label="{{ __('Bank Transfer') }}" data-control="select2" data-placeholder="{{ __('Pilih bank transfer..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select2-hidden-accessible" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih bank transfer..') }}</option>
                                        @foreach($acctbankaccount as $key => $value)
                                            <option data-kt-flag="{{ $value['bank_account_id'] }}" value="{{ $value['bank_account_id'] }}" {{ $value['bank_account_id'] === old('bank_account_id', (int)$sessiondata['bank_account_id'] ?? '') ? 'selected' :'' }}>{{ $value['bank_account_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @php  if( strtotime($acctsavingsaccount['savings_account_pickup_date'] ?? '') > strtotime('now') && $acctsavingsaccount['unblock_state'] == 0) {
                                        if (($key=  $acctmutation->where('mutation_name','Pengambilan Via Bank')->pluck('mutation_id')) != false) {
                                            $acctmutation = $acctmutation->except($key[0]);
										} 
									}  @endphp
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Sandi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="mutation_id" id="mutation_id" aria-label="{{ __('Sandi') }}" data-control="select2" data-placeholder="{{ __('Pilih sandi..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select2-hidden-accessible" onchange="calculate()">
                                        <option value="">{{ __('Pilih sandi..') }}</option>
                                        @foreach($acctmutation as $key => $value)
                                            <option data-kt-flag="{{ $value['mutation_id'] }}" value="{{ $value['mutation_id'] }}" {{ $value['mutation_id'] === old('mutation_id', (int)$sessiondata['mutation_id'] ?? '') ? 'selected' :'' }}>{{ $value['mutation_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo Lama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_account_last_balance_view" id="savings_account_last_balance_view" class="form-control form-control-lg form-control-solid" placeholder="Saldo Lama" value="{{ old('savings_account_last_balance_view', number_format($acctsavingsaccount['savings_account_last_balance'], 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="savings_account_last_balance" id="savings_account_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Lama" value="{{ old('savings_account_last_balance', $acctsavingsaccount['savings_account_last_balance'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jumlah Transaksi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_bank_mutation_amount_view" id="savings_bank_mutation_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('savings_bank_mutation_amount_view', number_format($sessiondata['savings_bank_mutation_amount'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="savings_bank_mutation_amount" id="savings_bank_mutation_amount" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('savings_bank_mutation_amount', $sessiondata['savings_bank_mutation_amount'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Administrasi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_bank_mutation_amount_adm_view" id="savings_bank_mutation_amount_adm_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('savings_bank_mutation_amount_adm_view', number_format($sessiondata['savings_bank_mutation_amount_adm'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="savings_bank_mutation_amount_adm" id="savings_bank_mutation_amount_adm" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('savings_bank_mutation_amount_adm', $sessiondata['savings_bank_mutation_amount_adm'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo Baru') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_bank_mutation_last_balance_view" id="savings_bank_mutation_last_balance_view" class="form-control form-control-lg form-control-solid" placeholder="Saldo Baru" value="{{ old('savings_bank_mutation_last_balance_view', number_format($sessiondata['savings_bank_mutation_last_balance'], 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="savings_bank_mutation_last_balance" id="savings_bank_mutation_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Baru" value="{{ old('savings_bank_mutation_last_balance', $sessiondata['savings_bank_mutation_last_balance'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Mutasi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_bank_mutation_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Mutasi" value="{{ date('d-m-Y') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6  ">{{ __('Keterangan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="savings_bank_mutation_remark" name="savings_bank_mutation_remark" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Keterangan" onchange="function_elements_add(this.name, this.value)">{{ old('savings_bank_mutation_remark', $sessiondata['savings_bank_mutation_remark'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_savings_bank_mutation_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_modal_savings_account">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Daftar Tabungan</h3>
    
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