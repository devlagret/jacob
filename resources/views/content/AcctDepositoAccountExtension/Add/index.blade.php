
@php
    use App\Http\Controllers\AcctDepositoAccountExtensionController;
@endphp

@section('scripts')
<script>
const form = document.getElementById('kt_deposito_account_extension_edit_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'deposito_account_extra_period': {
                validators: {
                    notEmpty: {
                        message: 'Jangka Waktu harus diisi'
                    }
                }
            },
            'deposito_account_amount_adm': {
                validators: {
                    notEmpty: {
                        message: 'Biaya Administrasi harus diisi'
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

const submitButton = document.getElementById('kt_deposito_account_extension_edit_submit');
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
    var period = $('#deposito_account_extra_period').val();
    var counts = {
        normal: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
        leap:   [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
    }
    var no = 1 + parseFloat(period);
    var d = new Date(),
        month = '' + (d.getMonth() + no),
        day = '' + d.getDate(),
        year = d.getFullYear();

    var endYear  = d.getFullYear() + Math.ceil((no + d.getMonth()) / 12) - 1;
    var yearType = ((endYear % 4 == 0) && (endYear % 100 != 0)) || (endYear % 400 == 0) ? 'leap' : 'normal';
    var endMonth = (d.getMonth() + no) % 12;
    var endDate  = Math.min(d.getDate(), counts[yearType][endMonth]);
    if(endMonth == 0){
        endMonth = endMonth+12;
    }
    if (endMonth.toString().length < 2){

        endMonth = '0' + endMonth;
    } 
    if (endDate.toString().length < 2) {
        endDate = '0' + endDate;
    }
    var date = [endDate , endMonth , endYear].join('-');

    if (period != '') {
        $('#deposito_account_extra_due_date').val(date);
    }

    $('#deposito_account_extra_period').change(function(){
        var period = $('#deposito_account_extra_period').val();
        var counts = {
            normal: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
            leap:   [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
        }
        var no = 1 + parseFloat(period);
        var d = new Date(),
            month = '' + (d.getMonth() + no),
            day = '' + d.getDate(),
            year = d.getFullYear();

        var endYear  = d.getFullYear() + Math.ceil((no + d.getMonth()) / 12) - 1;
        var yearType = ((endYear % 4 == 0) && (endYear % 100 != 0)) || (endYear % 400 == 0) ? 'leap' : 'normal';
        var endMonth = (d.getMonth() + no) % 12;
        var endDate  = Math.min(d.getDate(), counts[yearType][endMonth]);
        if(endMonth == 0){
            endMonth = endMonth+12;
        }
        if (endMonth.toString().length < 2){

            endMonth = '0' + endMonth;
        } 
        if (endDate.toString().length < 2) {
            endDate = '0' + endDate;
        }
        var date = [endDate , endMonth , endYear].join('-');

        $('#deposito_account_extra_due_date').val(date);
    });

    $('#deposito_account_amount_adm_view').change(function(){
        var deposito_account_amount_adm = $('#deposito_account_amount_adm_view').val();

        function_elements_add('deposito_account_amount_adm',deposito_account_amount_adm);
        $('#deposito_account_amount_adm_view').val(toRp(deposito_account_amount_adm));
        $('#deposito_account_amount_adm').val(deposito_account_amount_adm);
    });

    $('#kt_member_savings_transfer_mutation_add_reset').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('deposito-account-extension.reset-elements-add')}}",
                success: function(msg){
                    location.reload();
            }

        });
    }); 

});

