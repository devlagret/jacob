@php
    use App\Http\Controllers\BalanceSheetController;
    $day 	= date('d');
    $month 	= empty($session['month_period']) ? date('m') : $session['month_period'];
    $year 	= empty($session['year_period']) ? date('Y') : $session['year_period'];

    if($month == 12){
        $last_month 	= 01;
        $last_year 		= $year + 1;
    } else {
        $last_month 	= $month + 1;
        $last_year 		= $year;
    }

    switch ($month) {
        case '01':
            $month_name = "Januari";
            break;
        case '02':
            $month_name = "Februari";
            break;
        case '03':
            $month_name = "Maret";
            break;
        case '04':
            $month_name = "April";
            break;
        case '05':
            $month_name = "Mei";
            break;
        case '06':
            $month_name = "Juni";
            break;
        case '07':
            $month_name = "Juli";
            break;
        case '08':
            $month_name = "Agustus";
            break;
        case '09':
            $month_name = "September";
            break;
        case '10':
            $month_name = "Oktober";
            break;
        case '11':
            $month_name = "November";
            break;
        case '12':
            $month_name = "Desember";
            break;
        
        default:
            break;
    }

    $period = $day." ".$month_name." ".$year;

    $year_now = date('Y');
    for($i=($year_now-2); $i<($year_now+2); $i++){
        $yearlist[$i] = $i;
    } 
@endphp

