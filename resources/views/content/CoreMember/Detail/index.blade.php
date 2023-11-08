@section('scripts')
<script>
const form = document.getElementById('kt_member_detail_view_form');

</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Detail Anggota') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('member.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_member_detail_view">
            <div class="card-body border-top p-9">
                <div class="row mb-6">
                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Anggota') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_no" class="form-control form-control-lg form-control-solid" placeholder="No Anggota" value="{{ $coremember['member_no'] }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Anggota') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Anggota" value="{{ $coremember['member_name'] }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sifat Anggota') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_character" class="form-control form-control-lg form-control-solid" placeholder="Sifat Anggota" value="{{ $membercharacter[$coremember['member_character']] }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Kelamin') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_gender" class="form-control form-control-lg form-control-solid" placeholder="Jenis Kelamin" value="{{ $membergender[$coremember['member_gender']] }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Provinsi') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="province_name" class="form-control form-control-lg form-control-solid" placeholder="Provinsi" value="{{ $coremember->province->province_name }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kabupaten') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="city_name" class="form-control form-control-lg form-control-solid" placeholder="Kabupaten" value="{{ $coremember->city->city_name }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kecamatan') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="kecamatan_name" class="form-control form-control-lg form-control-solid" placeholder="Kecamatan" value="{{ $coremember->kecamatan->kecamatan_name }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kode Pos') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_postal_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Pos" value="{{ $coremember['member_postal_code'] }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                            <div class="col-lg-8 fv-row">
                                <textarea id="member_address" name="member_address" class="form-control form-control form-control-solid" data-kt-autosize="true" placeholder="Alamat Sesuai KTP" readonly>{{ $coremember['member_address'] }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tempat Lahir') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_place_of_birth" class="form-control form-control-lg form-control-solid" placeholder="Tempat Lahir" value="{{ $coremember['member_place_of_birth'] }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Lahir') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_date_of_birth" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Lahir" value="{{ date('d-m-Y', strtotime($coremember['member_date_of_birth'])) }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Telepon') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_phone" class="form-control form-control-lg form-control-solid" placeholder="No Telepon" value="{{ $coremember['member_phone'] }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Identitas') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="No Identitas" value="{{ $coremember['member_identity_no'] }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Ibu Kandung') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_mother" class="form-control form-control-lg form-control-solid" placeholder="Nama Ibu Kandung" value="{{ $coremember['member_mother'] }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo Simp Pokok') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_principal_savings_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Pokok" value="{{ number_format($coremember['member_principal_savings_last_balance'], 2) }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo Simp Wajib') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_mandatory_savings_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Wajib" value="{{ number_format($coremember['member_mandatory_savings_last_balance'], 2) }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo Simp Khusus') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_special_savings_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp Khusus" value="{{ number_format($coremember['member_special_savings_last_balance'], 2) }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive"> 
                    <div class="row mb-6"> 
                        <div class="col-lg-4">
                            <table class="table table-rounded border gy-4 gs-4 show-border">
                                <thead>
                                    <tr align="center">
                                        <th colspan="3"><b>Daftar Tabungan</b></th>
                                    </tr>
                                    <tr align="center">
                                        <th align="center"><b>No</b></th>
                                        <th><b>No Rek Tabungan</b></th>
                                        <th><b>Jenis Tabungan</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach($coremember->savingacc as $key => $val)
                                        <tr>
                                            <th style="text-align: center">{{ $no }}</th>
                                            <th>{{ $val['savings_account_no'] }}</th>
                                            <th>{{ $val->savingdata->savings_name }}</th>
                                        </tr>
                                    <?php $no++ ?>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="col-lg-4">
                            <table class="table table-rounded border gy-4 gs-4 show-border">
                                <thead>
                                    <tr align="center">
                                        <th colspan="3"><b>Daftar Tabungan Deposito</b></th>
                                    </tr>
                                    <tr align="center">
                                        <th align="center"><b>No</b></th>
                                        <th><b>No Rek Tabungan</b></th>
                                        <th><b>Jenis Tabungan</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach($coremember->depositoacc as $key => $val)
                                        <tr>
                                            <th style="text-align: center">{{ $no }}</th>
                                            <th>{{ $val['savings_account_no'] }}</th>
                                            <th>{{ $val->deposito->savings_name }}</th>
                                        </tr>
                                    <?php $no++ ?>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="col-lg-4">
                            <table class="table table-rounded border gy-4 gs-4 show-border">
                                <thead>
                                    <tr align="center">
                                        <th colspan="3"><b>Daftar Pinjaman</b></th>
                                    </tr>
                                    <tr align="center">
                                        <th><b>No</b></th>
                                        <th><b>No Akad Pinjaman</b></th>
                                        <th><b>Jenis Pinjaman</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    @foreach($coremember->creditacc as $key => $val)
                                        <tr>
                                            <th style="text-align: center">{{ $no }}</th>
                                            <th>{{ $val['credits_account_serial'] }}</th>
                                            <th>{{ $val->credit->credits_name }}</th>
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
    </div>
</x-base-layout>

