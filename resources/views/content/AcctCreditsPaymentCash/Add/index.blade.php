@section('scripts')
<script>

const form = document.getElementById('kt_credits_payment_cash_view_form');

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

const submitButton = document.getElementById('kt_credits_payment_cash_submit');
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
            url : "{{route('credits-payment-cash.modal-credits-account')}}",
            success: function(msg){
                $('#kt_modal_credits_account').modal('show');
                $('#modal-body').html(msg);
            }
        });
    });
    
    $("#angsuran_pokok_view").change(function(){
        var angsuran_pokok                                    = $("#angsuran_pokok_view").val();
        document.getElementById("angsuran_pokok").value       = angsuran_pokok;
        document.getElementById("angsuran_pokok_view").value  = toRp(angsuran_pokok);
        calculate();
    });
    
    $("#angsuran_bunga_view").change(function(){
        var angsuran_bunga                                    = $("#angsuran_bunga_view").val();
        document.getElementById("angsuran_bunga").value       = angsuran_bunga;
        document.getElementById("angsuran_bunga_view").value  = toRp(angsuran_bunga);
        calculate();
    });
    
    $("#credits_payment_fine_view").change(function(){
        var credits_payment_fine                                    = $("#credits_payment_fine_view").val();
        document.getElementById("credits_payment_fine").value       = credits_payment_fine;
        document.getElementById("credits_payment_fine_view").value  = toRp(credits_payment_fine);
        function_elements_add('credits_payment_fine', credits_payment_fine);
        calculate();
    });
    
    $("#others_income_view").change(function(){
        var others_income                                    = $("#others_income_view").val();
        document.getElementById("others_income").value       = others_income;
        document.getElementById("others_income_view").value  = toRp(others_income);
        function_elements_add('others_income', others_income);
        calculate();
    });
    
    $("#member_mandatory_savings_view").change(function(){
        var member_mandatory_savings                                    = $("#member_mandatory_savings_view").val();
        document.getElementById("member_mandatory_savings").value       = member_mandatory_savings;
        document.getElementById("member_mandatory_savings_view").value  = toRp(member_mandatory_savings);
        function_elements_add('member_mandatory_savings', member_mandatory_savings);
        calculate();
    });

    calculate();
});

