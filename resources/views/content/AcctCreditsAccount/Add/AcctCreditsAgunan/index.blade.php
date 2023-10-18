@php
    $tipe_angunan = array(
        'BPKB' => 'BPKB',
        'Sertifikat' => 'Sertifikat',
        'Lain-lain' => 'Lain-lain',
        // 'Bilyet Simpanan Berjangka' => 'Bilyet Simpanan Berjangka',
        // 'Elektro' => 'Elektro',
        // 'Dana Keanggotaan' => 'Dana Keanggotaan',
        // 'Tabungan' => 'Tabungan',
        // 'ATM / Jamsostek' => 'ATM / Jamsostek',
    );
@endphp

<script>
    function formupdate(value) {
        if (value == 'BPKB') {
            $('#BPKB').removeClass('d-none');
            $('#Sertifikat').addClass('d-none');
            $('#atmjamsostek').addClass('d-none');
            $('#other').addClass('d-none');
        } else if (value == 'Sertifikat') {
            $('#Sertifikat').removeClass('d-none');
            $('#atmjamsostek').addClass('d-none');
            $('#BPKB').addClass('d-none');
            $('#other').addClass('d-none');
        } else if (value == 'ATM / Jamsostek') {
            $('#atmjamsostek').removeClass('d-none');
            $('#Sertifikat').addClass('d-none');
            $('#BPKB').addClass('d-none');
            $('#other').addClass('d-none');
        } else if (value == '') {
            $('#other').addClass('d-none');
            $('#atmjamsostek').addClass('d-none');
            $('#Sertifikat').addClass('d-none');
            $('#BPKB').addClass('d-none');
        } else {
            $('#other').removeClass('d-none');
            $('#atmjamsostek').addClass('d-none');
            $('#Sertifikat').addClass('d-none');
            $('#BPKB').addClass('d-none');
        }
    }

    function processAddArrayAgunan(){
        var tipe					= document.getElementById("tipe_agunan").value;
		var bpkb_nomor				= document.getElementById("bpkb_nomor").value;
		var bpkb_type				= document.getElementById("bpkb_type").value;
		var bpkb_nama 				= document.getElementById("bpkb_nama").value;
		var bpkb_address 			= document.getElementById("bpkb_address").value;
		var bpkb_nopol 				= document.getElementById("bpkb_nopol").value;
		var bpkb_no_mesin 			= document.getElementById("bpkb_no_mesin").value;
		var bpkb_no_rangka 			= document.getElementById("bpkb_no_rangka").value;
		var bpkb_dealer_name 		= document.getElementById("bpkb_dealer_name").value;
		var bpkb_dealer_address 	= document.getElementById("bpkb_dealer_address").value;
		var bpkb_taksiran 			= document.getElementById("bpkb_taksiran").value;
		var bpkb_gross 				= document.getElementById("bpkb_gross").value;
		var bpkb_keterangan 		= document.getElementById("bpkb_keterangan").value;
		var shm_no_sertifikat 		= document.getElementById("shm_no_sertifikat").value;
		var shm_luas 				= document.getElementById("shm_luas").value;
		var shm_no_gs 				= document.getElementById("shm_no_gs").value;
		var shm_tanggal_gs 			= document.getElementById("shm_tanggal_gs").value;
		var shm_kedudukan 			= document.getElementById("shm_kedudukan").value;
		var shm_atas_nama 			= document.getElementById("shm_atas_nama").value;
		var shm_taksiran 			= document.getElementById("shm_taksiran").value;
		var shm_keterangan 			= document.getElementById("shm_keterangan").value;
		var atmjamsostek_nomor 		= document.getElementById("atmjamsostek_nomor").value;
		var atmjamsostek_nama 		= document.getElementById("atmjamsostek_nama").value;
		var atmjamsostek_bank 		= document.getElementById("atmjamsostek_bank").value;
		var atmjamsostek_taksiran 	= document.getElementById("atmjamsostek_taksiran").value;
		var atmjamsostek_keterangan = document.getElementById("atmjamsostek_keterangan").value;
		var other_keterangan 		= document.getElementById("other_keterangan").value;
        const arr = [];
        $('.form-anggunan').each(function() {
        if($(this).val() != ''){
         arr.push($(this).val());}
        });
        if(arr.length==0){
           alert('Harap isi data anggunan');
        } else {
        $.ajax({
			  type: "POST",
			  url : "{{ route('credits-account.process-add-array-agunan') }}",
			  data: {
					'tipe' 						: tipe,	
					'bpkb_nomor' 				: bpkb_nomor,
					'bpkb_type' 				: bpkb_type,
					'bpkb_nama' 				: bpkb_nama,
					'bpkb_address' 				: bpkb_address,
					'bpkb_nopol' 				: bpkb_nopol, 
					'bpkb_no_mesin' 			: bpkb_no_mesin, 
					'bpkb_no_rangka' 			: bpkb_no_rangka,
					'bpkb_dealer_name' 			: bpkb_dealer_name,
					'bpkb_dealer_address' 		: bpkb_dealer_address,
					'bpkb_taksiran'				: bpkb_taksiran,
					'bpkb_gross'				: bpkb_gross,
					'bpkb_keterangan'			: bpkb_keterangan,	
					'shm_no_sertifikat' 		: shm_no_sertifikat,
					'shm_luas' 					: shm_luas, 
					'shm_no_gs' 				: shm_no_gs, 
					'shm_tanggal_gs' 			: shm_tanggal_gs, 
					'shm_kedudukan' 			: shm_kedudukan, 
					'shm_atas_nama' 			: shm_atas_nama,
					'shm_taksiran'				: shm_taksiran,
					'shm_keterangan'			: shm_keterangan,
					'atmjamsostek_nama'			: atmjamsostek_nama,
					'atmjamsostek_bank'			: atmjamsostek_bank,
					'atmjamsostek_nomor'		: atmjamsostek_nomor,
					'atmjamsostek_taksiran'		: atmjamsostek_taksiran,
					'atmjamsostek_keterangan'	: atmjamsostek_keterangan,
					'other_keterangan'			: other_keterangan,
                    '_token'                    : '{{csrf_token()}}'
				},
			  success: function(msg){
                location.reload();
			 }
        });
    }
    }
    function processDeleteArrayAgunan(record_id){
        if(confirm("Anda yakin ingin menghapus akad ?") == true){
            $.ajax({
			  type: "POST",
			  url : "{{ route('credits-account.process-delete-array-agunan') }}",
			  data: {
					'record_id'                 : record_id,
                    '_token'                    : '{{csrf_token()}}'
				},
			  success: function(msg){
                location.reload();
			 }
        });
        }
    }
