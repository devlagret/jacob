@section('scripts')
<script>

const form = document.getElementById('kt_credits_acquittance_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'credits_account_id': {
                validators: {
                    notEmpty: {
                        message: 'No akad pinjaman harus diisi'
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

const submitButton = document.getElementById('kt_credits_acquittance_add_submit');
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
            url : "{{route('credits-acquittance.modal-credits-account')}}",
            success: function(msg){
                $('#kt_modal_credits_account').modal('show');
                $('#modal-body').html(msg);
            }
        });
    });
    
    $("#credits_acquittance_principal_view").change(function(){
        var credits_acquittance_principal                                    = $("#credits_acquittance_principal_view").val();
        document.getElementById("credits_acquittance_principal").value       = credits_acquittance_principal;
        document.getElementById("credits_acquittance_principal_view").value  = toRp(credits_acquittance_principal);
        function_elements_add('credits_acquittance_principal', credits_acquittance_principal);
        calculate();
    });
    
    $("#credits_acquittance_interest_view").change(function(){
        var credits_acquittance_interest                                    = $("#credits_acquittance_interest_view").val();
        document.getElementById("credits_acquittance_interest").value       = credits_acquittance_interest;
        document.getElementById("credits_acquittance_interest_view").value  = toRp(credits_acquittance_interest);
        function_elements_add('credits_acquittance_interest', credits_acquittance_interest);
        calculate();
    });
    
    $("#credits_acquittance_fine_view").change(function(){
        var credits_acquittance_fine                                    = $("#credits_acquittance_fine_view").val();
        document.getElementById("credits_acquittance_fine").value       = credits_acquittance_fine;
        document.getElementById("credits_acquittance_fine_view").value  = toRp(credits_acquittance_fine);
        function_elements_add('credits_acquittance_fine', credits_acquittance_fine);
        calculate();
    });

    $("#penalty").change(function(){
        var penalty = $("#penalty").val();
        function_elements_add('penalty', penalty);
        calculate();
    });

    calculate();
});

