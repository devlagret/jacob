@section('scripts')
<script>
</script>
@endsection
<?php 
if($acctcreditsaccount['credits_account_last_payment_date'] == null){
    $acctcreditsaccount['credits_account_last_payment_date'] = "-";
}else{
    $acctcreditsaccount['credits_account_last_payment_date'] = date('d-m-Y', strtotime($acctcreditsaccount['credits_account_last_payment_date']));
}
?>
<x-base-layout>
    <div class="card">
        <div class="card-header">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Detail') }}</h3>
            </div>
            <a href="{{ route('credits-account-history.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}
            </a>
        </div>

        <div id="kt_credits_history_view">
            <div class="card-body pt-6">
                <div class="row mb-6">
                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Anggota') }}</b>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Anggota') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Anggota" value="{{ old('member_name', $acctcreditsaccount->member->member_name ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Anggota') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_no" class="form-control form-control-lg form-control-solid" placeholder="No Anggota" value="{{ old('member_no', $acctcreditsaccount->member->member_no ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                            <div class="col-lg-8 fv-row">
                                <textarea id="member_address" name="member_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat" readonly>{{ old('member_address', $acctcreditsaccount->member->member_address ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Identitas') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="No Identitas" value="{{ old('member_identity_no', $acctcreditsaccount->member->member_identity_no ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('Pinjaman') }}</b>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Akad Pinjaman') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_serial" class="form-control form-control-lg form-control-solid" placeholder="No Akad Pinjaman" value="{{ old('credits_account_serial', $acctcreditsaccount['credits_account_serial'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Pinjaman') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_name" class="form-control form-control-lg form-control-solid" placeholder="Jenis Pinjaman" value="{{ old('credits_name', $acctcreditsaccount->credit->credits_name ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Realisasi') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Realisasi" value="{{ old('credits_account_date', date('d-m-Y', strtotime($acctcreditsaccount['credits_account_date'])) ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jangka Waktu') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka Waktu" value="{{ old('credits_account_period', $acctcreditsaccount['credits_account_period'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row mb-14">
                            <b class="col-lg-12 fw-bold fs-3 text-primary">{{ __('') }}</b>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Jatuh Tempo') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_due_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Jatuh Tempo" value="{{ old('credits_account_due_date', date('d-m-Y', strtotime($acctcreditsaccount['credits_account_due_date'])) ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah Pinjaman') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_amount" class="form-control form-control-lg form-control-solid" placeholder="Jumlah Pinjaman" value="{{ old('credits_account_amount', number_format($acctcreditsaccount['credits_account_amount'], 2) ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Angsuran') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="payment_type_id" class="form-control form-control-lg form-control-solid" placeholder="Jenis Angsuran" value="{{ old('payment_type_id', $paymenttype[$acctcreditsaccount['payment_type_id']] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Pokok') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_principal_amount" class="form-control form-control-lg form-control-solid" placeholder="Angsuran Pokok" value="{{ old('credits_account_principal_amount', number_format($acctcreditsaccount['credits_account_principal_amount'], 2) ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Presentase Bunga') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_interest" class="form-control form-control-lg form-control-solid" placeholder="Presentase Bunga" value="{{ old('credits_account_interest', $acctcreditsaccount['credits_account_interest'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo Pokok') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Pokok" value="{{ old('credits_account_last_balance', number_format($acctcreditsaccount['credits_account_last_balance'], 2) ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Angsuran Terakhir') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_last_payment_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Angsuran Terakhir" value="{{ old('credits_account_last_payment_date', $acctcreditsaccount['credits_account_last_payment_date'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Terakhir') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_payment_to" class="form-control form-control-lg form-control-solid" placeholder="Angsuran Terakhir" value="{{ old('credits_account_payment_to', $acctcreditsaccount['credits_account_payment_to'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah Denda') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_Account_accumulated_fines" class="form-control form-control-lg form-control-solid" placeholder="Jumlah Sanksi" value="{{ old('credits_Account_accumulated_fines', number_format($acctcreditsaccount['credits_Account_accumulated_fines'], 2) ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                                    <th><b>Sanksi</b></th>
                                    <th><b>Ak Sanksi</b></th>
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
                                        <th style="text-align: right">{{ number_format($val['credits_payment_fine'], 2) }}</th>
                                        <th style="text-align: right">{{ number_format($val['credits_payment_fine_last_balance'], 2) }}</th>
                                    </tr>
                                <?php $no++ ?>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('credits-account-history.print-payment-history', $acctcreditsaccount['credits_account_id']) }}" class="btn btn-primary" id="kt_credits_payment_history_print">
                {{ __('Cetak') }}
            </a>
        </div>
    </div>
</x-base-layout>