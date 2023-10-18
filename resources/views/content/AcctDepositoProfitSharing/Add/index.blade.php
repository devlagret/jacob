@section('scripts')
<script>

const form = document.getElementById('kt_savings_cash_mutation_add_view_form');

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

const submitButton = document.getElementById('kt_savings_cash_mutation_add_submit');
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
            url : "{{route('deposito-profit-sharing.modal-savings-account')}}",
            success: function(msg){
                $('#kt_modal_savings_account').modal('show');
                $('#modal-body').html(msg);
            }
        });
    });

    calculate();
});

function function_elements_add(name, value){
    $.ajax({
        type: "POST",
        url : "{{route('deposito-profit-sharing.elements-add')}}",
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
    var deposito_account_amount = $("#deposito_account_amount").val();
    var deposito_account_interest = $("#deposito_account_interest").val();

    var deposito_profit_sharing_amount = parseFloat(deposito_account_interest) / 12 / 100 * parseFloat(deposito_account_amount);

    console.log(deposito_profit_sharing_amount);

    document.getElementById("deposito_profit_sharing_amount").value = deposito_profit_sharing_amount;
    document.getElementById("deposito_profit_sharing_amount_view").value = toRp(deposito_profit_sharing_amount);
}
</script>
@endsection
<?php 
if(!isset($acctsavingsaccount['savings_account_last_balance'])){
    $acctsavingsaccount['savings_account_last_balance']  = 0;
}
?>
<x-base-layout>
    <div class="card">
        <div class="card-header">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Proses Bunga Simpanan Berjangka') }}</h3>
            </div>
            <a href="{{ route('deposito-profit-sharing.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}
            </a>
        </div>

        <div id="kt_savings_cash_mutation_view">
            <form id="kt_savings_cash_mutation_add_view_form" class="form" method="POST" action="{{ route('deposito-profit-sharing.process-update') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Simpanan Berjangka') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Rek Simp Berjangka') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_no" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('deposito_account_no', $acctdepositoaccount['deposito_account_no'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="deposito_profit_sharing_id" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('deposito_profit_sharing_id', $sessiondata['deposito_profit_sharing_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="deposito_id" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('deposito_id', $acctdepositoaccount['deposito_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="deposito_account_id" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('deposito_account_id', $acctdepositoaccount['deposito_account_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="deposito_account_interest" id="deposito_account_interest" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('deposito_account_interest', $acctdepositoaccount['deposito_account_interest'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Seri') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_serial_no" class="form-control form-control-lg form-control-solid" placeholder="No Seri" value="{{ old('deposito_account_serial_no', $acctdepositoaccount['deposito_account_serial_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jangka Waktu') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka Waktu" value="{{ old('deposito_account_period', $acctdepositoaccount['deposito_account_period'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Buka') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_date" id="deposito_account_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Buka" value="{{ old('deposito_account_date', date('d-m-Y', strtotime($acctdepositoaccount['deposito_account_date'])) ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jatuh Tempo') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_due_date" id="deposito_account_due_date" class="form-control form-control-lg form-control-solid" placeholder="Jatuh Tempo" value="{{ old('deposito_account_due_date', date('d-m-Y', strtotime($acctdepositoaccount['deposito_account_due_date'])) ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_amount_view" id="deposito_account_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('deposito_account_amount_view', number_format($acctdepositoaccount['deposito_account_amount'], 2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="deposito_account_amount" id="deposito_account_amount" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('deposito_account_amount', $acctdepositoaccount['deposito_account_amount'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Anggota') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_name', $acctdepositoaccount['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea id="member_address" name="member_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Sesuai KTP" readonly>{{ old('member_address', $acctdepositoaccount['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Telepon') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_phone" class="form-control form-control-lg form-control-solid" placeholder="No Telepon" value="{{ old('member_phone', $acctdepositoaccount['member_phone'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Bunga Simpanan Berjangka') }}</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Bunga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_profit_sharing_amount_view" id="deposito_profit_sharing_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('deposito_profit_sharing_amount_view', '' ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="deposito_profit_sharing_amount" id="deposito_profit_sharing_amount" class="form-control form-control-lg form-control-solid" placeholder="Rupiah" value="{{ old('deposito_profit_sharing_amount', '' ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No Rek Tabungan') }}</label>
                                <div class="col-lg-5 fv-row">
                                    <input type="hidden" name="savings_account_id" class="form-control form-control-lg form-control-solid" placeholder="ID Rek Tabungan" value="{{ old('savings_account_id', $acctsavingsaccount['savings_account_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="text" name="full_no" class="form-control form-control-lg form-control-solid" placeholder="No Rek Tabungan" value="{{ old('full_no', $acctsavingsaccount['full_no'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="savings_id" class="form-control form-control-lg form-control-solid" placeholder="No Rek Tabungan" value="{{ old('savings_id', $acctsavingsaccount['savings_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="member_id_savings" class="form-control form-control-lg form-control-solid" placeholder="No Rek Tabungan" value="{{ old('member_id_savings', $acctsavingsaccount['member_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="savings_account_last_balance" class="form-control form-control-lg form-control-solid" placeholder="No Rek Tabungan" value="{{ old('savings_account_last_balance', $acctsavingsaccount['savings_account_last_balance'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                                <div class="col-lg-3 fv-row">
                                    <button type="button" id="open_modal_button" class="btn btn-primary">
                                        {{ __('Cari Tabungan') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_savings_cash_mutation_add_submit">
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