function function_elements_add(name, value){
    $.ajax({
        type: "POST",
        url : "{{route('credits-acquittance.elements-add')}}",
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
    var penalty_type_id                     = $("#penalty_type_id").val();
    var penalty                             = $("#penalty").val();
    var credits_acquittance_principal       = $("#credits_acquittance_principal").val();
    var credits_acquittance_interest        = $("#credits_acquittance_interest").val();
    var credits_acquittance_fine            = $("#credits_acquittance_fine").val();

    var credits_account_last_balance        = $("#credits_account_last_balance").val();
    var credits_account_interest            = $("#credits_account_interest").val();
    var credits_account_interest_amount     = $("#credits_account_interest_amount").val();
    var credits_account_payment_amount      = $("#credits_account_payment_amount").val();
    var payment_type_id                     = $("#payment_type_id").val();
    
    if(penalty_type_id == 0 || penalty_type_id == '' || penalty_type_id == null){
        var credits_acquittance_penalty = 0;
    } else if(penalty_type_id == 1){
        var credits_acquittance_penalty = (parseFloat(credits_acquittance_principal) * parseFloat(penalty)) / 100;
    } else if(penalty_type_id == 2){
        if(payment_type_id == 1){
            var credits_acquittance_penalty = parseFloat(credits_account_interest_amount) * parseFloat(penalty) ;
        } else {
            var i;
            var credits_acquittance_penalty;
            var bunga = parseFloat(credits_account_interest) / 100;
            var sisapinjaman = parseFloat(credits_account_last_balance);

            for (i = 1; i <= penalty; i++) { 
                var angsuranbunga 		    = parseFloat(sisapinjaman) * bunga;
                var angsuranpokok 		    = parseFloat(credits_account_payment_amount) - angsuranbunga;
                var sisapokok 			    = parseFloat(sisapinjaman) - parseFloat(angsuranpokok);
                sisapinjaman 			    = sisapinjaman - angsuranpokok;
                credits_acquittance_penalty = parseFloat(credits_acquittance_penalty) + parseFloat(angsuranbunga);
            }
        }
    }

    credits_acquittance_amount = parseFloat(credits_acquittance_principal) + parseFloat(credits_acquittance_interest) + parseFloat(credits_acquittance_fine) + parseFloat(credits_acquittance_penalty);

    document.getElementById("credits_acquittance_penalty").value        = credits_acquittance_penalty;
    document.getElementById("credits_acquittance_penalty_view").value   = toRp(credits_acquittance_penalty);
    document.getElementById("credits_acquittance_amount").value         = credits_acquittance_amount;
    document.getElementById("credits_acquittance_amount_view").value    = toRp(credits_acquittance_amount);
    function_elements_add('credits_acquittance_amount', credits_acquittance_amount);
    function_elements_add('credits_acquittance_penalty', credits_acquittance_penalty);
    function_elements_add('penalty_type_id', penalty_type_id);
}
</script>
@endsection
<?php 
if(!isset($acctcreditsaccount['credits_account_interest_last_balance'])){
    $acctcreditsaccount['credits_account_interest_last_balance']  = 0;
}
if(!isset($acctcreditsaccount['credits_account_last_balance'])){
    $acctcreditsaccount['credits_account_last_balance']  = 0;
}
if(!isset($acctcreditsaccount['credits_account_accumulated_fines'])){
    $acctcreditsaccount['credits_account_accumulated_fines']  = 0;
}
if(empty($sessiondata)){
    $sessiondata['penalty_type_id']                         = null;
    $sessiondata['credits_acquittance_interest']            = 0;
    $sessiondata['credits_acquittance_fine']                = 0;
    $sessiondata['credits_acquittance_penalty']             = 0;
    $sessiondata['credits_acquittance_amount']              = 0;
    $sessiondata['penalty']                                 = 0;
}
?>
<x-base-layout>
    <div class="card">
        <div class="card-header">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Pelunasan Pinjaman') }}</h3>
            </div>
            <a href="{{ route('credits-acquittance.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}
            </a>
        </div>

        <div id="kt_credits_acquittance_view">
            <form id="kt_credits_acquittance_add_view_form" class="form" method="POST" action="{{ route('credits-acquittance.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Pinjaman') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Perjanjian Kredit') }}</label>
                                <div class="col-lg-5 fv-row">
                                    <input type="text" name="credits_account_serial" class="form-control form-control-lg form-control-solid" placeholder="No Akad Pinjaman" value="{{ old('credits_account_serial', $acctcreditsaccount['credits_account_serial'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_id" class="form-control form-control-lg form-control-solid" placeholder="No Akad Pinjaman" value="{{ old('credits_account_id', $acctcreditsaccount['credits_account_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_interest_amount" id="credits_account_interest_amount" class="form-control form-control-lg form-control-solid" placeholder="No Akad Pinjaman" value="{{ old('credits_account_interest_amount', $acctcreditsaccount['credits_account_interest_amount'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_payment_amount" id="credits_account_payment_amount" class="form-control form-control-lg form-control-solid" placeholder="No Akad Pinjaman" value="{{ old('credits_account_payment_amount', $acctcreditsaccount['credits_account_payment_amount'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="payment_type_id" id="payment_type_id" class="form-control form-control-lg form-control-solid" placeholder="No Akad Pinjaman" value="{{ old('payment_type_id', $acctcreditsaccount['payment_type_id'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                                <div class="col-lg-3 fv-row">
                                    <button type="button" id="open_modal_button" class="btn btn-primary">
                                        {{ __('Cari Pinjaman') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Pinjaman') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_name" class="form-control form-control-lg form-control-solid" placeholder="Jenis Pinjaman" value="{{ old('credits_name', $acctcreditsaccount['credits_name'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_id" class="form-control form-control-lg form-control-solid" placeholder="Jenis Pinjaman" value="{{ old('credits_id', $acctcreditsaccount['credits_id'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Realisasi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Realisasi" value="{{ old('credits_account_date', $acctcreditsaccount['credits_account_date'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jatuh Tempo') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_payment_date" class="form-control form-control-lg form-control-solid" placeholder="Jatuh Tempo" value="{{ old('credits_account_payment_date', $acctcreditsaccount['credits_account_payment_date'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Pelunasan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_acquittance_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Pelunasan" value="{{ date('d-m-Y') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Total Angsuran') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_payment_to" class="form-control form-control-lg form-control-solid" placeholder="Total Angsuran" value="{{ old('credits_account_payment_to', $acctcreditsaccount['credits_account_payment_to'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jangka Waktu') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka Waktu" value="{{ old('credits_account_period', $acctcreditsaccount['credits_account_period'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Anggota') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_name', $acctcreditsaccount['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="member_id" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_id', $acctcreditsaccount['member_id'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="member_address" name="member_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Sesuai KTP" readonly>{{ old('member_address', $acctcreditsaccount['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Pelunasan') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sisa Pokok') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_last_balance_view" id="credits_account_last_balance_view" class="form-control form-control-lg form-control-solid" placeholder="Sisa Pokok" value="{{ old('credits_account_last_balance_view', number_format($acctcreditsaccount['credits_account_last_balance'], 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_last_balance" id="credits_account_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Sisa Pokok" value="{{ old('credits_account_last_balance', $acctcreditsaccount['credits_account_last_balance'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sisa Bunga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_interest_last_balance_view" id="credits_account_interest_last_balance_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_account_interest_last_balance_view', number_format($acctcreditsaccount['credits_account_interest_last_balance'], 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_interest_last_balance" id="credits_account_interest_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_account_interest_last_balance', $acctcreditsaccount['credits_account_interest_last_balance'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Akumulasi Sanksi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_accumulated_fines_view" id="credits_account_accumulated_fines_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_account_accumulated_fines_view', number_format($acctcreditsaccount['credits_account_accumulated_fines'], 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_accumulated_fines" id="credits_account_accumulated_fines" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_account_accumulated_fines', $acctcreditsaccount['credits_account_accumulated_fines'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Pelunasan Pokok') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_acquittance_principal_view" id="credits_acquittance_principal_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_acquittance_principal_view', number_format($acctcreditsaccount['credits_account_last_balance'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="credits_acquittance_principal" id="credits_acquittance_principal" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_acquittance_principal', $acctcreditsaccount['credits_account_last_balance'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Pelunasan Bunga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_acquittance_interest_view" id="credits_acquittance_interest_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_acquittance_interest_view', number_format($sessiondata['credits_acquittance_interest'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="credits_acquittance_interest" id="credits_acquittance_interest" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_acquittance_interest', $sessiondata['credits_acquittance_interest'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Pelunasan Sanksi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_acquittance_fine_view" id="credits_acquittance_fine_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_acquittance_fine_view', number_format($sessiondata['credits_acquittance_fine'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="credits_acquittance_fine" id="credits_acquittance_fine" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_acquittance_fine', $sessiondata['credits_acquittance_fine'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tipe Penalti') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="penalty_type_id" id="penalty_type_id" aria-label="{{ __('Penalti') }}" data-control="select2" data-placeholder="{{ __('Pilih penalti..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select2-hidden-accessible" onchange="calculate()">
                                        <option value="">{{ __('Pilih penalti..') }}</option>
                                        @foreach($penaltytype as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('penalty_type_id', (int)$sessiondata['penalty_type_id'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Persentase Penalti') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="penalty" id="penalty" class="form-control form-control-lg form-control-solid" placeholder="%" value="{{ old('penalty', $sessiondata['penalty'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah Penalti') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_acquittance_penalty_view" id="credits_acquittance_penalty_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_acquittance_penalty_view', number_format($sessiondata['credits_acquittance_penalty'], 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_acquittance_penalty" id="credits_acquittance_penalty" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_acquittance_penalty', $sessiondata['credits_acquittance_penalty'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Total Pelunasan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_acquittance_amount_view" id="credits_acquittance_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_acquittance_amount_view', number_format($sessiondata['credits_acquittance_amount'], 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_acquittance_amount" id="credits_acquittance_amount" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_acquittance_amount', $sessiondata['credits_acquittance_amount'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_credits_acquittance_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>

    <br/>
    <br/>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Daftar Angsuran') }}</h3>
            </div>
        </div>

        <div id="kt_payment_list_view">
            <div class="card-body border-top p-9">
                <div class="table-responsive">
                    <div class="row mb-12">
                        <table class="table table-rounded border gy-7 gs-7 show-border">
                            <thead>
                                <tr align="center">
                                    <th><b>Ke</b></th>
                                    <th><b>Tanggal Angsuran</b></th>
                                    <th><b>Angsuran Pokok</b></th>
                                    <th><b>Angsuran Bunga</b></th>
                                    <th><b>Saldo Pokok</b></th>
                                    <th><b>Saldo Bunga</b></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                @foreach($acctcreditspayment as $key => $val)
                                    <tr>
                                        <th style="text-align: center">{{ $no }}</th>
                                        <th>{{ date('d-m-Y', strtotime($val['credits_payment_date'])) }}</th>
                                        <th style="text-align: right">{{ number_format($val['credits_payment_principal'], 2) }}</th>
                                        <th style="text-align: right">{{ number_format($val['credits_payment_interest'], 2) }}</th>
                                        <th style="text-align: right">{{ number_format($val['credits_principal_last_balance'], 2) }}</th>
                                        <th style="text-align: right">{{ number_format($val['credits_interest_last_balance'], 2) }}</th>
                                    </tr>
                                <?php $no++ ?>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_modal_credits_account">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Daftar Pinjaman</h3>
    
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