<?php 
if (empty($sessiondata)){
    $sessiondata['start_month_period']  = date('m');
    $sessiondata['end_month_period']    = date('m');
    $sessiondata['year_period']         = date('Y');
    $sessiondata['account_id']          = null;
    $sessiondata['branch_id']           = auth()->user()->branch_id;
}
?>
<x-base-layout>
    <div class="card">
        <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#kt_card_collapsible">
            <h3 class="card-title">Filter</h3>
            <div class="card-toolbar rotate-180">
                <span class="bi bi-chevron-up fs-2">
                </span>
            </div>
        </div>
        <form id="kt_filter_general-ledger_form" class="form" method="POST" action="{{ route('general-ledger-report.filter') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div id="kt_card_collapsible" class="collapse">
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Periode Mulai') }}</label>
                            <select name="start_month_period" id="start_month_period" aria-label="{{ __('Periode Mulai') }}" data-control="select2" data-placeholder="{{ __('Pilih periode mulai..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih periode mulai..') }}</option>
                                @foreach($monthlist as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ (int)$key === old('start_month_period', (int)$sessiondata['start_month_period'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Periode Akhir') }}</label>
                            <select name="end_month_period" id="end_month_period" aria-label="{{ __('Periode Akhir') }}" data-control="select2" data-placeholder="{{ __('Pilih periode akhir..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih periode akhir..') }}</option>
                                @foreach($monthlist as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ (int)$key === old('end_month_period', (int)$sessiondata['end_month_period'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tahun') }}</label>
                            <select name="year_period" id="year_period" aria-label="{{ __('Tahun') }}" data-control="select2" data-placeholder="{{ __('Pilih tahun..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih tahun..') }}</option>
                                @foreach($year as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('year_period', (int)$sessiondata['year_period'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <div class="col-lg-6 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama Perkiraan') }}</label>
                            <select name="account_id" id="account_id" aria-label="{{ __('Nama Perkiraan') }}" data-control="select2" data-placeholder="{{ __('Pilih nama perkiraan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih nama perkiraan..') }}</option>
                                @foreach($acctaccount as $key => $value)
                                    <option data-kt-flag="{{ $value['account_id'] }}" value="{{ $value['account_id'] }}" {{ $value['account_id'] === old('account_id', (int)$sessiondata['account_id'] ?? '') ? 'selected' :'' }}>{{ $value['account_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Cabang') }}</label>
                            <select name="branch_id" id="branch_id" aria-label="{{ __('Cabang') }}" data-control="select2" data-placeholder="{{ __('Pilih cabang..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih cabang..') }}</option>
                                @foreach($corebranch as $key => $value)
                                    <option data-kt-flag="{{ $value['branch_id'] }}" value="{{ $value['branch_id'] }}" {{ $value['branch_id'] === old('branch_id', (int)$sessiondata['branch_id'] ?? '') ? 'selected' :'' }}>{{ $value['branch_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('general-ledger-report.filter-reset') }}" class="btn btn-danger me-2" id="kt_filter_cancel">
                        {{__('Batal')}}
                    </a>
                    <button type="submit" class="btn btn-success" id="kt_filter_search">
                        {{__('Cari')}}
                    </button>
                </div>
            </div>
        </form>
    </div>
    <br>
    <br>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Buku Besar</h3>
            <div class="card-toolbar">
            </div>
        </div>
        <div class="card-body pt-6">
            <div class="table-responsive"> 
                <div class="col-lg-12">
                    <table class="table table-sm table-rounded border gy-3 gs-3 show-border">
                        <thead>
                            <tr align="center">
                                <th rowspan="2" class="align-middle"><b>No</b></th>
                                <th rowspan="2" class="align-middle"><b>Tanggal</b></th>
                                <th rowspan="2" class="align-middle"><b>No Jurnal</b></th>
                                <th rowspan="2" class="align-middle"><b>Deskripsi</b></th>
                                <th rowspan="2" class="align-middle"><b>Nama Perkiraan</b></th>
                                <th rowspan="2" class="align-middle"><b>Debet</b></th>
                                <th rowspan="2" class="align-middle"><b>Kredit</b></th>
                                <th colspan="2" class="align-middle"><b>Saldo</b></th>
                            </tr>
                            <tr align="center">
                                <th><b>Debet</b></th>
                                <th><b>Kredit</b></th>
                            </tr>
                        </thead>
						<tbody>
							<tr>
								<td colspan="5" align="center"><b> Saldo Awal</b></td>
								<td></td>
								<td></td>
								<?php 
									if($account_id_status == 0){
										if($opening_balance_amount >= 0){
											echo "
												<td style='text-align: right'>".number_format($opening_balance_amount, 2)."</td>
												<td style='text-align: right'>0.00</td>
											";
										} else {
											echo "
												<td style='text-align: right'>0.00</td>
												<td style='text-align: right'>".number_format($opening_balance_amount, 2)."</td>
											";
										}
									} else {
										if($opening_balance_amount >= 0){
											echo "
												<td style='text-align: right'>0.00</td>
												<td style='text-align: right'>".number_format($opening_balance_amount, 2)."</td>
											";
										} else {
											echo "
												<td style='text-align: right'>".number_format($opening_balance_amount, 2)."</td>
												<td style='text-align: right'>0.00</td>
											";
										}
									}
								?>
							</tr>
							<?php
								$no                     = 1;
								$last_balance_debet 	= 0;
								$last_balance_credit	= 0;
								$total_debet 			= 0;
								$total_kredit 			= 0;
								if(!empty($acctgeneralledgerreport)){	
									foreach ($acctgeneralledgerreport as $key=>$val){	
										echo"
											<tr>			
												<td style='text-align:center'>$no.</td>
												<td style='text-align:center'>".date('d-m-Y', strtotime($val['transaction_date']))."</td>
												<td>".$val['transaction_no']."</td>
												<td>".$val['transaction_description']."</td>
												<td>".$val['account_name']."</td>
												<td style='text-align:right'>".number_format($val['account_in'], 2)."</td>
												<td style='text-align:right'>".number_format($val['account_out'], 2)."</td>
												<td style='text-align:right'>".number_format($val['last_balance_debet'], 2)."</td>
												<td style='text-align:right'>".number_format($val['last_balance_credit'], 2)."</td>
											</tr>
										";
										$no++;

										$last_balance_debet 	= $val['last_balance_debet'];
										$last_balance_credit 	= $val['last_balance_credit'];

										$total_debet 			+= $val['account_in'];
										$total_kredit			+= $val['account_out'];
									} 
								} else {
									if($account_id_status == 0){
										if($opening_balance_amount >= 0){
											$last_balance_debet 	= $opening_balance_amount;
											$last_balance_credit 	= 0;
										} else {
											$last_balance_debet 	= 0;
											$last_balance_credit 	= $opening_balance_amount;
										}
									} else {
										if($opening_balance_amount >= 0){
											$last_balance_debet 	= 0;
											$last_balance_credit 	= $opening_balance_amount;
										} else {
											$last_balance_debet 	= $opening_balance_amount;
											$last_balance_credit 	= 0;
										}
									}
								}
							?>
                            <tr>
                                <td colspan="5" align="center"><b> Total Debet Kredit</b></td>
                                <td style="text-align: right"><?php echo number_format($total_debet, 2); ?></td>
                                <td style="text-align: right"><?php echo number_format($total_kredit, 2); ?></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" align="center"><b> Saldo Akhir</b></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: right"><?php echo number_format($last_balance_debet, 2); ?></td>
                                <td style="text-align: right"><?php echo number_format($last_balance_credit, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('general-ledger-report.export') }}" class="btn btn-primary me-2">{{ __('Export Excel') }}</a>
            <a href="{{ route('general-ledger-report.print') }}" class="btn btn-primary">{{ __('Export PDF') }}</a>
        </div>
    </div>
</x-base-layout>