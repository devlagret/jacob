<x-base-layout>
<div class="card mb-5 mb-xl-10">
    <div class="card-header border-0">
        <div class="card-title m-0">
            <h3 class="fw-bolder m-0">{{ __('Detail Data Pinjaman Baru') }}</h3>
        </div>

        <a href="{{ route('credits-account.index') }}" class="btn btn-light align-self-center">
            {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
            {{ __('Kembali') }}</a>
    </div>

    <div id="kt_user_edit_view">
        <form id="kt_credits_account_add_view_form" class="form" method="POST" action="{{ route('credits-account.process-add') }}" enctype="multipart/form-data">
        @csrf
        @method('POST')
            <div class="card-body border-top p-9">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row mb-6">
                            <b class="col-lg-12 fw-bold fs-3 text-primary">Data Anggota</b>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('No. Anggota') }}</label>
                            <div class="col-lg-10 fv-row">
                                <input type="text" name="member_no" id="member_no" class="form-control form-control-lg form-control-solid" placeholder="No. Anggota" value="{{ old('member_no', $coremember['member_no'] ?? '') }}" autocomplete="off" readonly/>
                                <input type="hidden" name="member_id" id="member_id" class="form-control form-control-lg form-control-solid" value="{{ old('member_id', $coremember['member_id'] ?? '') }}"/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Nama Anggota') }}</label>
                            <div class="col-lg-10 fv-row">
                                <input type="text" name="member_name" id="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Anggota" value="{{ old('member_name', $coremember['member_name'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Tanggal Lahir') }}</label>
                            <div class="col-lg-10 fv-row">
                                <input type="text" name="member_date_of_birth" id="member_date_of_birth" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Lahir" value="{{ old('member_date_of_birth', empty($coremember['member_date_of_birth']) ? '' : date('d-m-Y', strtotime($coremember['member_date_of_birth'])) ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Jenis Kelamin') }}</label>
                            <div class="col-lg-10 fv-row">
                                <input type="text" name="member_gender" id="member_gender" class="form-control form-control-lg form-control-solid" placeholder="Jenis Kelamin" value="{{ old('member_gender', empty($coremember['member_gender']) ? '' : $membergender[$coremember['member_gender']] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('No. Telepon') }}</label>
                            <div class="col-lg-10 fv-row">
                                <input type="text" name="member_phone" id="member_phone" class="form-control form-control-lg form-control-solid" placeholder="No. Telepon" value="{{ old('member_phone', $coremember['member_phone'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                            <div class="col-lg-10 fv-row">
                                <textarea type="text" name="member_address" id="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" autocomplete="off" readonly>{{ old('member_address', $coremember['member_address'] ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Nama Ibu') }}</label>
                            <div class="col-lg-10 fv-row">
                                <input type="text" name="member_mother" id="member_mother" class="form-control form-control-lg form-control-solid" placeholder="Nama Ibu" value="{{ old('member_mother', $coremember['member_mother'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('No. Identitas') }}</label>
                            <div class="col-lg-10 fv-row">
                                <input type="text" name="member_identity_no" id="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="No. Identitas" value="{{ old('member_identity_no', $coremember['member_identity_no'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                    </div>
                    <div class="separator my-16"></div>
                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <b class="col-lg-12 fw-bold fs-3 text-primary">Data Pinjaman</b>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Pinjaman') }}</label>
                            <div class="col-lg-8 fv-row">
                                <select name="credits_id" id="credits_id" data-control="select2" data-placeholder="{{ __('Pilih Jenis Pinjaman') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" disabled>
                                    <option value="">{{ __('Pilih') }}</option>
                                    @foreach($creditid as $key => $value)
                                        <option data-kt-flag="{{ $value['credits_id'] }}" value="{{ $value['credits_id'] }}" {{ $value['credits_id'] == old('credits_id', $datasession['credits_id'] ?? '') ? 'selected' :'' }}>{{ $value['credits_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Tiap') }}</label>
                            <div class="col-lg-8 fv-row">
                                <select name="payment_period" id="payment_period" data-control="select2" data-placeholder="{{ __('Pilih Angsuran Tiap') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" disabled>
                                    <option value="">{{ __('Pilih') }}</option>
                                    @foreach($paymentperiod as $key => $value)
                                        <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('payment_period', $datasession['payment_period'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Realisasi') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credit_account_date" id="credit_account_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Realisasi" value="{{ old('credit_account_date', empty($datasession['credit_account_date']) ? date('d-m-Y') : $datasession['credit_account_date'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Angsuran I') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credit_account_payment_to" id="credit_account_payment_to" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Angsuran I" value="{{ old('credit_account_payment_to', empty($datasession['credit_account_payment_to']) ? date('d-m-Y') : $datasession['credit_account_payment_to'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Agunan') }}</label>
                            <div class="col-lg-8 fv-row">
                                <a type="button" class="btn btn-sm btn-primary btn-active-light-primary m-1" data-bs-toggle="modal" data-bs-target="#kt_modal_angunan">
                                    Angunan
                                </a>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Business Office (BO)') }}</label>
                            <div class="col-lg-8 fv-row">
                                <select name="office_id" id="office_id" data-control="select2" data-placeholder="{{ __('Pilih Business Office (BO)') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" disabled>
                                    <option value="">{{ __('Pilih') }}</option>
                                    @foreach($coreoffice as $key => $value)
                                        <option data-kt-flag="{{ $value['office_id'] }}" value="{{ $value['office_id'] }}" {{ $value['office_id'] == old('office_id', $datasession['office_id'] ?? '') ? 'selected' :'' }}>{{ $value['office_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Plafon Pinjaman') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_last_balance_principal_view" id="credits_account_last_balance_principal_view" class="form-control form-control-lg form-control-solid" placeholder="Plafon Pinjaman" value="{{ old('credits_account_last_balance_principal_view', empty($datasession['credits_account_last_balance_principal']) ? '' : number_format($datasession['credits_account_last_balance_principal'],2) ?? '') }}" autocomplete="off" readonly/>
                                <input type="hidden" name="credits_account_last_balance_principal" id="credits_account_last_balance_principal" class="form-control form-control-lg form-control-solid" value="{{ old('credits_account_last_balance_principal', $datasession['credits_account_last_balance_principal'] ?? '') }}"/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Pokok') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credits_account_principal_amount_view" id="credits_account_principal_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Angsuran Pokok" value="{{ old('credits_account_principal_amount_view', empty($datasession['credits_account_principal_amount']) ? '' : number_format($datasession['credits_account_principal_amount'],2) ?? '') }}" autocomplete="off" readonly/>
                                <input type="hidden" name="credits_account_principal_amount" id="credits_account_principal_amount" class="form-control form-control-lg form-control-solid" value="{{ old('credits_account_principal_amount', $datasession['credits_account_principal_amount'] ?? '') }}"/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah Angsuran') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credit_account_payment_amount_view" id="credit_account_payment_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Jumlah Angsuran" value="{{ old('credit_account_payment_amount_view', empty($datasession['credit_account_payment_amount']) ? '' : number_format($datasession['credit_account_payment_amount'],2) ?? '') }}" autocomplete="off" readonly/>
                                <input type="hidden" name="credit_account_payment_amount" id="credit_account_payment_amount" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_payment_amount', $datasession['credit_account_payment_amount'] ?? '') }}"/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Provisi') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credit_account_provisi_view" id="credit_account_provisi_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Provisi" value="{{ old('credit_account_provisi_view', empty($datasession['credit_account_provisi']) ? '' : number_format($datasession['credit_account_provisi'],2) ?? '') }}" autocomplete="off" readonly/>
                                <input type="hidden" name="credit_account_provisi" id="credit_account_provisi" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_provisi', $datasession['credit_account_provisi'] ?? '') }}"/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Survei') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credit_account_komisi_view" id="credit_account_komisi_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Survei" value="{{ old('credit_account_komisi_view', empty($datasession['credit_account_komisi']) ? '' : number_format($datasession['credit_account_komisi'],2) ?? '') }}" autocomplete="off" readonly/>
                                <input type="hidden" name="credit_account_komisi" id="credit_account_komisi" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_komisi', $datasession['credit_account_komisi'] ?? '') }}"/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Asuransi') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credit_account_insurance_view" id="credit_account_insurance_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Asuransi" value="{{ old('credit_account_insurance_view', empty($datasession['credit_account_insurance']) ? '' : number_format($datasession['credit_account_insurance'],2) ?? '') }}" autocomplete="off" readonly/>
                                <input type="hidden" name="credit_account_insurance" id="credit_account_insurance" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_insurance', $datasession['credit_account_insurance'] ?? '') }}"/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Simpanan Wajib') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credit_account_stash_view" id="credit_account_stash_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Simpanan Wajib" value="{{ old('credit_account_stash_view', empty($datasession['credit_account_stash']) ? '' : number_format($datasession['credit_account_stash'],2) ?? '') }}" autocomplete="off" readonly/>
                                <input type="hidden" name="credit_account_stash" id="credit_account_stash" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_stash', $datasession['credit_account_stash'] ?? '') }}"/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Terima Bersih') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="credit_account_amount_received_view" id="credit_account_amount_received_view" class="form-control form-control-lg form-control-solid" placeholder="Terima Bersih" value="{{ old('credit_account_amount_received_view', empty($datasession['credit_account_amount_received']) ? '' : number_format($datasession['credit_account_amount_received'],2) ?? '') }}" autocomplete="off" readonly/>
                                <input type="hidden" name="credit_account_amount_received" id="credit_account_amount_received" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_amount_received', $datasession['credit_account_amount_received'] ?? '') }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <div class="col-lg-12 fw-bold fs-3 text-white">_</div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sumber Dana') }}</label>
                            <div class="col-lg-8 fv-row">
                                @if (!empty($coremember))
                                    <select name="sumberdana" id="sumberdana" data-control="select2" data-placeholder="{{ __('Pilih Sumber Dana') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" disabled>
                                        <option value="">{{ __('Pilih') }}</option>
                                        @foreach($sumberdana as $key => $value)
                                            <option data-kt-flag="{{ $value['source_fund_id'] }}" value="{{ $value['source_fund_id'] }}" {{ $value['source_fund_id'] == old('sumberdana', $datasession['sumberdana'] ?? '') ? 'selected' :'' }}>{{ $value['source_fund_name'] }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select name="sumberdana" id="sumberdana" data-control="select2" data-placeholder="{{ __('Pilih Sumber Dana') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" disabled>
                                        <option value="">{{ __('Pilih') }}</option>
                                        @foreach($sumberdana as $key => $value)
                                            <option data-kt-flag="{{ $value['source_fund_id'] }}" value="{{ $value['source_fund_id'] }}" {{ $value['source_fund_id'] == old('sumberdana', $datasession['sumberdana'] ?? '') ? 'selected' :'' }}>{{ $value['source_fund_name'] }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Angsuran') }}</label>
                            <div class="col-lg-8 fv-row">
                                <select name="payment_type_id" id="payment_type_id" data-control="select2" data-placeholder="{{ __('Pilih Jenis Angsuran') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" disabled>
                                    <option value="">{{ __('Pilih') }}</option>
                                    @foreach($paymenttype as $key => $value)
                                        <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('payment_type_id', $datasession['payment_type_id'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Preferensi Angsuran') }}</label>
                            <div class="col-lg-8 fv-row">
                                <select name="payment_preference_id" id="payment_preference_id" data-control="select2" data-placeholder="{{ __('Pilih Preferensi Angsuran') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" disabled>
                                    <option value="">{{ __('Pilih') }}</option>
                                    @foreach($paymentpreference as $key => $value)
                                        <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('payment_preference_id', $datasession['payment_preference_id'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jangka waktu') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input name="credit_account_period" id="credit_account_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka waktu" autocomplete="off" value="{{ old('credit_account_period', $datasession['credit_account_period'] ?? '') }}" readonly>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jatuh Tempo') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input name="credit_account_due_date" id="credit_account_due_date" class="form-control form-control-lg form-control-solid" placeholder="Jatuh Tempo" autocomplete="off" value="{{ old('credit_account_due_date', date('d-m-Y') ?? '') }}" readonly>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Bunga') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input name="credit_account_interest" id="credit_account_interest" class="form-control form-control-lg form-control-solid" placeholder="%" autocomplete="off" value="{{ old('credit_account_interest', $datasession['credit_account_interest'] ?? '') }}" readonly>
                                {{-- <input type="hidden" name="credit_account_interest" id="credit_account_interest" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_interest',  $datasession['credit_account_interest'] ?? '') }}"> --}}
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Bunga') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input name="credits_account_interest_amount_view" id="credits_account_interest_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Angsuran Bunga" autocomplete="off" value="{{ old('credits_account_interest_amount_view', empty($datasession['credits_account_interest_amount']) ? '' : number_format($datasession['credits_account_interest_amount'],2) ?? '') }}" readonly>
                                <input type="hidden" name="credits_account_interest_amount" id="credits_account_interest_amount" class="form-control form-control-lg form-control-solid" value="{{ old('credits_account_interest_amount', $datasession['credits_account_interest_amount'] ?? '') }}">
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Administrasi') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input name="credit_account_adm_cost_view" id="credit_account_adm_cost_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Administrasi" autocomplete="off" value="{{ old('credit_account_adm_cost_view', empty($datasession['credit_account_adm_cost']) ? '' : number_format($datasession['credit_account_adm_cost'],2) ?? '') }}" readonly>
                                <input type="hidden" name="credit_account_adm_cost" id="credit_account_adm_cost" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_adm_cost', $datasession['credit_account_adm_cost'] ?? '') }}">
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Materai') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input name="credit_account_materai_view" id="credit_account_materai_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Materai" autocomplete="off" value="{{ old('credit_account_materai_view', empty($datasession['credit_account_materai']) ? '' : number_format($datasession['credit_account_materai'],2) ?? '') }}" readonly>
                                <input type="hidden" name="credit_account_materai" id="credit_account_materai" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_materai', $datasession['credit_account_materai'] ?? '') }}">
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Cadangan Resiko') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input name="credit_account_risk_reserve_view" id="credit_account_risk_reserve_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Cadangan Resiko" autocomplete="off" value="{{ old('credit_account_risk_reserve_view', empty($datasession['credit_account_risk_reserve']) ? '' : number_format($datasession['credit_account_risk_reserve'],2) ?? '') }}" readonly>
                                <input type="hidden" name="credit_account_risk_reserve" id="credit_account_risk_reserve" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_risk_reserve', $datasession['credit_account_risk_reserve'] ?? '') }}">
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Simpanan Pokok') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input name="credit_account_principal_view" id="credit_account_principal_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Simpanan Pokok" autocomplete="off" value="{{ old('credit_account_principal_view', empty($datasession['credit_account_principal']) ? '' : number_format($datasession['credit_account_principal'],2) ?? '') }}" readonly>
                                <input type="hidden" name="credit_account_principal" id="credit_account_principal" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_principal', $datasession['credit_account_principal'] ?? '') }}">
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Simpanan') }}</label>
                            <div class="col-lg-8 fv-row">
                                <select name="savings_account_id" id="savings_account_id" data-control="select2" data-placeholder="{{ __('Pilih No. Simpanan') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" disabled>
                                    <option value="">{{ __('Pilih') }}</option>
                                    @foreach($acctsavingsaccount as $key => $value)
                                        <option data-kt-flag="{{ $value['savings_account_id'] }}" value="{{ $value['savings_account_id'] }}" {{ $value['savings_account_id'] == old('savings_account_id', $datasession['savings_account_id'] ?? '') ? 'selected' :'' }}>{{ $value['savings_account_no'] }} - {{ $value['member_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="separator my-16"></div>
                    <div class="col-lg-12">
                        <div class="row mb-6">
                            <b class="col-lg-12 fw-bold fs-3 text-primary">Pola Angsuran</b>
                        </div>
                        <div class="row mb-6">
                            <table class="table table-rounded border gy-7 gs-7 show-border">
                                <thead>
                                    <tr align="center">
                                        <th width="5%"><b>Ke</b></th>
                                        <th width="19%"><b>Saldo Pokok</b></th>
                                        <th width="19%"><b>Angsuran Pokok</b></th>
                                        <th width="19%"><b>Angsuran Jasa</b></th>
                                        <th width="19%"><b>Total Angsuran</b></th>
                                        <th width="19%"><b>Sisa Pokok</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if(!empty($datapola)){
                                    ?> 
                                    @foreach($datapola as $key => $val)
                                        <tr>
                                            <td>{{ $val['ke'] }}</td>
                                            <td>{{ number_format(abs($val['opening_balance']),2) }}</td>
                                            <td>{{ number_format(abs($val['angsuran_pokok']),2) }}</td>
                                            <td>{{ number_format(abs($val['angsuran_bunga']),2) }}</td>
                                            <td>{{ number_format(abs($val['angsuran']),2) }}</td>
                                            <td>{{ number_format(abs($val['last_balance']),2) }}</td>
                                        </tr>
                                    @endforeach
                                    <?php }else{?>
                                        <tr>
                                            <td colspan="9" style="text-align: center">Data Kosong</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                {{-- <a type="button" href="{{ route('credits-account.print-pola-angsuran') }}" class="btn btn-primary me-2" id="kt_credits_account_add_submit">
                    Cetak Pola Angsuran
                </a> --}}
                <a type="button" href="{{ route('credits-account.print-schedule', $credits_account_id) }}" class="btn btn-sm btn-info btn-active-light-info m-1">
                   Cetak Jadwal Angsuran
                </a>
                <a type="button" href="{{ route('credits-account.print-note', $credits_account_id) }}" class="btn btn-primary" id="kt_credits_account_add_submit">
                    Cetak Kwitansi
                </a>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" tabindex="-1" id="kt_modal_angunan">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Angunan</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="bi bi-x-lg"></span>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body" id="modal-body">
                <div>
                    <div class="row mb-6">
                        <table class="table table-rounded border gy-7 gs-7 show-border">
                            <thead>
                                <tr align="center">
                                    <th width="10%"><b>No</b></th>
                                    <th width="30%"><b>Tipe</b></th>
                                    <th width="60%"><b>Keterangan</b></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                if(!empty($daftaragunan)){
                                ?> 
                                @foreach($daftaragunan as $key => $val)
                                    @if ($val['credits_agunan_type'] == "BPKB")
                                        <tr>
                                            <td>{{ $no }}</td>
                                            <td>{{ $val['credits_agunan_type'] }}</td>
                                            <td>{{ "Nomor : ".$val['credits_agunan_bpkb_nomor'].", Jenis: ".$val['credits_agunan_bpkb_type'].", Nama : ".$val['credits_agunan_bpkb_nama'].", Alamat: ".$val['credits_agunan_bpkb_address'].", Nopol : ".$val['credits_agunan_bpkb_nopol'].", No. Rangka : ".$val['credits_agunan_bpkb_no_rangka'].", No. Mesin : ".$val['credits_agunan_bpkb_no_mesin'].", Nama Dealer: ".$val['credits_agunan_bpkb_dealer_name'].", Alamat Dealer: ".$val['credits_agunan_bpkb_dealer_address'].", Taksiran : Rp. ".number_format($val['credits_agunan_bpkb_taksiran'],2).", Uang Muka Gross : Rp. ".$val['credits_agunan_bpkb_gross'].", Ket : ".$val['credits_agunan_bpkb_keterangan'] }}</td>
                                        </tr>
                                    @elseif($val['credits_agunan_type'] == "Sertifikat")
                                        <tr>
                                            <td>{{ $no }}</td>
                                            <td>{{ $val['credits_agunan_type'] }}</td>
                                            <td>{{ "Nomor : ".$val['credits_agunan_shm_no_sertifikat'].", Nama : ".$val['credits_agunan_shm_atas_nama'].", Luas : ".$val['credits_agunan_shm_luas'].", No. GS : ".$val['credits_agunan_shm_no_gs'].", Tgl. GS : ".$val['credits_agunan_shm_gambar_gs'].", Kedudukan : ".$val['credits_agunan_shm_kedudukan'].", Taksiran : Rp. ".number_format($val['credits_agunan_shm_taksiran'],2).", Ket : ".$val['credits_agunan_shm_keterangan'] }}</td>
                                        </tr>
                                    @elseif($val['credits_agunan_type'] == "ATM / Jamsostek")
                                        <tr>
                                            <td>{{ $no }}</td>
                                            <td>{{ $val['credits_agunan_type'] }}</td>
                                            <td>{{ "Nomor : ".$val['credits_agunan_atmjamsostek_nomor'].", Atas Nama : ".$val['credits_agunan_atmjamsostek_nama'].", Nama Bank : ".$val['credits_agunan_atmjamsostek_bank'].", Taksiran : Rp. ".number_format($val['credits_agunan_atmjamsostek_taksiran'],2).", Ket : ".$val['credits_agunan_atmjamsostek_keterangan'] }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>{{ $no }}</td>
                                            <td>{{ $val['credits_agunan_type'] }}</td>
                                            <td>{{ "Keterangan : ".$val['credits_agunan_other_keterangan'] }}</td>
                                        </tr>
                                    @endif
                                <?php $no++ ?>
                                @endforeach
                                <?php }else{?>
                                    <tr>
                                        <td colspan="9" style="text-align: center">Data Kosong</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</x-base-layout>