</script>

<div class="row">
    <div class="col-lg-12">
        <div class="row mb-6">
            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tipe Angunan') }}</label>
            <div class="col-lg-8 fv-row">
                <select name="tipe_agunan" id="tipe_agunan" onchange="formupdate(this.value)" data-control="select2" data-placeholder="{{ __('Pilih Tipe Angunan') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                    <option value="">{{ __('Pilih') }}</option>
                    @foreach($tipe_angunan as $key => $value)
                        <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('tipe_agunan', '' ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="d-none" id="BPKB">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. BPKB') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="bpkb_nomor" id="bpkb_nomor" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="No. BPKB" autocomplete="off" value="{{ old('bpkb_nomor', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jenis Kendaraan') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="bpkb_type" id="bpkb_type" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Jenis Kendaraan" autocomplete="off" value="{{ old('bpkb_type', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="bpkb_nama" id="bpkb_nama" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Nama" autocomplete="off" value="{{ old('bpkb_nama', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                <div class="col-lg-8 fv-row">
                    <textarea type="text" id="bpkb_address" name="bpkb_address" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Alamat" autocomplete="off">{{ old('bpkb_address', ''?? '') }}</textarea>
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Pol') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="bpkb_nopol" id="bpkb_nopol" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="No. Pol" autocomplete="off" value="{{ old('bpkb_nopol', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Mesin') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="bpkb_no_mesin" id="bpkb_no_mesin" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="No. Mesin" autocomplete="off" value="{{ old('bpkb_no_mesin', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Rangka') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="bpkb_no_rangka" id="bpkb_no_rangka" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="No. Rangka" autocomplete="off" value="{{ old('bpkb_no_rangka', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Dealer') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="bpkb_dealer_name" id="bpkb_dealer_name" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Nama Dealer" autocomplete="off" value="{{ old('bpkb_dealer_name', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat Dealer') }}</label>
                <div class="col-lg-8 fv-row">
                    <textarea type="text" name="bpkb_dealer_address" id="bpkb_dealer_address" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Alamat Dealer" autocomplete="off">{{ old('bpkb_dealer_address', ''?? '') }}</textarea>
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Taksiran') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="bpkb_taksiran_view" id="bpkb_taksiran_view" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('bpkb_taksiran_view', '' ?? '') }}">
                    <input type="hidden" name="bpkb_taksiran" id="bpkb_taksiran" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('bpkb_taksiran', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Uang Muka Gross') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="bpkb_gross_view" id="bpkb_gross_view" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('bpkb_gross_view', '' ?? '') }}">
                    <input type="hidden" name="bpkb_gross" id="bpkb_gross" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('bpkb_gross', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Keterangan') }}</label>
                <div class="col-lg-8 fv-row">
                    <textarea type="text" name="bpkb_keterangan" id="bpkb_keterangan" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Alamat Dealer" autocomplete="off">{{ old('bpkb_keterangan', ''?? '') }}</textarea>
                </div>
            </div>
        </div>
        <div class="d-none" id="Sertifikat">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Sertifikat') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="shm_no_sertifikat" id="shm_no_sertifikat" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="No. Sertifikat" autocomplete="off" value="{{ old('shm_no_sertifikat', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Luas') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="shm_luas" id="shm_luas" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Luas" autocomplete="off" value="{{ old('shm_luas', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Gambar Situasi') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="shm_no_gs" id="shm_no_gs" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="No Gambar Situasi" autocomplete="off" value="{{ old('shm_no_gs', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Gambar Situasi') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="shm_tanggal_gs" id="shm_tanggal_gs" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Tanggal Gambar Situasi" autocomplete="off" value="{{ old('shm_tanggal_gs', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Atas Nama') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="shm_atas_nama" id="shm_atas_nama" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Atas Nama" autocomplete="off" value="{{ old('shm_atas_nama', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kedudukan') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="shm_kedudukan" id="shm_kedudukan" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Kedudukan" autocomplete="off" value="{{ old('shm_kedudukan', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Taksiran') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="shm_taksiran_view" id="shm_taksiran_view" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('shm_taksiran_view', '' ?? '') }}">
                    <input type="hidden" name="shm_taksiran" id="shm_taksiran" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('shm_taksiran', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Keterangan') }}</label>
                <div class="col-lg-8 fv-row">
                    <textarea type="text" name="shm_keterangan" id="shm_keterangan" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Keterangan" autocomplete="off">{{ old('shm_keterangan', ''?? '') }}</textarea>
                </div>
            </div>
        </div>
        <div class="d-none" id="atmjamsostek">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nomor ATM') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="atmjamsostek_nomor" id="atmjamsostek_nomor" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Nomor ATM" autocomplete="off" value="{{ old('atmjamsostek_nomor', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Atas Nama') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="atmjamsostek_nama" id="atmjamsostek_nama" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Atas Nama" autocomplete="off" value="{{ old('atmjamsostek_nama', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Bank') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="atmjamsostek_bank" id="atmjamsostek_bank" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Nama Bank" autocomplete="off" value="{{ old('atmjamsostek_bank', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Taksiran') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="atmjamsostek_taksiran_view" id="atmjamsostek_taksiran_view" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('atmjamsostek_taksiran_view', '' ?? '') }}">
                    <input type="hidden" name="atmjamsostek_taksiran" id="atmjamsostek_taksiran" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Rp." autocomplete="off" value="{{ old('atmjamsostek_taksiran', '' ?? '') }}">
                </div>
            </div>
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Rek. Tabungan / No. BPJS') }}</label>
                <div class="col-lg-8 fv-row">
                    <input name="atmjamsostek_keterangan" id="atmjamsostek_keterangan" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Rek. Tabungan / No. BPJS" autocomplete="off" value="{{ old('atmjamsostek_keterangan', '' ?? '') }}">
                </div>
            </div>
        </div>
        <div class="d-none" id="other">
            <div class="row mb-6">
                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Keterangan') }}</label>
                <div class="col-lg-8 fv-row">
                    <textarea type="text" id="other_keterangan" name="other_keterangan" class="form-control form-anggunan form-control-lg form-control-solid" placeholder="Keterangan" autocomplete="off">{{ old('other_keterangan', ''?? '') }}</textarea>
                </div>
            </div>
        </div>
        <div class="row mb-6">
            <button type="submit" class="btn btn-primary" onclick="processAddArrayAgunan()">
                Tambah
            </button>
        </div>
        <div class="row mb-6">
            <table class="table table-rounded border gy-7 gs-7 show-border">
                <thead>
                    <tr align="center">
                        <th width="10%"><b>No</b></th>
                        <th width="30%"><b>Tipe</b></th>
                        <th width="60%"><b>Keterangan</b></th>
                        <th width="60%"><b>Aksi</b></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if(!empty($daftaragunan)){
                    ?> 
                    @foreach($daftaragunan as $key=>$val)
                        @if ($val['credits_agunan_type'] == "BPKB")
                            <tr>
                                <td>{{ $no }}</td>
                                <td>{{ $val['credits_agunan_type'] }}</td>
                                <td>{{ "Nomor : ".$val['credits_agunan_bpkb_nomor'].", Jenis: ".$val['credits_agunan_bpkb_type'].", Nama : ".$val['credits_agunan_bpkb_nama'].", Alamat: ".$val['credits_agunan_bpkb_address'].", Nopol : ".$val['credits_agunan_bpkb_nopol'].", No. Rangka : ".$val['credits_agunan_bpkb_no_rangka'].", No. Mesin : ".$val['credits_agunan_bpkb_no_mesin'].", Nama Dealer: ".$val['credits_agunan_bpkb_dealer_name'].", Alamat Dealer: ".$val['credits_agunan_bpkb_dealer_address'].", Taksiran : Rp. ".number_format($val['credits_agunan_bpkb_taksiran'],2).", Uang Muka Gross : Rp. ".$val['credits_agunan_bpkb_gross'].", Ket : ".$val['credits_agunan_bpkb_keterangan'] }}</td>
                                <td><button onclick="processDeleteArrayAgunan('{{$val['record_id']}}')" class="btn btn-danger btn-sm">Hapus</button></td>
                            </tr>
                        @elseif($val['credits_agunan_type'] == "Sertifikat")
                            <tr>
                                <td>{{ $no }}</td>
                                <td>{{ $val['credits_agunan_type'] }}</td>
                                <td>{{ "Nomor : ".$val['credits_agunan_shm_no_sertifikat'].", Nama : ".$val['credits_agunan_shm_atas_nama'].", Luas : ".$val['credits_agunan_shm_luas'].", No. GS : ".$val['credits_agunan_shm_no_gs'].", Tgl. GS : ".$val['credits_agunan_shm_gambar_gs'].", Kedudukan : ".$val['credits_agunan_shm_kedudukan'].", Taksiran : Rp. ".number_format($val['credits_agunan_shm_taksiran'],2).", Ket : ".$val['credits_agunan_shm_keterangan'] }}</td>
                                <td><button onclick="processDeleteArrayAgunan('{{$val['record_id']}}')" class="btn btn-danger btn-sm">Hapus</button></td>
                            </tr>
                        @elseif($val['credits_agunan_type'] == "ATM / Jamsostek")
                            <tr>
                                <td>{{ $no }}</td>
                                <td>{{ $val['credits_agunan_type'] }}</td>
                                <td>{{ "Nomor : ".$val['credits_agunan_atmjamsostek_nomor'].", Atas Nama : ".$val['credits_agunan_atmjamsostek_nama'].", Nama Bank : ".$val['credits_agunan_atmjamsostek_bank'].", Taksiran : Rp. ".number_format($val['credits_agunan_atmjamsostek_taksiran'],2).", Ket : ".$val['credits_agunan_atmjamsostek_keterangan'] }}</td>
                                <td><button onclick="processDeleteArrayAgunan('{{$val['record_id']}}')" class="btn btn-danger btn-sm">Hapus</button></td>
                            </tr>
                        @else
                            <tr>
                                <td>{{ $no }}</td>
                                <td>{{ $val['credits_agunan_type'] }}</td>
                                <td>{{ "Keterangan : ".$val['credits_agunan_other_keterangan'] }}</td>
                                <td><button onclick="processDeleteArrayAgunan('{{$val['record_id']}}')" class="btn btn-danger btn-sm">Hapus</button></td>
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