function function_elements_add(name, value){
    $.ajax({
        type: "POST",
        url : "{{route('credits-payment-cash.elements-add')}}",
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
    var angsuran_pokok              = $("#angsuran_pokok").val();
    var angsuran_bunga              = $("#angsuran_bunga").val();
    var credits_payment_fine        = $("#credits_payment_fine").val();
    var others_income               = $("#others_income").val();
    var member_mandatory_savings    = $("#member_mandatory_savings").val();
    
    angsuran_total = parseFloat(angsuran_pokok) + parseFloat(angsuran_bunga) + parseFloat(credits_payment_fine) + parseFloat(others_income) + parseFloat(member_mandatory_savings);

    document.getElementById("angsuran_total").value         = angsuran_total;
    document.getElementById("angsuran_total_view").value    = toRp(angsuran_total);
    function_elements_add('angsuran_total', angsuran_total);
}
</script>
@endsection
<?php 
if(!isset($acctcreditsaccount['credits_account_amount'])){
    $acctcreditsaccount['credits_account_amount']  = 0;
}
if(!isset($acctcreditsaccount['credits_account_last_balance'])){
    $acctcreditsaccount['credits_account_last_balance']  = 0;
}
if(!isset($acctcreditsaccount['credits_account_interest_last_balance'])){
    $acctcreditsaccount['credits_account_interest_last_balance']  = 0;
}
if(!isset($acctcreditsaccount['payment_type_id'])){
    $acctcreditsaccount['payment_type_id']  = 1;
}
if(!isset($acctcreditsaccount['credits_account_date'])){
    $acctcreditsaccount['credits_account_date']  = null;
}else{
    $acctcreditsaccount['credits_account_date']  = date('d-m-Y', strtotime($acctcreditsaccount['credits_account_date']));
}
if(!isset($acctcreditsaccount['credits_account_payment_date'])){
    $acctcreditsaccount['credits_account_payment_date']  = null;
}else{
    $acctcreditsaccount['credits_account_payment_date']  = date('d-m-Y', strtotime($acctcreditsaccount['credits_account_payment_date']));
}
if(empty($sessiondata)){
    $sessiondata['credits_payment_fine']        = 0;
    $sessiondata['others_income']               = 0;
    $sessiondata['member_mandatory_savings']    = 0;
    $sessiondata['angsuran_total']              = 0;
}
?>
<x-base-layout>
    <div class="card">
        <div class="card-header">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Angsuran Tunai') }}</h3>
            </div>
            <a href="{{ route('credits-payment-cash.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}
            </a>
        </div>

        <div id="kt_credits_payment_cash_view">
            <form id="kt_credits_payment_cash_view_form" class="form" method="POST" action="{{ route('credits-payment-cash.process-add') }}" enctype="multipart/form-data">
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
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Angsuran') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_payment_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Angsuran" value="{{ date('d-m-Y') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Ke') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_payment_to" class="form-control form-control-lg form-control-solid" placeholder="Angsuran Ke" value="{{ old('credits_payment_to', $angsuranke ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jangka Waktu') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka Waktu" value="{{ old('credits_account_period', $acctcreditsaccount['credits_account_period'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_payment_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka Waktu" value="{{ old('credits_payment_period', $acctcreditsaccount['credits_payment_period'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Keterlambatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_payment_day_of_delay" class="form-control form-control-lg form-control-solid" placeholder="Keterlambatan" value="{{ old('credits_payment_day_of_delay', $credits_payment_day_of_delay ?? '') }}" autocomplete="off" readonly/>
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
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Anggota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_no" class="form-control form-control-lg form-control-solid" placeholder="No Anggota" value="{{ old('member_no', $acctcreditsaccount['member_no'] ?? '') }}" autocomplete="off" readonly/>
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
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Angsuran') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah Denda') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_accumulated_fines_view" id="credits_account_accumulated_fines_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_account_accumulated_fines_view', number_format($credits_account_accumulated_fines, 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_accumulated_fines" id="credits_account_accumulated_fines" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_account_accumulated_fines', $credits_account_accumulated_fines ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Denda Bulan/Minggu Ini') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_payment_fine_amount_view" id="credits_payment_fine_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_payment_fine_amount_view', number_format($credits_payment_fine_amount, 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_payment_fine_amount" id="credits_payment_fine_amount" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_payment_fine_amount', $credits_payment_fine_amount ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah Pinjaman') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_amount" id="credits_account_amount" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_account_amount', number_format($acctcreditsaccount['credits_account_amount']) ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Outstanding') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="sisa_pokok_view" id="sisa_pokok_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('sisa_pokok_view', number_format($acctcreditsaccount['credits_account_last_balance']) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="sisa_pokok" id="sisa_pokok" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('sisa_pokok', $acctcreditsaccount['credits_account_last_balance'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="sisa_bunga" id="sisa_bunga" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('sisa_bunga', $acctcreditsaccount['credits_account_interest_last_balance'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Guna Membayar') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Pokok') }}</label>
                                <div class="col-lg-8 fv-row">
                                    @if($acctcreditsaccount['payment_type_id'] == 1)
                                        <input type="text" name="angsuran_pokok_view" id="angsuran_pokok_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('angsuran_pokok_view', number_format($angsuranpokok, 2) ?? '') }}" autocomplete="off" readonly/>
                                    @else
                                        <input type="text" name="angsuran_pokok_view" id="angsuran_pokok_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('angsuran_pokok_view', number_format($angsuranpokok, 2) ?? '') }}" autocomplete="off"/>
                                    @endif
                                    <input type="hidden" name="angsuran_pokok" id="angsuran_pokok" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('angsuran_pokok', $angsuranpokok ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Bunga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="angsuran_bunga_view" id="angsuran_bunga_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('angsuran_bunga_view', number_format($angsuranbunga, 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="angsuran_bunga" id="angsuran_bunga" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('angsuran_bunga', $angsuranbunga ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sanksi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_payment_fine_view" id="credits_payment_fine_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_payment_fine_view', number_format($sessiondata['credits_payment_fine'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="credits_payment_fine" id="credits_payment_fine" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('credits_payment_fine', $sessiondata['credits_payment_fine'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Pendapatan Lain') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="others_income_view" id="others_income_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('others_income_view', number_format($sessiondata['others_income'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="others_income" id="others_income" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('others_income', $sessiondata['others_income'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Penerimaan') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Simpanan Wajib') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_mandatory_savings_view" id="member_mandatory_savings_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('member_mandatory_savings_view', number_format($sessiondata['member_mandatory_savings'], 2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="member_mandatory_savings" id="member_mandatory_savings" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('member_mandatory_savings', $sessiondata['member_mandatory_savings'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Total') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="angsuran_total_view" id="angsuran_total_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('angsuran_total_view', number_format($sessiondata['angsuran_total'], 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="angsuran_total" id="angsuran_total" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('angsuran_total', $sessiondata['angsuran_total'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_credits_payment_cash_submit">
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
                                    <th><b>Pendapatan Lain</b></th>
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
                                        <th style="text-align: right">{{ number_format($val['credits_others_income'], 2) }}</th>
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