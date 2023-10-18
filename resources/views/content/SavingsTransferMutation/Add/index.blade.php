
@section('scripts')
<script>
const form = document.getElementById('kt_savings_transfer_mutation_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'savings_account_no': {
                validators: {
                    notEmpty: {
                        message: 'No. Rekening harus diisi'
                    }
                }
            },
            'savings_transfer_mutation_amount': {
                validators: {
                    notEmpty: {
                        message: 'Jumlah harus diisi'
                    }
                }
            },
            'transfer_mutation_date': {
                validators: {
                    notEmpty: {
                        message: 'Tanggal Transfer harus diisi'
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

const submitButton = document.getElementById('kt_savings_transfer_mutation_add_submit');
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

$(document).ready(function(){
    $('#savings_transfer_mutation_amount_view').change(function(){
        var savings_transfer_mutation_amount = $('#savings_transfer_mutation_amount_view').val();

        function_elements_add('savings_transfer_mutation_amount', savings_transfer_mutation_amount);
        $('#savings_transfer_mutation_amount_view').val(toRp(savings_transfer_mutation_amount));
        $('#savings_transfer_mutation_amount').val(savings_transfer_mutation_amount);
        calculateSavings();
    });
    $('#button_modal_savings_from').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('savings-transfer-mutation.modal-savings-account-from')}}",
                success: function(msg){
                    $('#kt_modal').modal('show');
                    $('#modal-body').html(msg);
            }
    
        });
    });
    $('#button_modal_savings_to').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('savings-transfer-mutation.modal-savings-account-to')}}",
                success: function(msg){
                    $('#kt_modal').modal('show');
                    $('#modal-body').html(msg);
            }
    
        });
    });

    $('#kt_member_savings_transfer_mutation_add_reset').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('savings-transfer-mutation.reset-elements-add')}}",
                success: function(msg){
                    location.reload();
            }

        });
    }); 

});

function calculateSavings(){
    var savings_transfer_mutation_amount			= $('#savings_transfer_mutation_amount').val();
    var savings_account_from_opening_balance		= $('#savings_account_from_opening_balance').val();	
    var savings_account_to_opening_balance			= $('#savings_account_to_opening_balance').val();		

    savings_account_from_last_balance = parseFloat(savings_account_from_opening_balance) - parseFloat(savings_transfer_mutation_amount);
    savings_account_to_last_balance = parseFloat(savings_account_to_opening_balance) + parseFloat(savings_transfer_mutation_amount);


    $('#savings_account_to_last_balance').val(savings_account_to_last_balance);
    $('#savings_account_from_last_balance').val(savings_account_from_last_balance);

}