<x-base-layout>
    <div class="card">
        <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#kt_card_collapsible">
            <h3 class="card-title">Filter</h3>
            <div class="card-toolbar rotate-180">
                <span class="bi bi-chevron-up fs-2">
                </span>
            </div>
        </div>
        <form id="kt_filter_savings_account_form" class="form" method="POST" action="{{ route('balance-sheet.filter') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div id="kt_card_collapsible" class="collapse">
                <div class="card-body pt-6">
                    <div class="row mb-6">
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Periode') }}</label>
                            <select name="month_period" id="month_period" aria-label="{{ __('Periode') }}" data-control="select2" data-placeholder="{{ __('Pilih Periode..') }}" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih Periode..') }}</option>
                                @foreach($monthlist as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('month_period', empty($session['month_period']) ? date('m') : $session['month_period'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tahun') }}</label>
                            <select name="year_period" id="year_period" aria-label="{{ __('Tahun') }}" data-control="select2" data-placeholder="{{ __('Pilih Tahun..') }}" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih Tahun..') }}</option>
                                @foreach($yearlist as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('year_period', empty($session['year_period']) ? date('Y') : $session['year_period'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 fv-row">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Cabang') }}</label>
                            <select name="branch_id" id="branch_id" aria-label="{{ __('Cabang') }}" data-control="select2" data-placeholder="{{ __('Pilih cabang..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih cabang..') }}</option>
                                @foreach($corebranch as $key => $value)
                                    <option data-kt-flag="{{ $value['branch_id'] }}" value="{{ $value['branch_id'] }}" {{ $value['branch_id'] == old('branch_id', $session['branch_id'] ?? '') ? 'selected' :'' }}>{{ $value['branch_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('balance-sheet.reset-filter') }}" class="btn btn-danger me-2" id="kt_filter_cancel">
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
            <h3 class="card-title">Laporan Neraca</h3>
            <div class="card-toolbar">
            </div>
        </div>
        <div class="card-body pt-6">
            <div class="table-responsive">
                <div class="row mb-6">
                    <table class="table table-rounded border gy-7 gs-7 show-border">
                    <thead>
                            <tr align="center">
                                <th colspan="2"><b>LAPORAN NERACA</b></th>
                            </tr>
                            <tr align="center">
                                <th colspan="2"><b>{{ $preferencecompany['company_name'] }}</b></th>
                            </tr>
                            <tr align="center">
                                <th colspan="2"><b>Periode {{ $period }}</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="width: 50%">
                                    <table class="table table-rounded border gy-7 gs-7 show-border">
                                        <?php
                                            $grand_total_account_amount1 = 0;
                                            $total_account_amount10	= 0;
                                            $grand_total_account_amount2 = 0;
                                                foreach ($acctbalancesheetreport_left as $key => $val) {
                                                    if($val['report_tab1'] == 0){
                                                        $report_tab1 = ' ';
                                                    } else if($val['report_tab1'] == 1){
                                                        $report_tab1 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab1'] == 2){
                                                        $report_tab1 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    } else if($val['report_tab1'] == 3){
                                                        $report_tab1 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                    }

                                                    if($val['report_bold1'] == 1){
                                                        $report_bold1 = 'bold';
                                                    } else {
                                                        $report_bold1 = 'normal';
                                                    }

                                                    echo "
                                                        <tr>
                                                    ";

                                                        if($val['report_type1'] == 1){
                                                            echo "
                                                                <td colspan='2'><div style='font-weight:".$report_bold1."'>".$report_tab1."".$val['account_name1']."</div></td>
                                                            ";
                                                        }
                                                        
                                                    echo "
                                                        </tr>
                                                    ";

                                                    echo "
                                                        <tr>
                                                    ";

                                                        if($val['report_type1']	== 2){
                                                            echo "
                                                                <td style='width: 75%'><div style='font-weight:".$report_bold1."'>".$report_tab1."".$val['account_name1']."</div></td>
                                                                <td style='width: 25%'><div style='font-weight:".$report_bold1."'></div></td>
                                                            ";
                                                        }
                                                            
                                                    echo "
                                                        </tr>
                                                    ";

                                                    echo "
                                                        <tr>
                                                    ";

                                                        if($val['report_type1']	== 3){
                                                            $last_balance1 	= BalanceSheetController::getLastBalance($val['account_id1'], empty($session['branch_id']) ? auth()->user()->branch_id : $session['branch_id'], $last_month, $last_year);

                                                            echo "
                                                                <td><div style='font-weight:".$report_bold1."'>".$report_tab1."(".$val['account_code1'].") ".$val['account_name1']."</div> </td>
                                                                <td style='text-align:right'><div style='font-weight:".$report_bold1."'>".number_format($last_balance1, 2)."</div></td>
                                                            ";

                                                            $account_amount1_top[$val['report_no']] = $last_balance1;
                                                        }
                                                            
                                                    echo "
                                                        </tr>
                                                    ";

                                                    echo "
                                                        <tr>
                                                    ";
                                                        $grand_total_account_name1 = '';
                                                        if($val['report_type1'] == 4){
                                                            if(!empty($val['report_formula1']) && !empty($val['report_operator1'])){
                                                                $grand_total_account_name1  = $val['account_name1'];
                                                                $report_formula1 	        = explode('#', $val['report_formula1']);
                                                                $report_operator1 	        = explode('#', $val['report_operator1']);

                                                                $total_account_amount1	= 0;
                                                                for($i = 0; $i < count($report_formula1); $i++){
                                                                    if($report_operator1[$i] == '-'){
                                                                        if($total_account_amount1 == 0 ){
                                                                            $total_account_amount1 = $total_account_amount1 + $account_amount1_top[$report_formula1[$i]];
                                                                        } else {
                                                                            $total_account_amount1 = $total_account_amount1 - $account_amount1_top[$report_formula1[$i]];
                                                                        }
                                                                    } else if($report_operator1[$i] == '+'){
                                                                        if($total_account_amount1 == 0){
                                                                            $total_account_amount1 = $total_account_amount1 + $account_amount1_top[$report_formula1[$i]];
                                                                        } else {
                                                                            $total_account_amount1 = $total_account_amount1 + $account_amount1_top[$report_formula1[$i]];
                                                                        }
                                                                    }
                                                                }

                                                                $grand_total_account_amount1 = $grand_total_account_amount1 + $total_account_amount1;

                                                                echo "
                                                                    <td><div style='font-weight:".$report_bold1."'>".$report_tab1."".$val['account_name1']."</div></td>
                                                                    <td style='text-align:right'><div style='font-weight:".$report_bold1."'>".number_format($total_account_amount1+$total_account_amount10, 2)."</div></td>
                                                                ";
                                                            }
                                                        }

                                                    echo "			
                                                        </tr>
                                                    ";

                                                    echo "
                                                        <tr>
                                                    ";
                                                        if($val['report_type1']	== 5){
                                                            $last_balance10 	= BalanceSheetController::getLastBalance($val['account_id1'], empty($session['branch_id']) ? auth()->user()->branch_id : $session['branch_id'], $last_month, $last_year);

                                                            $account_amount10_top[$val['report_no']] = $last_balance10;
                                                        }	
                                                    echo "
                                                        </tr>
                                                    ";

                                                    echo "
                                                        <tr>
                                                    ";

                                                        if($val['report_type1'] == 6){
                                                            if(!empty($val['report_formula1']) && !empty($val['report_operator1'])){
                                                                $report_formula1 	= explode('#', $val['report_formula1']);
                                                                $report_operator1 	= explode('#', $val['report_operator1']);

                                                                for($i = 0; $i < count($report_formula1); $i++){
                                                                    if($report_operator1[$i] == '-'){
                                                                        if($total_account_amount10 == 0 ){
                                                                            $total_account_amount10 = $total_account_amount10 + $account_amount10_top[$report_formula1[$i]];
                                                                        } else {
                                                                            $total_account_amount10 = $total_account_amount10 - $account_amount10_top[$report_formula1[$i]];
                                                                        }
                                                                    } else if($report_operator1[$i] == '+'){
                                                                        if($total_account_amount10 == 0){
                                                                            $total_account_amount10 = $total_account_amount10 + $account_amount10_top[$report_formula1[$i]];
                                                                        } else {
                                                                            $total_account_amount10 = $total_account_amount10 + $account_amount10_top[$report_formula1[$i]];
                                                                        }
                                                                    }
                                                                }

                                                                $grand_total_account_amount1 = $grand_total_account_amount1 + $total_account_amount10;

                                                                echo "
                                                                    <td><div style='font-weight:".$report_bold1."'>".$report_tab1."".$val['account_name1']."</div></td>
                                                                    <td style='text-align:right'><div style='font-weight:".$report_bold1."'>".number_format($total_account_amount10, 2)."</div></td>
                                                                ";
                                                            }
                                                        }

                                                    echo "			
                                                        </tr>
                                                    ";
                                                }
                                            ?>
                                    </table>
                                </td>
                                <td style="width: 50%">
                                    <table class="table table-rounded border gy-7 gs-7 show-border">
                                        <?php
                                            foreach ($acctbalancesheetreport_right as $key => $val) {
                                                if($val['report_tab2'] == 0){
                                                    $report_tab2 = ' ';
                                                } else if($val['report_tab2'] == 1){
                                                    $report_tab2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                } else if($val['report_tab2'] == 2){
                                                    $report_tab2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                } else if($val['report_tab2'] == 3){
                                                    $report_tab2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                                }

                                                if($val['report_bold2'] == 1){
                                                    $report_bold2 = 'bold';
                                                } else {
                                                    $report_bold2 = 'normal';
                                                }

                                                echo "
                                                    <tr>
                                                ";

                                                    if($val['report_type2'] == 1){
                                                        echo "
                                                            <td colspan='2'><div style='font-weight:".$report_bold2."'>".$report_tab2."".$val['account_name2']."</div></td>
                                                        ";
                                                    }
                                                    
                                                echo "
                                                    </tr>
                                                ";

                                                echo "
                                                    <tr>
                                                ";

                                                    if($val['report_type2']	== 2){
                                                        echo "
                                                            <td style='width: 75%'><div style='font-weight:".$report_bold2."'>".$report_tab2."".$val['account_name2']."</div></td>
                                                            <td style='width: 25%'><div style='font-weight:".$report_bold2."'></div></td>
                                                        ";
                                                    }
                                                        
                                                echo "
                                                    </tr>
                                                ";

                                                echo "
                                                    <tr>
                                                ";

                                                    if($val['report_type2']	== 3){
                                                        
                                                        $last_balance2 	= BalanceSheetController::getLastBalance($val['account_id2'], empty($session['branch_id']) ? auth()->user()->branch_id : $session['branch_id'], $last_month, $last_year);

                                                        echo "
                                                            <td><div style='font-weight:".$report_bold2."'>".$report_tab2."(".$val['account_code2'].") ".$val['account_name2']."</div> </td>
                                                            <td style='text-align:right'><div style='font-weight:".$report_bold2."'>".number_format($last_balance2, 2)."</div></td>
                                                        ";

                                                        $account_amount2_bottom[$val['report_no']] = $last_balance2;
                                                    }

                                                echo "
                                                    </tr>
                                                ";

                                                echo "
                                                    <tr>
                                                ";
                                                    $grand_total_account_name2 = '';
                                                    if($val['report_type2'] == 4){
                                                        if(!empty($val['report_formula2']) && !empty($val['report_operator2'])){
                                                            $grand_total_account_name2  = $val['account_name2'];
                                                            $report_formula2 	        = explode('#', $val['report_formula2']);
                                                            $report_operator2 	        = explode('#', $val['report_operator2']);

                                                            $total_account_amount2	= 0;
                                                            for($i = 0; $i < count($report_formula2); $i++){
                                                                if($report_operator2[$i] == '-'){
                                                                    if($total_account_amount2 == 0 ){
                                                                        $total_account_amount2 = $total_account_amount2 + $account_amount2_bottom[$report_formula2[$i]];
                                                                    } else {
                                                                        $total_account_amount2 = $total_account_amount2 - $account_amount2_bottom[$report_formula2[$i]];
                                                                    }
                                                                } else if($report_operator2[$i] == '+'){
                                                                    if($total_account_amount2 == 0){
                                                                        $total_account_amount2 = $total_account_amount2 + $account_amount2_bottom[$report_formula2[$i]];
                                                                    } else {
                                                                        $total_account_amount2 = $total_account_amount2 + $account_amount2_bottom[$report_formula2[$i]];
                                                                    }
                                                                }
                                                            }

                                                            $grand_total_account_amount2 = $grand_total_account_amount2 + $total_account_amount2;

                                                            echo "
                                                                <td><div style='font-weight:".$report_bold2."'>".$report_tab2."".$val['account_name2']."</div></td>
                                                                <td style='text-align:right'><div style='font-weight:".$report_bold2."'>".number_format($total_account_amount2+$total_account_amount210, 2)."</div></td>
                                                            ";
                                                        }	
                                                    }
                                                echo "			
                                                    </tr>
                                                ";

                                                echo "
                                                    <tr>
                                                ";
                                                    if($val['report_type2']	== 5){
                                                        $last_balance210 	= BalanceSheetController::getSHUTahunBerjalan($val['account_id2'], empty($session['branch_id']) ? auth()->user()->branch_id : $session['branch_id'], $month, $year);

                                                        $account_amount210_top[$val['report_no']] = $last_balance210;
                                                    }	
                                                echo "
                                                    </tr>
                                                ";

                                                echo "
                                                    <tr>
                                                ";

                                                    if($val['report_type2'] == 6){
                                                        if(!empty($val['report_formula2']) && !empty($val['report_operator2'])){
                                                            $report_formula2 	= explode('#', $val['report_formula2']);
                                                            $report_operator2 	= explode('#', $val['report_operator2']);

                                                            $total_account_amount210	= 0;
                                                            for($i = 0; $i < count($report_formula2); $i++){
                                                                if($report_operator2[$i] == '-'){
                                                                    if($total_account_amount210 == 0 ){
                                                                        $total_account_amount210 = $total_account_amount210 + $account_amount210_top[$report_formula2[$i]];
                                                                    } else {
                                                                        $total_account_amount210 = $total_account_amount210 - $account_amount210_top[$report_formula2[$i]];
                                                                    }
                                                                } else if($report_operator2[$i] == '+'){
                                                                    if($total_account_amount210 == 0){
                                                                        $total_account_amount210 = $total_account_amount210 + $account_amount210_top[$report_formula2[$i]];
                                                                    } else {
                                                                        $total_account_amount210 = $total_account_amount210 + $account_amount210_top[$report_formula2[$i]];
                                                                    }
                                                                }
                                                            }

                                                            $grand_total_account_amount2 = $grand_total_account_amount2 + $total_account_amount210;

                                                            echo "
                                                                <td><div style='font-weight:".$report_bold2."'>".$report_tab2."".$val['account_name2']."</div></td>
                                                                <td style='text-align:right'><div style='font-weight:".$report_bold2."'>".number_format($total_account_amount210, 2)."</div></td>
                                                            ";
                                                        }
                                                    }

                                                echo "			
                                                    </tr>
                                                ";
                                            }
                                        ?>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style='width: 50%'>
                                    <table class="table table-rounded border gy-7 gs-7 show-border">
                                        <tr>
                                            <?php
                                                echo "
                                                    <td style=\"width: 75%\"><div style=\"font-weight:".$report_bold1.";font-size:14px\">".$report_tab1."".$grand_total_account_name1."</div>
                                                    </td>
                                                    <td style=\"width: 25%; text-align:right;\"><div style=\"font-weight:".$report_bold1."; font-size:14px\">".number_format($grand_total_account_amount1, 2)."</div>
                                                    </td>
                                                ";
                                            ?>
                                        </tr>
                                    </table>
                                </td>

                                <td style='width: 50%'>
                                    <table class="table table-rounded border gy-7 gs-7 show-border">
                                        <tr>
                                            <?php 
                                                echo "
                                                    <td style=\"width: 75%\"><div style=\"font-weight:".$report_bold2.";font-size:14px\">".$report_tab2."".$grand_total_account_name2."</div></td>
                                                    <td style=\"width: 25%; text-align:right;\"><div style=\"font-weight:".$report_bold2."; font-size:14px\">".number_format($grand_total_account_amount2, 2)."</div></td>
                                                ";
                                            ?>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <a href="{{ route('balance-sheet.preview') }}" class="btn btn-primary me-2">
                {{__('Preview')}}
            </a>
            <a href="{{ route('balance-sheet.export') }}" class="btn btn-primary me-2">
                {{__('Cetak')}}
            </a>
        </div>
    </div>
</x-base-layout>