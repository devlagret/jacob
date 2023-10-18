<?php 
use \App\Http\Controllers\AcctProfitLossReportController;

if (empty($sessiondata)){
    $sessiondata['start_month_period']          = date('m');
    $sessiondata['end_month_period']            = date('m');
    $sessiondata['year_period']                 = date('Y');
    $sessiondata['profit_loss_report_type']     = 1;
    $sessiondata['branch_id']                   = auth()->user()->branch_id;
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
        <form id="kt_filter_general-ledger_form" class="form" method="POST" action="{{ route('profit-loss-report.filter') }}" enctype="multipart/form-data">
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
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Rugi Laba') }}</label>
                            <select name="profit_loss_report_type" id="profit_loss_report_type" aria-label="{{ __('Rugi Laba') }}" data-control="select2" data-placeholder="{{ __('Pilih nama rugi laba..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih nama rugi laba..') }}</option>
                                @foreach($profitlossreporttype as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('profit_loss_report_type', (int)$sessiondata['profit_loss_report_type'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
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
                    <a href="{{ route('profit-loss-report.process-shu') }}" class="btn btn-info me-2" id="kt_filter_cancel">
                        {{__('Proses SHU')}}
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
            <h3 class="card-title">Laporan Laba Rugi</h3>
            <div class="card-toolbar">
            </div>
        </div>
        <div class="card-body pt-6">
            <div class="row mb-6"> 
                <div class="col-lg-2">
                </div>
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="table table-sm table-rounded border gy-3 gs-3 show-border">
                            <thead>
                                <tr align="center">
                                    <th colspan="2" class="align-middle"><b>LAPORAN LABA RUGI</b></th>
                                </tr>
                                <tr align="center">
                                    <th colspan="2" class="align-middle"><b>{{ $company_name }}</b></th>
                                </tr>
                                <tr align="center">
                                    <th colspan="2" class="align-middle"><b>Periode {{ $monthlist[$sessiondata['start_month_period']].' - '.$monthlist[$sessiondata['end_month_period']].' '.$sessiondata['year_period'] }}</b></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $shu        = 0;    
                                    $income_tax = 0;    
                                ?>
                                <tr>
                                    <td>
                                        <table class="table table-bordered table-advance table-hover">
                                            <?php
                                                foreach ($acctprofitlossreport_top as $key => $val) {
                                                    if($val['report_tab'] == 0){
                                                        $report_tab = ' ';
                                                    } else if($val['report_tab'] == 1){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab'] == 2){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab'] == 3){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab'] == 4){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab'] == 5){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab'] == 6){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab'] == 7){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab'] == 8){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    }

                                                    if($val['report_bold'] == 1){
                                                        $report_bold = 'bold';
                                                    } else {
                                                        $report_bold = 'normal';
                                                    }
                                                    echo "<tr>";

                                                    if($val['report_type'] == 1){
                                                        echo "
                                                        <td colspan='2'><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>";
                                                    }
                                                    echo "</tr><tr>";

                                                    if($val['report_type']	== 2){
                                                        echo "
                                                        <td style='width: 75%'><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
                                                        <td style='width: 25%'><div style='font-weight:".$report_bold."'></div></td>";
                                                    }
                                                    echo "</tr><tr>";

                                                    if($val['report_type']	== 3){
                                                        $account_subtotal 	= AcctProfitLossReportController::getAccountAmount($val['account_id'], $sessiondata['start_month_period'], $sessiondata['end_month_period'], $sessiondata['year_period'], $sessiondata['profit_loss_report_type'], $sessiondata['branch_id']);

                                                        echo "
                                                        <td><div style='font-weight:".$report_bold."'>".$report_tab."(".$val['account_code'].") ".$val['account_name']."</div> </td>
                                                        <td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($account_subtotal, 2)."</div></td>";

                                                        $account_amount[$val['report_no']] = $account_subtotal;
                                                    }
                                                    echo "</tr><tr>";

                                                    if($val['report_type'] == 5){
                                                        if(!empty($val['report_formula']) && !empty($val['report_operator'])){
                                                            $report_formula 	= explode('#', $val['report_formula']);
                                                            $report_operator 	= explode('#', $val['report_operator']);

                                                            $total_account_amount	= 0;
                                                            for($i = 0; $i < count($report_formula); $i++){
                                                                if($report_operator[$i] == '-'){
                                                                    if($total_account_amount == 0 ){
                                                                        $total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
                                                                    } else {
                                                                        $total_account_amount = $total_account_amount - $account_amount[$report_formula[$i]];
                                                                    }
                                                                } else if($report_operator[$i] == '+'){
                                                                    if($total_account_amount == 0){
                                                                        $total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
                                                                    } else {
                                                                        $total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
                                                                    }
                                                                }
                                                            }

                                                            echo "
                                                            <td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
                                                            <td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($total_account_amount, 2)."</div></td>";
                                                        }
                                                    }

                                                    echo "</tr>";

                                                    if($val['report_type'] == 6){
                                                        if(!empty($val['report_formula']) && !empty($val['report_operator'])){
                                                            $report_formula 	= explode('#', $val['report_formula']);
                                                            $report_operator 	= explode('#', $val['report_operator']);
                                                            
                                                            $grand_total_account_amount1	= 0;
                                                            for($i = 0; $i < count($report_formula); $i++){
                                                                if($report_operator[$i] == '-'){
                                                                    if($grand_total_account_amount1 == 0 ){
                                                                        $grand_total_account_amount1 = $grand_total_account_amount1 + $account_amount[$report_formula[$i]];
                                                                    } else {
                                                                        $grand_total_account_amount1 = $grand_total_account_amount1 - $account_amount[$report_formula[$i]];
                                                                    }
                                                                } else if($report_operator[$i] == '+'){
                                                                    if($grand_total_account_amount1 == 0){
                                                                        $grand_total_account_amount1 = $grand_total_account_amount1 + $account_amount[$report_formula[$i]];
                                                                    } else {
                                                                        $grand_total_account_amount1 = $grand_total_account_amount1 + $account_amount[$report_formula[$i]];
                                                                    }
                                                                }
                                                            }
                                                            echo "
                                                            <td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
                                                            <td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($grand_total_account_amount1, 2)."</div></td>";
                                                        }
                                                    }
                                                }
                                            ?>
                                        </table>
                                    </td>
                                </tr>
                                <tr>	
                                    <td>
                                        <table class="table table-bordered table-advance table-hover">
                                            <?php
                                                foreach ($acctprofitlossreport_bottom as $key => $val) {
                                                    if($val['report_tab'] == 0){
                                                        $report_tab = ' ';
                                                    } else if($val['report_tab'] == 1){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab'] == 2){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab'] == 3){
                                                        $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    }

                                                    if($val['report_bold'] == 1){
                                                        $report_bold = 'bold';
                                                    } else {
                                                        $report_bold = 'normal';
                                                    }

                                                    echo "<tr>";

                                                    if($val['report_type'] == 1){
                                                        echo "
                                                        <td colspan='2'><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>";
                                                    }
                                                    echo "</tr><tr>";

                                                    if($val['report_type']	== 2){
                                                        echo "
                                                        <td style='width: 75%'><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
                                                        <td style='width: 25%'><div style='font-weight:".$report_bold."'></div></td>";
                                                    }
                                                    echo "</tr><tr>";

                                                    if($val['report_type']	== 3){
                                                        $account_subtotal 	= AcctProfitLossReportController::getAccountAmount($val['account_id'], $sessiondata['start_month_period'], $sessiondata['end_month_period'], $sessiondata['year_period'], $sessiondata['profit_loss_report_type'], $sessiondata['branch_id']);

                                                        echo "
                                                        <td><div style='font-weight:".$report_bold."'>".$report_tab."(".$val['account_code'].") ".$val['account_name']."</div> </td>
                                                        <td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($account_subtotal, 2)."</div></td>";

                                                        $account_amount[$val['report_no']] = $account_subtotal;
                                                    }
                                                    echo "</tr><tr>";

                                                    if($val['report_type'] == 5){
                                                        if(!empty($val['report_formula']) && !empty($val['report_operator'])){
                                                            $report_formula 	= explode('#', $val['report_formula']);
                                                            $report_operator 	= explode('#', $val['report_operator']);

                                                            $total_account_amount	= 0;
                                                            for($i = 0; $i < count($report_formula); $i++){
                                                                if($report_operator[$i] == '-'){
                                                                    if($total_account_amount == 0 ){
                                                                        $total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
                                                                    } else {
                                                                        $total_account_amount = $total_account_amount - $account_amount[$report_formula[$i]];
                                                                    }
                                                                } else if($report_operator[$i] == '+'){
                                                                    if($total_account_amount == 0){
                                                                        $total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
                                                                    } else {
                                                                        $total_account_amount = $total_account_amount + $account_amount[$report_formula[$i]];
                                                                    }
                                                                }
                                                            }

                                                            echo "
                                                            <td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
                                                            <td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($total_account_amount, 2)."</div></td>";
                                                        }
                                                    }

                                                    echo "</tr>";

                                                    if($val['report_type'] == 6){
                                                        if(!empty($val['report_formula']) && !empty($val['report_operator'])){
                                                            $report_formula 	= explode('#', $val['report_formula']);
                                                            $report_operator 	= explode('#', $val['report_operator']);

                                                            $grand_total_account_amount2	= 0;
                                                            for($i = 0; $i < count($report_formula); $i++){
                                                                if($report_operator[$i] == '-'){
                                                                    if($grand_total_account_amount2 == 0 ){
                                                                        $grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
                                                                    } else {
                                                                        $grand_total_account_amount2 = $grand_total_account_amount2 - $account_amount[$report_formula[$i]];
                                                                    }
                                                                } else if($report_operator[$i] == '+'){
                                                                    if($grand_total_account_amount2 == 0){
                                                                        $grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
                                                                    } else {
                                                                        $grand_total_account_amount2 = $grand_total_account_amount2 + $account_amount[$report_formula[$i]];
                                                                    }
                                                                }
                                                            }

                                                            echo "
                                                            <td><div style='font-weight:".$report_bold."'>".$report_tab."".$val['account_name']."</div></td>
                                                            <td style='text-align:right'><div style='font-weight:".$report_bold."'>".number_format($grand_total_account_amount2, 2)."</div></td>";
                                                        }
                                                    }
                                                }
                                            ?>
                                        </table>
                                    </td>
                                </tr><tr>	
                                    <td>
                                        <table class="table table-bordered table-advance table-hover">
                                            <tr>
                                                <td style="width: 70%">
                                                    <div style='font-weight:bold; font-size:16px'>
                                                        SHU SEBELUM PAJAK
                                                    </div>
                                                </td >
                                                <td style="width: 25%; text-align:right" >
                                                    <div style='font-weight:bold; font-size:16px'>
                                                        <?php
                                                            $shu = $grand_total_account_amount1 - $grand_total_account_amount2;
                                                            echo number_format($shu, 2);
                                                        ?>	
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 70%">
                                                    <div style='font-weight:bold; font-size:16px'>
                                                        PAJAK PENGHASILAN
                                                    </div>
                                                </td >
                                                <td style="width: 25%; text-align:right" >
                                                    <div style='font-weight:bold; font-size:16px'>
                                                        <?php
                                                            $income_tax 	= AcctProfitLossReportController::getAccountAmount($account_income_tax_id, $sessiondata['start_month_period'], $sessiondata['end_month_period'], $sessiondata['year_period'], $sessiondata['profit_loss_report_type'], $sessiondata['branch_id']);

                                                            echo number_format($income_tax, 2);
                                                        ?>	
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 70%">
                                                    <div style='font-weight:bold; font-size:16px'>
                                                        SHU SESUDAH PAJAK
                                                    </div>
                                                </td >
                                                <td style="width: 25%; text-align:right" >
                                                    <div style='font-weight:bold; font-size:16px'>
                                                        <?php
                                                            $shu = $grand_total_account_amount1 - $grand_total_account_amount2 - $income_tax;
                                                            echo number_format($shu, 2);
                                                        ?>	
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-2">
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('profit-loss-report.export') }}" class="btn btn-primary me-2">{{ __('Export Excel') }}</a>
            <a href="{{ route('profit-loss-report.print') }}" class="btn btn-primary">{{ __('Export PDF') }}</a>
        </div>
    </div>
</x-base-layout>