function function_elements_add(name, value){
    $.ajax({
            type: "POST",
            url : "{{route('savings-transfer-mutation.elements-add')}}",
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
@php $save=0; @endphp
@if(isset($savingsaccountfrom['unblock_state']))
    @if( strtotime($savingsaccountfrom['savings_account_pickup_date']) > strtotime('now') && $savingsaccountfrom['unblock_state'] == 0) 
    @php $save = 1; @endphp
    <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
            {{-- <span class="svg-icon svg-icon-2hx svg-icon-primary me-3">...</span> --}}
            <svg class="me-3" xmlns="http://www.w3.org/2000/svg" height="2em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .2-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
            <div class="d-flex flex-column">
            <h5 class="mb-1">Peringatan</h5>
            <span>Rekening Tabungan Yang Dipilih Belum Bisa Diambil Saldonya. Dapat diambil pada {{ old('savings_account_pickup_date', date('d-m-Y',strtotime($savingsaccountfrom['savings_account_pickup_date'] ?? ''))) }}.</span>
            </div>
    </div>
    @endif
@endif
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah') }}</h3>
            </div>

            <a href="{{ route('savings-transfer-mutation.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembalsi') }}</a>
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_savings_transfer_mutation_add_view_form" class="form" method="POST" action="{{ route('savings-transfer-mutation.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Transfer') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_transfer_mutation_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Transfer" value="{{ date('d-m-Y') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">Asal Transfer</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Rekening') }}</label>
                                <div class="col-lg-4 fv-row">
                                    <input type="text" name="savings_account_no" class="form-control form-control-lg form-control-solid" placeholder="No. Rekening" value="{{ old('savings_account_no', $savingsaccountfrom['savings_account_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                                <div class="col-lg-4">
                                    <button type="button" id="button_modal_savings_from" class="btn btn-primary">
                                        {{ __('Cari Rekening') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tabungan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_name" class="form-control form-control-lg form-control-solid" placeholder="Tabungan" value="{{ old('savings_name', $savingsaccountfrom['savings_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_name', $savingsaccountfrom['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea  name="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" autocomplete="off" readonly>{{ old('member_address', $savingsaccountfrom['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kabupaten') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="city_name" class="form-control form-control-lg form-control-solid" placeholder="Kabupaten" autocomplete="off" value="{{ old('city_name', $savingsaccountfrom['city_name'] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kecamatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="kecamatan_name" class="form-control form-control-lg form-control-solid" placeholder="Kecamatan" autocomplete="off" value="{{ old('kecamatan_name', $savingsaccountfrom['kecamatan_name'] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_account_last_balance_view" class="form-control form-control-lg form-control-solid" placeholder="Saldo" autocomplete="off" value="{{ old('savings_account_last_balance', empty($savingsaccountfrom['savings_account_last_balance']) ? '' : number_format($savingsaccountfrom['savings_account_last_balance'],2) ?? '') }}" readonly>
                                    <input type="hidden" name="savings_account_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo" autocomplete="off" value="{{ old('savings_account_last_balance', $savingsaccountfrom['savings_account_last_balance'] ?? '') }}" readonly>
                                    <input type="hidden" name="savings_account_from_opening_balance" id="savings_account_from_opening_balance" value="{{ old('savings_account_last_balance', $savingsaccountfrom['savings_account_last_balance'] ?? '') }}">
                                    <input type="hidden" name="savings_account_from_last_balance" id="savings_account_from_last_balance">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sandi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="hidden" name="mutation_id" class="form-control form-control-lg form-control-solid" placeholder="Sandi" autocomplete="off" value="{{ old('mutation_id', $acctmutation['mutation_id'] ?? '') }}" readonly>
                                    <input name="mutation_name" class="form-control form-control-lg form-control-solid" placeholder="Sandi" autocomplete="off" value="{{ old('mutation_name', $acctmutation['mutation_name'] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jumlah') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="savings_transfer_mutation_amount_view" id="savings_transfer_mutation_amount_view" class="form-control form-contr ol-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('savings_transfer_mutation_amount_view', empty($session['savings_transfer_mutation_amount']) ? '' : number_format($session['savings_transfer_mutation_amount'],2) ?? '') }}">
                                    <input type="hidden" name="savings_transfer_mutation_amount" id="savings_transfer_mutation_amount" class="form-control form-control-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('savings_transfer_mutation_amount', $session['savings_transfer_mutation_amount'] ?? '') }}">
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">Tujuan Transfer</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Rekening') }}</label>
                                <div class="col-lg-4 fv-row">
                                    <input type="text" name="savings_account_no" class="form-control form-control-lg form-control-solid" placeholder="No. Rekening" value="{{ old('savings_account_no', $savingsaccountto['savings_account_no'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" cclass="form-control form-control-lg form-control-solid" name="savings_account_to_last_balance" id="savings_account_to_last_balance">
                                    <input type="hidden" name="savings_account_to_opening_balance" id="savings_account_to_opening_balance" value="{{ old('savings_account_last_balance', $savingsaccountto['savings_account_last_balance'] ?? '') }}">
                                </div>
                                <div class="col-lg-4">
                                    <button @if($save) disabled  @endif type="button" id="button_modal_savings_to" class="btn btn-primary">
                                        {{ __('Cari Rekening') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tabungan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_name" class="form-control form-control-lg form-control-solid" placeholder="Tabungan" value="{{ old('savings_name', $savingsaccountto['savings_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_name', $savingsaccountto['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea  name="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" autocomplete="off" readonly>{{ old('member_address', $savingsaccountto['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kabupaten') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="city_name" class="form-control form-control-lg form-control-solid" placeholder="Kabupaten" autocomplete="off" value="{{ old('city_name', $savingsaccountto['city_name'] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kecamatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="kecamatan_name" class="form-control form-control-lg form-control-solid" placeholder="Kecamatan" autocomplete="off" value="{{ old('kecamatan_name', $savingsaccountto['kecamatan_name'] ?? '') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" id="kt_member_savings_transfer_mutation_add_reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
                    
                    <button @if($save) disabled type="button" @else type="submit" @endif class="btn btn-primary" id="kt_savings_transfer_mutation_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Daftar Rekening</h3>
    
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