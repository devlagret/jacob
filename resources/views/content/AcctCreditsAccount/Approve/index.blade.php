    @section('scripts')
    <script>
        const form = document.getElementById('kt_credit_account_approving_view_form');

        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'credits_account_serial': {
                        validators: {
                            notEmpty: {
                                message: 'Nomor Perjanjian Kredit harus diisi'
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

        const submitButton = document.getElementById('kt_credit_account_approving_submit');
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
    </script>
@endsection
<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Persetujuan Pinjaman') }}</h3>
            </div>

            <a href="{{ route('credits-account.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_credit_account_approving_view_form" class="form" method="POST" action="{{ route('credits-account.process-approving') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Perjanjian Kredit') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_serial" class="form-control form-control-lg form-control-solid" placeholder="No. Perjanjian Kredit" value="{{ old('credits_account_serial', $acctcreditsaccount['credits_account_serial'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_id" class="form-control form-control-lg form-control-solid" placeholder="No. Perjanjian Kredit" value="{{ old('credits_account_id', $acctcreditsaccount['credits_account_id'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Anggota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Anggota" value="{{ old('member_name', $acctcreditsaccount->member->member_name ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat Anggota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea  name="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat Anggota" autocomplete="off" readonly>{{ old('member_address', $acctcreditsaccount->member->member_address ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Identitas') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="No. Identitas" autocomplete="off" value="{{ old('member_identity_no', $acctcreditsaccount->member->member_identity_no ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Pinjaman') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="credits_name" class="form-control form-control-lg form-control-solid" placeholder="Jenis Pinjaman" autocomplete="off" value="{{ old('credits_name', $acctcreditsaccount->credit->credits_name ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Realisasi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="credits_account_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Realisasi" autocomplete="off" value="{{ old('credits_account_date', empty($acctcreditsaccount['credits_account_date']) ? date('d-m-Y') : date('d-m-Y', strtotime($acctcreditsaccount['credits_account_date'])) ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jangka Waktu') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="credits_account_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka Waktu" autocomplete="off" value="{{ old('credits_account_period', $acctcreditsaccount['credits_account_period'] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Jatuh Tempo') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="credits_account_due_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Jatuh Tempo" autocomplete="off" value="{{ old('credits_account_due_date',  empty($acctcreditsaccount['credits_account_due_date']) ? date('d-m-Y') : date('d-m-Y', strtotime($acctcreditsaccount['credits_account_due_date'])) ?? '') }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah Pinjaman') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_amount" class="form-control form-control-lg form-control-solid" placeholder="Jumlah Pinjaman" value="{{ old('credits_account_amount', empty($acctcreditsaccount['credits_account_amount']) ? '' : number_format($acctcreditsaccount['credits_account_amount'],2) ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Angsuran') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="payment_type_id" class="form-control form-control-lg form-control-solid" placeholder="Jenis Angsuran" value="{{ old('payment_type_id', empty($acctcreditsaccount['payment_type_id']) ? '' : $paymenttype[$acctcreditsaccount['payment_type_id']] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Pokok') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_principal_amount" class="form-control form-control-lg form-control-solid" placeholder="Angsuran Pokok" value="{{ old('credits_account_principal_amount', empty($acctcreditsaccount['credits_account_principal_amount']) ? '' : number_format($acctcreditsaccount['credits_account_principal_amount'],2) ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Bunga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_interest_amount" class="form-control form-control-lg form-control-solid" placeholder="Angsuran Bunga" value="{{ old('credits_account_interest_amount', empty($acctcreditsaccount['credits_account_interest_amount']) ? '' : number_format($acctcreditsaccount['credits_account_interest_amount'],2) ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah Angsuran') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_payment_amount" class="form-control form-control-lg form-control-solid" placeholder="Jumlah Angsuran" value="{{ old('credits_account_payment_amount', empty($acctcreditsaccount['credits_account_payment_amount']) ? '' : number_format($acctcreditsaccount['credits_account_payment_amount'],2) ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Presentase Bunga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_interest" class="form-control form-control-lg form-control-solid" placeholder="Presentase Bunga" value="{{ old('credits_account_interest', $acctcreditsaccount['credits_account_interest'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
    
                    <button type="submit" class="btn btn-primary" id="kt_credit_account_approving_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>
