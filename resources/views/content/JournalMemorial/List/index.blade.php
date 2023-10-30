
<x-base-layout>
    <div class="card">
        <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#kt_card_collapsible">
            <h3 class="card-title">Filter</h3>
            <div class="card-toolbar rotate-180">
                <span class="bi bi-chevron-up fs-2">
                </span>
            </div>
        </div>
        <form id="kt_filter_journal_memorial_form" class="form" method="POST" action="{{ route('journal-memorial.filter') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div id="kt_card_collapsible" class="collapse">
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-4 fv-row">
                            <label class="col-form-label fw-bold fs-6 required">{{ __('Tanggal Awal') }}</label>
                            <input type="text" name="start_date" id="start_date" class="date form-control form-control-lg form-control-solid" placeholder="No. Identitas" value="{{ old('start_date', empty($session['start_date']) ? date('d-m-Y') : date('d-m-Y', strtotime($session['start_date'])) ?? '') }}" autocomplete="off"/>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-form-label fw-bold fs-6 required">{{ __('Tanggal Akhir') }}</label>
                            <input type="text" name="end_date" id="end_date" class="date form-control form-control-lg form-control-solid" placeholder="No. Identitas" value="{{ old('end_date', empty($session['end_date']) ? date('d-m-Y') : date('d-m-Y', strtotime($session['end_date'])) ?? '') }}" autocomplete="off"/>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-form-label fw-bold fs-6">{{ __('Cabang') }}</label>
                            <select name="branch_id" id="branch_id" data-control="select2" data-placeholder="{{ __('Pilih Cabang') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($corebranch as $key => $value)
                                    <option data-kt-flag="{{ $value['branch_id'] }}" value="{{ $value['branch_id'] }}" {{ $value['branch_id'] == old('branch_id', $session['branch_id'] ?? '') ? 'selected' :'' }}>{{ $value['branch_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('journal-memorial.reset-filter') }}" class="btn btn-danger me-2" id="kt_filter_cancel">
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
            <h3 class="card-title">Daftar Jurnal Memorial</h3>
        </div>
        <div class="card-body pt-6">
            <div class="table-responsive">
                <div class="row mb-6">
                    <table class="table table-rounded border  gs-7 show-border">
                        <thead>
                            <tr align="center">
                                <th width="5%"><b>No</b></th>
                                <th width="10%"><b>Bukti</b></th>
                                <th width="25%"><b>Keterangan</b></th>
                                <th width="10%"><b>Tanggal</b></th>
                                <th width="15%"><b>No. Perkiraan</b></th>
                                <th width="15%"><b>Nama Perkiraan</b></th>
                                <th width="10%"><b>Nominal</b></th>
                                <th width="10%"><b>D/K</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $no             = 1;
                                $totaldebet     = 0;
                                $totalkredit    = 0;
                            @endphp
                            @if (count($acctmemorialjournal) == 0)
                                <tr>
                                    <td colspan="8" style="text-align: center">Data Kosong</td>
                                </tr>
                            @else
                            @php
                                $id = 0;
                            @endphp
                                @foreach ($acctmemorialjournal as $val)
                                    @php
                                        if($val['journal_voucher_debit_amount'] <> 0 ){
                                            $nominal = $val['journal_voucher_debit_amount'];
                                            $status = "D";
                                        } else if($val['journal_voucher_credit_amount'] <> 0){
                                            $nominal = $val['journal_voucher_credit_amount'];
                                            $status = "K";
                                        }
                                    @endphp
                                    @if ($val['journal_voucher_id'] != $id)
                                        <tr>			
                                            <td style="text-align:center; background-color:lightgrey">{{ $no++ }}</td>
                                            <td style="text-align:left; background-color:lightgrey">{{ $val['transaction_module_code'] }}</td>
                                            <td style="text-align:left; background-color:lightgrey">{{ $val['journal_voucher_description'] }}</td>
                                            <td style="text-align:center; background-color:lightgrey">{{ date('d-m-Y', strtotime($val['journal_voucher_date'])) }}</td>
                                            <td style="text-align:left; background-color:lightgrey">{{ $val['account_code'] }}</td>
                                            <td style="text-align:left; background-color:lightgrey">{{ $val['account_name'] }}</td>
                                            <td style="text-align:right; background-color:lightgrey">{{ number_format($nominal, 2 ) }}</td>
                                            <td style="text-align:right; background-color:lightgrey">{{ $status }}</td>
                                        </tr>
                                    @else
                                        <tr>			
                                            <td style="text-align:center"></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $val['account_code'] }}</td>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $val['account_name'] }}</td>
                                            <td style="text-align:right;">{{ number_format($nominal, 2 ) }}</td>
                                            <td style="text-align:right;">{{ $status }}</td>
                                        </tr>
                                    @endif
                                    @php
                                        $totaldebet += $val['journal_voucher_debit_amount'];
                                        $totalkredit += $val['journal_voucher_credit_amount'];	
                                        if($id != $val['journal_voucher_id']){
                                            $id = $val['journal_voucher_id'];
                                        }
                                    @endphp
                                @endforeach
                            @endif
                            <tr>
                                <td colspan="6" align="right"><b>Total Debet</td>
                                <td align="right"><b>{{ number_format($totaldebet, 2) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" align="right"><b>Totel Kredit</td>
                                <td align="right"><b>{{ number_format($totalkredit, 2) }}</b></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>