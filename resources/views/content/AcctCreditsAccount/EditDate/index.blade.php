@section('scripts')
<script>
    const form = document.getElementById('kt_credits_account_edit_date_view_form');

    var validator = FormValidation.formValidation(
        form,
        {
            fields: {
                'credits_account_serial': {
                    validators: {
                        notEmpty: {
                            message: 'No. Perjanjian Pinjaman harus diisi'
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

    const submitButton = document.getElementById('kt_credits_account_edit_date_submit');
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
    $(document).ready(function(){change_date();})
    function change_date(){
        var angsuran 	= $("#credits_payment_period").val();
        var period 		= $("#credits_account_period").val();
        var date2 		= $("#credits_account_date").val();
        var day2 		= date2.substring(0, 2);
        var month2 		= date2.substring(3, 5);
        var year2 		= date2.substring(6, 10);
        var date 		= year2 + '-' + month2 + '-' + day2;
        var date1		= new Date(date);

        if(angsuran == 1){
            var a 		= moment(date1); 
            var b 		= a.add(period, 'month'); 
            var testDate = new Date(date);
            var tmp2 = testDate.setMonth(testDate.getMonth() + 1);
            var date_first = testDate.toISOString();
            var day2 = date_first.substring(8, 10);
            var month2 = date_first.substring(5, 7);
            var year2 = date_first.substring(0, 4);
            var first = day2 + '-' + month2 + '-' + year2;
            
            $('#credits_account_due_date').val(b.format('DD-MM-YYYY'));
            $('#credits_account_payment_date').val(first);
        }else{
            var week 		= period * 7;
            var testDate 	= new Date(date1);
            var tmp 		= testDate.setDate(testDate.getDate() + week);
            var date_tmp 	= testDate.toISOString();
            var day 		= date_tmp.substring(8, 10);
            var month 		= date_tmp.substring(5, 7);
            var year 		= date_tmp.substring(0, 4); 
            var name 		= 'credit_account_due_date';
            var value 		= day + '-' + month + '-' + year;
            
            var testDate2 = new Date(date1);
            var tmp2 = testDate2.setDate(testDate2.getDate() + 7);
            var date_first = testDate2.toISOString();
            var day2 = date_first.substring(8, 10);
            var month2 = date_first.substring(5, 7);
            var year2 = date_first.substring(0, 4);
            var first = day2 + '-' + month2 + '-' + year2;

            $('#credits_account_due_date').val(value);
            $('#credits_account_payment_date').val(first);
        }
    };
</script>
@endsection 
<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Edit Tanggal Data Pinjaman') }}</h3>
            </div>
    
            <a href="{{ route('credits-account.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>
    
        <div id="kt_user_edit_view">
            <form id="kt_credits_account_edit_date_view_form" class="form" method="POST" action="{{ route('credits-account.process-edit-date') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6 required">{{ __('No. Perjanjian Pinjaman') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="credits_account_serial" id="credits_account_serial" class="form-control form-control-lg form-control-solid" placeholder="No. Perjanjian Pinjaman" value="{{ old('credits_account_serial', $acctcreditsaccount['credits_account_serial'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_id" id="credits_account_id" class="form-control form-control-lg form-control-solid" value="{{ old('credits_account_id', $acctcreditsaccount['credits_account_id'] ?? '') }}"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Nama Anggota') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="member_name" id="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Anggota" value="{{ old('member_name', $acctcreditsaccount['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('No. Identitas') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="member_identity_no" id="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="No. Identitas" value="{{ old('member_identity_no', $acctcreditsaccount['member_identity_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Jenis Pinjaman') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="credits_name" id="credits_name" class="form-control form-control-lg form-control-solid" placeholder="No. Identitas" value="{{ old('credits_name', $acctcreditsaccount['credits_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Tanggal Realisasi') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="credits_account_date" id="credits_account_date" class="date form-control form-control-lg form-control-solid" placeholder="No. Identitas" value="{{ old('credits_account_date', empty($acctcreditsaccount['credits_account_date']) ? date('d-m-Y') : date('d-m-Y', strtotime($acctcreditsaccount['credits_account_date'])) ?? '') }}" autocomplete="off" onchange="change_date()"/>
                                </div>
                            </div>  
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Jangka Waktu') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="credits_account_period" id="credits_account_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka Waktu" value="{{ old('credits_account_period', $acctcreditsaccount['credits_account_period'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_payment_period" id="credits_payment_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka Waktu" value="{{ old('credits_payment_period', $acctcreditsaccount['credits_payment_period'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Tanggal Jatuh Tempo') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="credits_account_due_date" id="credits_account_due_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Jatuh Tempo" value="{{ old('credits_account_due_date', empty($acctcreditsaccount['credits_account_due_date']) ? date('d-m-Y') : date('d-m-Y', strtotime($acctcreditsaccount['credits_account_due_date'])) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_payment_date" id="credits_account_payment_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Jatuh Tempo" value="{{ old('credits_account_payment_date', empty($acctcreditsaccount['credits_account_payment_date']) ? date('d-m-Y') : date('d-m-Y', strtotime($acctcreditsaccount['credits_account_payment_date'])) ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" id="kt_credits_account_edit_date_reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_credits_account_edit_date_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
    </x-base-layout>