function function_elements_add(name, value){
    $.ajax({
            type: "POST",
            url : "{{route('deposito-account-extension.elements-add')}}",
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
                <h3 class="fw-bolder m-0">{{ __('Form Tambah') }}</h3>
            </div>

            <a href="{{ route('deposito-account-extension.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_deposito_account_extension_edit_view_form" class="form" method="POST" action="{{ route('deposito-account-extension.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Simpka') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_no" class="form-control form-control-lg form-control-solid" placeholder="No. Simpka" value="{{ old('deposito_account_no', $acctdepositoaccount['deposito_account_no'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="deposito_account_id" class="form-control form-control-lg form-control-solid" placeholder="No. Simpka" value="{{ old('deposito_account_id', $acctdepositoaccount['deposito_account_id'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Seri') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_account_serial_no" class="form-control form-control-lg form-control-solid" placeholder="No. Seri" value="{{ old('deposito_account_serial_no', $acctdepositoaccount['deposito_account_serial_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Simpanan Berjangka') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="deposito_name" class="form-control form-control-lg form-control-solid" placeholder="Jenis Simpanan Berjangka" value="{{ old('deposito_name', $acctdepositoaccount['deposito_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Anggota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Anggota" value="{{ old('member_name', $acctdepositoaccount['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Anggota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_no" class="form-control form-control-lg form-control-solid" placeholder="No. Anggota" value="{{ old('member_no', $acctdepositoaccount['member_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Kelamin') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_gender" class="form-control form-control-lg form-control-solid" placeholder="Jenis Kelamin" value="{{ old('member_gender', $membergender[$acctdepositoaccount['member_gender']] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea name="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" autocomplete="off" readonly>{{ old('member_address', $acctdepositoaccount['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kabupaten') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="city_id" class="form-control form-control-lg form-control-solid" placeholder="Kabupaten" autocomplete="off" value="{{ old('city_id', AcctDepositoAccountExtensionController::getCityName($acctdepositoaccount['city_id']) ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kecamatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="kecamatan_id" class="form-control form-control-lg form-control-solid" placeholder="Kecamatan" autocomplete="off" value="{{ old('kecamatan_id', AcctDepositoAccountExtensionController::getKecamatanName($acctdepositoaccount['kecamatan_id']) ?? '') }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Identitas') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="identity_id" class="form-control form-control-lg form-control-solid" placeholder="Identitas" autocomplete="off" value="{{ old('identity_id', $memberidentity[$acctdepositoaccount['identity_id']] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Identitas') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="No. Identitas" autocomplete="off" value="{{ old('member_identity_no', $acctdepositoaccount['member_identity_no'] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jangka Waktu') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="deposito_account_period" class="form-control form-control-lg form-control-solid" placeholder="Bulan" autocomplete="off" value="{{ old('deposito_account_period', $acctdepositoaccount['deposito_account_period'] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="deposito_account_amount" class="form-control form-control-lg form-control-solid" placeholder="Saldo" autocomplete="off" value="{{ old('deposito_account_amount', empty($acctdepositoaccount['deposito_account_amount']) ? '' : number_format($acctdepositoaccount['deposito_account_amount'],2) ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Mulai') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="deposito_account_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Mulai" autocomplete="off" value="{{ old('deposito_account_date', date('d-m-Y', strtotime($acctdepositoaccount['deposito_account_date'])) ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jatuh Tempo') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="deposito_account_due_date" class="form-control form-control-lg form-control-solid" placeholder="Jatuh Tempo" autocomplete="off" value="{{ old('deposito_account_due_date', date('d-m-Y', strtotime($acctdepositoaccount['deposito_account_due_date'])) ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">Perpanjangan Simpanan</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jangka Waktu') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="deposito_account_extra_period" id="deposito_account_extra_period" class="form-control form-control-lg form-control-solid" placeholder="Bulan" autocomplete="off" onchange="function_elements_add(this.name, this.value)" value="{{ old('deposito_account_extra_period', $datases['deposito_account_extra_period'] ?? '') }}">
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Mulai') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="deposito_account_extra_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Mulai" autocomplete="off" value="{{ date('d-m-Y') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jatuh Tempo') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="deposito_account_extra_due_date" id="deposito_account_extra_due_date" class="form-control form-control-lg form-control-solid" placeholder="Jatuh Tempo" autocomplete="off" value="{{ old('deposito_account_extra_due_date', $acctdepositoaccount['deposito_account_extra_due_date'] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Biaya Administrasi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="deposito_account_amount_adm_view" id="deposito_account_amount_adm_view" class="form-control form-control-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('deposito_account_amount_adm', empty($datases['deposito_account_amount_adm']) ? '' : number_format($datases['deposito_account_amount_adm'],2) ?? '') }}">
                                    <input type="hidden" name="deposito_account_amount_adm" id="deposito_account_amount_adm" class="form-control form-control-lg form-control-solid" value="{{ old('deposito_account_amount_adm', $datases['deposito_account_amount_adm'] ?? '') }}">
                                </div>
                            </div>
                            <input type="hidden" name="deposito_id" class="form-control form-control-lg form-control-solid" value="{{ old('deposito_id', $acctdepositoaccount['deposito_id'] ?? '') }}">
                            <input type="hidden" name="member_id" class="form-control form-control-lg form-control-solid" value="{{ old('member_id', $acctdepositoaccount['member_id'] ?? '') }}">
                            <input type="hidden" name="deposito_account_interest_amount" class="form-control form-control-lg form-control-solid" value="{{ old('deposito_account_interest_amount', $acctdepositoaccount['deposito_account_interest_amount'] ?? '') }}">
                            <input type="hidden" name="savings_account_id" class="form-control form-control-lg form-control-solid" value="{{ old('savings_account_id', $acctdepositoaccount['savings_account_id'] ?? '') }}">
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" id="kt_member_savings_transfer_mutation_add_reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_deposito_account_extension_edit_submit">
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
                    <h3 class="modal-title"></h3>
    
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