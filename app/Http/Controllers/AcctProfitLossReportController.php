<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctAccountBalanceDetail;
use App\Models\AcctAccountMutation;
use App\Models\AcctAccountOpeningBalance;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctProfitLoss;
use App\Models\AcctProfitLossReport;
use App\Models\AcctRecalculateLog;
use App\Models\CoreBranch;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AcctProfitLossReportController extends Controller
{
    public function index()
    {
        $sessiondata = session()->get('filter_profitlossreport');
        
        if (empty($sessiondata)){
            $sessiondata['start_month_period']          = date('m');
            $sessiondata['end_month_period']            = date('m');
            $sessiondata['year_period']                 = date('Y');
            $sessiondata['profit_loss_report_type']     = 1;
            $sessiondata['branch_id']                   = auth()->user()->branch_id;
        }
        
        $monthlist              = array_filter(Configuration::Month());
        $profitlossreporttype   = array_filter(Configuration::ProfitLossReportType());

        $corebranch             = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state', 0)
        ->get();

        $acctaccount = AcctAccount::select('account_id', 'account_name')
        ->where('data_state', 0)
        ->get();

        $company_name = PreferenceCompany::first()->company_name;
        
        $year_now 	=	date('Y');
        for($i=($year_now-2); $i<($year_now+2); $i++){
            $year[$i] = $i;
        }
        
        $acctprofitlossreport_top		= AcctProfitLossReport::select('acct_profit_loss_report.profit_loss_report_id', 'acct_profit_loss_report.report_no', 'acct_profit_loss_report.account_id', 'acct_profit_loss_report.account_code', 'acct_profit_loss_report.account_name', 'acct_profit_loss_report.report_formula', 'acct_profit_loss_report.report_operator', 'acct_profit_loss_report.report_type', 'acct_profit_loss_report.report_tab', 'acct_profit_loss_report.report_bold')
        ->where('account_name', '!=', ' ')
        ->where('account_name', '!=', '')
        ->where('account_type_id', 2)
        ->orderBy('report_no', 'ASC')
        ->get();

        $acctprofitlossreport_bottom	= AcctProfitLossReport::select('acct_profit_loss_report.profit_loss_report_id', 'acct_profit_loss_report.report_no', 'acct_profit_loss_report.account_id', 'acct_profit_loss_report.account_code', 'acct_profit_loss_report.account_name', 'acct_profit_loss_report.report_formula', 'acct_profit_loss_report.report_operator', 'acct_profit_loss_report.report_type', 'acct_profit_loss_report.report_tab', 'acct_profit_loss_report.report_bold')
        ->where('account_name', '!=', ' ')
        ->where('account_name', '!=', '')
        ->where('account_type_id', 3)
        ->orderBy('report_no', 'ASC')
        ->get();

        $account_income_tax_id = PreferenceCompany::select('account_income_tax_id')
        ->first();

        return view('content.AcctProfitLossReport.List.index', compact('monthlist', 'year', 'corebranch', 'profitlossreporttype', 'sessiondata', 'company_name', 'acctprofitlossreport_top', 'acctprofitlossreport_bottom', 'account_income_tax_id'));
    }

    public function filter(Request $request){
        if($request->start_month_period){
            $start_month_period = $request->start_month_period;
        }else{
            $start_month_period = date('m');
        }

        if($request->end_month_period){
            $end_month_period = $request->end_month_period;
        }else{
            $end_month_period = date('m');
        }

        if($request->year_period){
            $year_period = $request->year_period;
        }else{
            $year_period = date('Y');
        }

        if($request->profit_loss_report_type){
            $profit_loss_report_type = $request->profit_loss_report_type;
        }else{
            $profit_loss_report_type = null;
        }

        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = auth()->user()->branch_id;
        }

        $sessiondata = array(
            'start_month_period'        => $start_month_period,
            'end_month_period'          => $end_month_period,
            'year_period'               => $year_period,
            'profit_loss_report_type'   => $profit_loss_report_type,
            'branch_id'                 => $branch_id
        );

        session()->put('filter_profitlossreport', $sessiondata);

        return redirect('profit-loss-report');
    }

    public function filterReset(){
        session()->forget('filter_profitlossreport');

        return redirect('profit-loss-report');
    }

    public function processPrinting(){
        $preferencecompany 	= PreferenceCompany::first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $monthlist 			= array_filter(Configuration::Month());

        $sessiondata = session()->get('filter_profitlossreport');
        
        if (empty($sessiondata)){
            $sessiondata['start_month_period']          = date('m');
            $sessiondata['end_month_period']            = date('m');
            $sessiondata['year_period']                 = date('Y');
            $sessiondata['profit_loss_report_type']     = null;
            $sessiondata['branch_id']                   = auth()->user()->branch_id;
        }
        
        $acctprofitlossreport_top		= AcctProfitLossReport::select('acct_profit_loss_report.profit_loss_report_id', 'acct_profit_loss_report.report_no', 'acct_profit_loss_report.account_id', 'acct_profit_loss_report.account_code', 'acct_profit_loss_report.account_name', 'acct_profit_loss_report.report_formula', 'acct_profit_loss_report.report_operator', 'acct_profit_loss_report.report_type', 'acct_profit_loss_report.report_tab', 'acct_profit_loss_report.report_bold')
        ->where('account_name', '!=', ' ')
        ->where('account_name', '!=', '')
        ->where('account_type_id', 2)
        ->orderBy('report_no', 'ASC')
        ->get();

        $acctprofitlossreport_bottom	= AcctProfitLossReport::select('acct_profit_loss_report.profit_loss_report_id', 'acct_profit_loss_report.report_no', 'acct_profit_loss_report.account_id', 'acct_profit_loss_report.account_code', 'acct_profit_loss_report.account_name', 'acct_profit_loss_report.report_formula', 'acct_profit_loss_report.report_operator', 'acct_profit_loss_report.report_type', 'acct_profit_loss_report.report_tab', 'acct_profit_loss_report.report_bold')
        ->where('account_name', '!=', ' ')
        ->where('account_name', '!=', '')
        ->where('account_type_id', 3)
        ->orderBy('report_no', 'ASC')
        ->get();

        $branch_name 					= CoreBranch::select('branch_name')
        ->where('branch_id', $sessiondata['branch_id'])
        ->first()
        ->branch_name;

        $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 6, 6, 6);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 8);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $account_subtotal = 0;
        $income_tax = 0;

        $export = "
        
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td><div style=\"text-align: center;font-size:10; font-weight:bold;\">LAPORAN PERHITUNGAN SHU</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center;font-size:10; font-weight:bold;\">".$preferencecompany['company_name']."</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center;font-size:10;\">".$branch_name."</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center;font-size:10;\">Periode : ".$monthlist[$sessiondata['start_month_period']].' - '.$monthlist[$sessiondata['end_month_period']].' '.$sessiondata['year_period']."</div></td>
            </tr>					
        </table>
        <br>";
        
        $export .= "
        <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\">";

            $export .= "
                <tr>
                    <td width=\"10%\"></td>
                    <td width=\"80%\" style=\"border-top:1px black solid;border-left:1px black solid;border-right:1px black solid\">	
                        
                        <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\">";	
                            $export .= "";
                            foreach ($acctprofitlossreport_top as $keyTop => $valTop) {
                                if($valTop['report_tab'] == 0){
                                    $report_tab = ' ';
                                } else if($valTop['report_tab'] == 1){
                                    $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else if($valTop['report_tab'] == 2){
                                    $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else if($valTop['report_tab'] == 3){
                                    $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                }

                                if($valTop['report_bold'] == 1){
                                    $report_bold = 'bold';
                                } else {
                                    $report_bold = 'normal';
                                }									

                                if($valTop['report_type'] == 1){
                                    $export .= "
                                        <tr>
                                            <td colspan=\"2\" style='width: 100%'><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
                                        </tr>";
                                }

                                if($valTop['report_type']	== 2){
                                    $export .= "
                                    <tr>
                                        <td style=\"width: 73%\"><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
                                        <td style=\"width: 25%\"><div style='font-weight:".$report_bold."'></div></td>
                                    </tr>";
                                }									

                                if($valTop['report_type']	== 3){
                                    if($sessiondata['profit_loss_report_type'] == 1){
                                        $account_subtotal 	= AcctAccountMutation::where('acct_account_mutation.account_id', $valTop['account_id'])
                                        ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
                                        ->where('acct_account_mutation.month_period', '>=', $sessiondata['start_month_period'])
                                        ->where('acct_account_mutation.month_period', '<=', $sessiondata['end_month_period'])
                                        ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
                                        ->sum('last_balance');
                                    }else if($sessiondata['profit_loss_report_type'] == 2){
                                        $account_subtotal 	= AcctAccountMutation::where('acct_account_mutation.account_id', $valTop['account_id'])
                                        ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
                                        ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
                                        ->sum('last_balance');
                                    }

                                    $export .= "
                                        <tr>
                                            <td style=\"width: 73%\"><div style='font-weight:".$report_bold."'>".$report_tab."(".$valTop['account_code'].") ".$valTop['account_name']."</div> </td>
                                            <td style=\"text-align:right;width: 25%\">".number_format($account_subtotal, 2)."</td>
                                        </tr>";

                                    $account_amount[$valTop['report_no']] = $account_subtotal;
                                }

                                if($valTop['report_type'] == 5){
                                    if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
                                        $report_formula 	= explode('#', $valTop['report_formula']);
                                        $report_operator 	= explode('#', $valTop['report_operator']);

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
                                        $export .= "
                                        <tr>
                                            <td><div style='font-weight:".$report_bold."'>".$report_tab."".$valTop['account_name']."</div></td>
                                            <td style=\"text-align:right;\"><div style='font-weight:".$report_bold."'>".number_format($total_account_amount, 2)."</div></td>
                                        </tr>";
                                    }
                                }

                                if($valTop['report_type'] == 6){
                                    if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
                                        $report_formula 	= explode('#', $valTop['report_formula']);
                                        $report_operator 	= explode('#', $valTop['report_operator']);

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
                                    }
                                }
                            }

		        $export	.= "
		        		</table>
		        	</td>
		        	<td width=\"10%\"></td>
		        </tr>";

				$export .= "
                <tr>
                    <td width=\"10%\"></td>
                    <td width=\"80%\" style=\"border-bottom:1px black solid;border-left:1px black solid;border-right:1px black solid\">	
                        <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\">";		
                            foreach ($acctprofitlossreport_bottom as $keyBottom => $valBottom) {
                                if($valBottom['report_tab'] == 0){
                                    $report_tab = ' ';
                                } else if($valBottom['report_tab'] == 1){
                                    $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else if($valBottom['report_tab'] == 2){
                                    $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                } else if($valBottom['report_tab'] == 3){
                                    $report_tab = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                }

                                if($valBottom['report_bold'] == 1){
                                    $report_bold = 'bold';
                                } else {
                                    $report_bold = 'normal';
                                }									

                                if($valBottom['report_type'] == 1){
                                    $export .= "
                                    <tr>
                                        <td colspan=\"2\"><div style=\"font-weight:".$report_bold."\">".$report_tab."".$valBottom['account_name']."</div></td>
                                    </tr>";
                                }

                                if($valBottom['report_type'] == 2){
                                    $export .= "
                                    <tr>
                                        <td style=\"width: 73%\"><div style=\"font-weight:".$report_bold."\">".$report_tab."".$valBottom['account_name']."</div></td>
                                        <td style=\"width: 25%\"><div style=\"font-weight:".$report_bold."\"></div></td>
                                    </tr>";
                                }									

                                if($valBottom['report_type']	== 3){
                                    if($sessiondata['profit_loss_report_type'] == 1){
                                        $account_subtotal 	= AcctAccountMutation::where('acct_account_mutation.account_id', $valBottom['account_id'])
                                        ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
                                        ->where('acct_account_mutation.month_period', '>=', $sessiondata['start_month_period'])
                                        ->where('acct_account_mutation.month_period', '<=', $sessiondata['end_month_period'])
                                        ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
                                        ->sum('last_balance');
                                    }else if($sessiondata['profit_loss_report_type'] == 2){
                                        $account_subtotal 	= AcctAccountMutation::where('acct_account_mutation.account_id', $valBottom['account_id'])
                                        ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
                                        ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
                                        ->sum('last_balance');
                                    }

                                    $export .= "
                                    <tr>
                                        <td style=\"width: 73%\"><div style=\"font-weight:".$report_bold."\">".$report_tab."(".$valBottom['account_code'].") ".$valBottom['account_name']."</div> </td>
                                        <td style=\"text-align:right;width: 25%\">".number_format($account_subtotal, 2)."</td>
                                    </tr>";

                                    $account_amount[$valBottom['report_no']] = $account_subtotal;
                                }
                                

                                if($valBottom['report_type'] == 5){
                                    if(!empty($valBottom['report_formula']) && !empty($valBottom['report_operator'])){
                                        $report_formula 	= explode('#', $valBottom['report_formula']);
                                        $report_operator 	= explode('#', $valBottom['report_operator']);

                                        $total_account_amount2	= 0;
                                        for($i = 0; $i < count($report_formula); $i++){
                                            if($report_operator[$i] == '-'){
                                                if($total_account_amount2 == 0 ){
                                                    $total_account_amount2 = $total_account_amount2 + $account_amount[$report_formula[$i]];
                                                } else {
                                                    $total_account_amount2 = $total_account_amount2 - $account_amount[$report_formula[$i]];
                                                }
                                            } else if($report_operator[$i] == '+'){
                                                if($total_account_amount2 == 0){
                                                    $total_account_amount2 = $total_account_amount2 + $account_amount[$report_formula[$i]];
                                                } else {
                                                    $total_account_amount2 = $total_account_amount2 + $account_amount[$report_formula[$i]];
                                                }
                                            }
                                        }
                                        $export .= "
                                        <tr>
                                            <td><div style=\"font-weight:".$report_bold."\">".$report_tab."".$valBottom['account_name']."</div></td>
                                            <td style=\"text-align:righr;\"><div style=\"font-weight:".$report_bold."\">".number_format($total_account_amount2, 2)."</div></td>
                                        </tr>";
                                    }
                                }

                                if($valBottom['report_type'] == 6){
                                    if(!empty($valBottom['report_formula']) && !empty($valBottom['report_operator'])){
                                        $report_formula 	= explode('#', $valBottom['report_formula']);
                                        $report_operator 	= explode('#', $valBottom['report_operator']);

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
                                    }
                                }
                            }

        $export .= "
                </table>
            </td>
            <td width=\"10%\"></td>
        </tr>";

        $shu = $grand_total_account_amount1 - $grand_total_account_amount2;
        
        if($sessiondata['profit_loss_report_type'] == 1){
            $income_tax 	= AcctAccountMutation::where('acct_account_mutation.account_id', $preferencecompany['account_income_tax_id'])
            ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
            ->where('acct_account_mutation.month_period', '>=', $sessiondata['start_month_period'])
            ->where('acct_account_mutation.month_period', '<=', $sessiondata['end_month_period'])
            ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
            ->sum('last_balance');
        }else if($sessiondata['profit_loss_report_type'] == 2){
            $income_tax 	= AcctAccountMutation::where('acct_account_mutation.account_id', $preferencecompany['account_income_tax_id'])
            ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
            ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
            ->sum('last_balance');
        }

        $export .= "
            <tr>
                <td width=\"10%\"></td>
                <td style=\"border:1px black solid;\">
                    <table id=\"items\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\">
                        <tr>
                            <td style=\"width: 75%\"><div style=\"font-weight:bold;font-size:14px\">SHU SEBELUM PAJAK</div></td>
                            <td style=\"width: 23%; text-align:right;\"><div style=\"font-weight:bold; font-size:14px\">".number_format($shu, 2)."</div></td>
                        </tr>
                        <tr>
                            <td style=\"width: 75%\"><div style=\"font-weight:bold;font-size:14px\">PAJAK PENGHASILAN</div></td>
                            <td style=\"width: 23%; text-align:right;\"><div style=\"font-weight:bold; font-size:14px\">".number_format($income_tax, 2)."</div></td>
                        </tr>
                        <tr>
                            <td style=\"width: 75%\"><div style=\"font-weight:bold;font-size:14px\">SHU SETELAH PAJAK</div></td>
                            <td style=\"width: 23%; text-align:right;\"><div style=\"font-weight:bold; font-size:14px\">".number_format($shu - $income_tax, 2)."</div></td>
                        </tr>
                    </table>
                </td>
                <td width=\"10%\"></td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Laba Rugi.pdf';
        $pdf::Output($filename, 'I');
    }

    public function export(){
        $spreadsheet        = new Spreadsheet();
        $monthlist 			= array_filter(Configuration::Month());
        $preferencecompany 	= PreferenceCompany::first();

        $sessiondata = session()->get('filter_profitlossreport');
        
        if (empty($sessiondata)){
            $sessiondata['start_month_period']          = date('m');
            $sessiondata['end_month_period']            = date('m');
            $sessiondata['year_period']                 = date('Y');
            $sessiondata['profit_loss_report_type']     = null;
            $sessiondata['branch_id']                   = auth()->user()->branch_id;
        }
        
        $acctprofitlossreport_top		= AcctProfitLossReport::select('acct_profit_loss_report.profit_loss_report_id', 'acct_profit_loss_report.report_no', 'acct_profit_loss_report.account_id', 'acct_profit_loss_report.account_code', 'acct_profit_loss_report.account_name', 'acct_profit_loss_report.report_formula', 'acct_profit_loss_report.report_operator', 'acct_profit_loss_report.report_type', 'acct_profit_loss_report.report_tab', 'acct_profit_loss_report.report_bold')
        ->where('account_name', '!=', ' ')
        ->where('account_name', '!=', '')
        ->where('account_type_id', 2)
        ->orderBy('report_no', 'ASC')
        ->get();

        $acctprofitlossreport_bottom	= AcctProfitLossReport::select('acct_profit_loss_report.profit_loss_report_id', 'acct_profit_loss_report.report_no', 'acct_profit_loss_report.account_id', 'acct_profit_loss_report.account_code', 'acct_profit_loss_report.account_name', 'acct_profit_loss_report.report_formula', 'acct_profit_loss_report.report_operator', 'acct_profit_loss_report.report_type', 'acct_profit_loss_report.report_tab', 'acct_profit_loss_report.report_bold')
        ->where('account_name', '!=', ' ')
        ->where('account_name', '!=', '')
        ->where('account_type_id', 3)
        ->orderBy('report_no', 'ASC')
        ->get();

        $branch_name 					= CoreBranch::select('branch_name')
        ->where('branch_id', $sessiondata['branch_id'])
        ->first()
        ->branch_name;

        if ($sessiondata['profit_loss_report_type'] == 1){
            $period = $monthlist[$sessiondata['start_month_period']]."-".$monthlist[$sessiondata['end_month_period']]." ".$sessiondata['year_period'];
        } else {
            $period = $sessiondata['year_period'];
        }

        if(count($acctprofitlossreport_top)>=0 && count($acctprofitlossreport_bottom)>=0 ){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan Laba Rugi")
                                            ->setSubject("")
                                            ->setDescription("Laporan Laba Rugi")
                                            ->setKeywords("Laporan Laba Rugi")
                                            ->setCategory("Laporan Laba Rugi");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan Laba Rugi");

            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(50);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            
            $spreadsheet->getActiveSheet()->mergeCells("B1:C1");
            $spreadsheet->getActiveSheet()->mergeCells("B2:C2");
            $spreadsheet->getActiveSheet()->mergeCells("B3:C3");
            $spreadsheet->getActiveSheet()->mergeCells("B4:C4");

            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B2')->getFont()->setBold(true)->setSize(12);
            $spreadsheet->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3')->getFont()->setBold(true)->setSize(12);
            
            $spreadsheet->getActiveSheet()->setCellValue('B1',"Laporan Perhitungan SHU ");	
            $spreadsheet->getActiveSheet()->setCellValue('B2',$preferencecompany['company_name']);	
            $spreadsheet->getActiveSheet()->setCellValue('B3',$branch_name);	
            $spreadsheet->getActiveSheet()->setCellValue('B4',"Periode ".$period);	
            
            $j              = 5;
            $no             = 0;
            $grand_total    = 0;
            foreach($acctprofitlossreport_top as $keyTop => $valTop){
                $spreadsheet->getActiveSheet()->getStyle('B'.$j.':C'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                if($valTop['report_tab'] == 0){
                    $report_tab = ' ';
                } else if($valTop['report_tab'] == 1){
                    $report_tab = '     ';
                } else if($valTop['report_tab'] == 2){
                    $report_tab = '          ';
                } else if($valTop['report_tab'] == 3){
                    $report_tab = '               ';
                }

                if($valTop['report_bold'] == 1){
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getFont()->setBold(true);	
                    $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getFont()->setBold(true);	
                } else {
                
                }

                if($valTop['report_type'] == 1){
                    $spreadsheet->getActiveSheet()->mergeCells("B".$j.":C".$j."");
                    $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $valTop['account_name']);

                    $j++;
                }
                    
                
                if($valTop['report_type']	== 2){
                    $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $valTop['account_name']);

                    $j++;
                }
                        

                if($valTop['report_type']	== 3){
                    if($sessiondata['profit_loss_report_type'] == 1){
                        $account_subtotal 	= AcctAccountMutation::where('acct_account_mutation.account_id', $valTop['account_id'])
                        ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
                        ->where('acct_account_mutation.month_period', '>=', $sessiondata['start_month_period'])
                        ->where('acct_account_mutation.month_period', '<=', $sessiondata['end_month_period'])
                        ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
                        ->sum('last_balance');
                    }else if($sessiondata['profit_loss_report_type'] == 2){
                        $account_subtotal 	= AcctAccountMutation::where('acct_account_mutation.account_id', $valTop['account_id'])
                        ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
                        ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
                        ->sum('last_balance');
                    }

                    $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valTop['account_name']);
                    $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $report_tab.$account_subtotal);

                    $account_amount[$valTop['report_no']] = $account_subtotal;
                    $j++;
                }


                if($valTop['report_type'] == 5){
                    if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
                        $report_formula 	= explode('#', $valTop['report_formula']);
                        $report_operator 	= explode('#', $valTop['report_operator']);

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

                        $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valTop['account_name']);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $report_tab.$total_account_amount);

                        $j++;
                    }
                }

                if($valTop['report_type'] == 6){
                    if(!empty($valTop['report_formula']) && !empty($valTop['report_operator'])){
                        $report_formula 	= explode('#', $valTop['report_formula']);
                        $report_operator 	= explode('#', $valTop['report_operator']);

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

                        $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valTop['account_name']);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $report_tab.$grand_total_account_amount1);

                        $j++;
                    }
                }
            }

            $j--;

            foreach($acctprofitlossreport_bottom as $keyBottom => $valBottom){
                $spreadsheet->getActiveSheet()->getStyle('B'.$j.':C'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
                $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                if($valBottom['report_tab'] == 0){
                    $report_tab = ' ';
                } else if($valBottom['report_tab'] == 1){
                    $report_tab = '     ';
                } else if($valBottom['report_tab'] == 2){
                    $report_tab = '          ';
                } else if($valBottom['report_tab'] == 3){
                    $report_tab = '               ';
                }

                if($valBottom['report_bold'] == 1){
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getFont()->setBold(true);	
                    $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getFont()->setBold(true);	
                } else {
                
                }

                if($valBottom['report_type'] == 1){
                    $spreadsheet->getActiveSheet()->mergeCells("B".$j.":C".$j."");
                    $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $valBottom['account_name']);
                }
                    
                
                if($valBottom['report_type']	== 2){
                    $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $valBottom['account_name']);
                }
                        

                if($valBottom['report_type']	== 3){
                    if($sessiondata['profit_loss_report_type'] == 1){
                        $account_subtotal 	= AcctAccountMutation::where('acct_account_mutation.account_id', $valBottom['account_id'])
                        ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
                        ->where('acct_account_mutation.month_period', '>=', $sessiondata['start_month_period'])
                        ->where('acct_account_mutation.month_period', '<=', $sessiondata['end_month_period'])
                        ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
                        ->sum('last_balance');
                    }else if($sessiondata['profit_loss_report_type'] == 2){
                        $account_subtotal 	= AcctAccountMutation::where('acct_account_mutation.account_id', $valBottom['account_id'])
                        ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
                        ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
                        ->sum('last_balance');
                    }

                    $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valBottom['account_name']);
                    $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $report_tab.$account_subtotal);

                    $account_amount[$valBottom['report_no']] = $account_subtotal;
                }


                if($valBottom['report_type'] == 5){
                    if(!empty($valBottom['report_formula']) && !empty($valBottom['report_operator'])){
                        $report_formula 	= explode('#', $valBottom['report_formula']);
                        $report_operator 	= explode('#', $valBottom['report_operator']);

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

                        $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valBottom['account_name']);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $report_tab.$total_account_amount);
                    }
                }

                if($valBottom['report_type'] == 6){
                    if(!empty($valBottom['report_formula']) && !empty($valBottom['report_operator'])){
                        $report_formula 	= explode('#', $valBottom['report_formula']);
                        $report_operator 	= explode('#', $valBottom['report_operator']);

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
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $report_tab.$valBottom['account_name']);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $report_tab.$grand_total_account_amount2);
                    }
                }
                $j++;
            }

            $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('B'.$j.':C'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle("B".($j-3).":C".$j)->getFont()->setBold(true);	

            $shu = $grand_total_account_amount1 - $grand_total_account_amount2;
            
            if($sessiondata['profit_loss_report_type'] == 1){
                $income_tax 	= AcctAccountMutation::where('acct_account_mutation.account_id', $preferencecompany['account_id'])
                ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
                ->where('acct_account_mutation.month_period', '>=', $sessiondata['start_month_period'])
                ->where('acct_account_mutation.month_period', '<=', $sessiondata['end_month_period'])
                ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
                ->sum('last_balance');
            }else if($sessiondata['profit_loss_report_type'] == 2){
                $income_tax 	= AcctAccountMutation::where('acct_account_mutation.account_id', $preferencecompany['account_id'])
                ->where('acct_account_mutation.branch_id', $sessiondata['branch_id'])
                ->where('acct_account_mutation.year_period', $sessiondata['year_period'])
                ->sum('last_balance');
            }

            $spreadsheet->getActiveSheet()->setCellValue('B'.($j-2), "SHU SEBELUM PAJAK");
            $spreadsheet->getActiveSheet()->setCellValue('C'.($j-2), $shu);
            $spreadsheet->getActiveSheet()->setCellValue('B'.($j-1), "PAJAK PENGHASILAN");
            $spreadsheet->getActiveSheet()->setCellValue('C'.($j-1), $income_tax);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$j, "SHU SETELAH PAJAK");
            $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $shu - $income_tax);

            $i = $j+2;

            $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $branch_city = CoreBranch::select('branch_city')
            ->where('branch_id', $sessiondata['branch_id'])
            ->first()
            ->branch_city;

            $spreadsheet->getActiveSheet()->setCellValue('C'.$i, $branch_city);

            $k = $i+2;

            $spreadsheet->getActiveSheet()->getStyle('B'.$k)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('C'.$k)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $spreadsheet->getActiveSheet()->setCellValue('B'.$k, "Yang Melaporkan");
            $spreadsheet->getActiveSheet()->setCellValue('C'.$k, "Manajer");

            $l = $k+6;

            $spreadsheet->getActiveSheet()->getStyle('B'.$l)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('C'.$l)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle("B".$l.":C".$l)->getFont()->setBold(true);	

            $branch_manager = CoreBranch::select('branch_manager')
            ->where('branch_id', $sessiondata['branch_id'])
            ->first()
            ->branch_manager;

            $spreadsheet->getActiveSheet()->setCellValue('B'.$l, "ADMIN");
            $spreadsheet->getActiveSheet()->setCellValue('C'.$l, strtoupper($branch_manager));
            
            ob_clean();
            $filename='Laporan Laba Rugi.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }else{
            echo "Maaf data yang di eksport tidak ada !";
        }
    }

    public function processSHU(){
        $monthlist 			= array_filter(Configuration::Month());
        $preferencecompany 	= PreferenceCompany::first();

        $sessiondata = session()->get('filter_profitlossreport');
        
        if (empty($sessiondata)){
            $sessiondata['start_month_period']          = date('m');
            $sessiondata['end_month_period']            = date('m');
            $sessiondata['year_period']                 = date('Y');
            $sessiondata['profit_loss_report_type']     = null;
            $sessiondata['branch_id']                   = auth()->user()->branch_id;
        }

        $data_recalculate_log = array (
            "branch_id"			=> $sessiondata['branch_id'],
            "month_period"		=> $sessiondata['start_month_period'],
            "year_period"		=> $sessiondata['year_period'],
            "created_id"		=> auth()->user()->user_id,
        );

        DB::beginTransaction();

        try {
            AcctRecalculateLog::create($data_recalculate_log);
            
            $acctaccount 	= AcctAccount::where('data_state', 0)
            ->get();

            foreach ($acctaccount as $key => $val){
                $openingbalanceold 	= AcctAccountOpeningBalance::select('opening_balance')
                ->where("account_id", $val['account_id'])
                ->where("month_period", $sessiondata['start_month_period'])
                ->where("year_period", $sessiondata['year_period'])
                ->where("branch_id", $sessiondata['branch_id'])
                ->first();

                if(isset($openingbalanceold['opening_balance'])){
                    $opening_balance_old = $openingbalanceold['opening_balance'];
                }else{
                    $opening_balance_old = 0;
                }
                
//!===========================================================================================================================================
                
                //*Pakai AcctAccountBalanceDetail
                // $total_mutation_in 		= AcctAccountBalanceDetail::where("account_id", $val['account_id'])
                // ->whereMonth("transaction_date", $sessiondata['start_month_period'])
                // ->whereYear("transaction_date", $sessiondata['year_period'])
                // ->where("branch_id", $sessiondata['branch_id'])
                // ->sum('account_in');

                // $total_mutation_out 	= AcctAccountBalanceDetail::where("account_id", $val['account_id'])
                // ->whereMonth("transaction_date", $sessiondata['start_month_period'])
                // ->whereYear("transaction_date", $sessiondata['year_period'])
                // ->where("branch_id", $sessiondata['branch_id'])
                // ->sum('account_out');

                //*Pakai AccJournalVoucherItem
                $total_mutation_in 		= AcctJournalVoucherItem::join('acct_journal_voucher', 'acct_journal_voucher.journal_voucher_id', '=', 'acct_journal_voucher_item.journal_voucher_id')
                ->where('acct_journal_voucher_item.account_id', $val['account_id'])
                ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
                ->whereColumn('acct_journal_voucher_item.account_id_status', 'acct_journal_voucher_item.account_id_default_status')
                ->whereMonth('acct_journal_voucher.journal_voucher_date', $sessiondata['start_month_period'])
                ->whereYear('acct_journal_voucher.journal_voucher_date', $sessiondata['year_period'])
                ->sum(DB::raw('acct_journal_voucher_item.journal_voucher_debit_amount + acct_journal_voucher_item.journal_voucher_credit_amount'));

                $total_mutation_out 		= AcctJournalVoucherItem::join('acct_journal_voucher', 'acct_journal_voucher.journal_voucher_id', '=', 'acct_journal_voucher_item.journal_voucher_id')
                ->where('acct_journal_voucher_item.account_id', $val['account_id'])
                ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
                ->whereColumn('acct_journal_voucher_item.account_id_status', '<>', 'acct_journal_voucher_item.account_id_default_status')
                ->whereMonth('acct_journal_voucher.journal_voucher_date', $sessiondata['start_month_period'])
                ->whereYear('acct_journal_voucher.journal_voucher_date', $sessiondata['year_period'])
                ->sum(DB::raw('acct_journal_voucher_item.journal_voucher_debit_amount + acct_journal_voucher_item.journal_voucher_credit_amount'));

//!===========================================================================================================================================

                if(empty($total_mutation_in)){
                    $total_mutation_in 	= 0;
                }

                if(empty($total_mutation_out)){
                    $total_mutation_out = 0;
                }

                $last_balance 			= $total_mutation_in - $total_mutation_out;
                $opening_balance_new 	= $opening_balance_old + $last_balance;
                $next_month 			= $sessiondata['start_month_period'] + 1;

                if($next_month == 13){
                    $next_month = '01';
                    $next_year 	= $sessiondata['year_period'] + 1;
                } else {
                    if($next_month < 10){
                        $next_month = '0'.$next_month;
                    } else {
                        $next_month = $next_month;
                    }
                    $next_year 	= $sessiondata['year_period'];
                }

                $data_account_opening_balance[$key] = array (
                    'branch_id'				=> $sessiondata['branch_id'],
                    'account_id'			=> $val['account_id'],
                    'month_period'			=> $next_month,
                    'year_period'			=> $next_year,
                    'opening_balance'		=> $opening_balance_new
                );

                $data_account_mutation[$key] = array (
                    'branch_id'				=> $sessiondata['branch_id'],
                    'account_id'			=> $val['account_id'],
                    'month_period'			=> $sessiondata['start_month_period'],
                    'year_period'			=> $sessiondata['year_period'],
                    'mutation_in_amount'	=> $total_mutation_in,
                    'mutation_out_amount'	=> $total_mutation_out,
                    'last_balance'			=> $last_balance
                );
            }

            $check_data_account_opening_balance = AcctAccountOpeningBalance::select('branch_id', 'account_id', 'opening_balance', 'month_period', 'year_period')
			->where("month_period", $next_month)
			->where("year_period", $next_year)
			->where("branch_id", $sessiondata['branch_id'])
            ->get();;

            $check_data_account_mutation 		= AcctAccountMutation::select('branch_id', 'account_id', 'mutation_in_amount', 'mutation_out_amount', 'last_balance', 'month_period', 'year_period')
			->where("month_period", $sessiondata['start_month_period'])
			->where("year_period", $sessiondata['year_period'])
			->where("branch_id", $sessiondata['branch_id'])
            ->get();

            $data_state = false;
            if(count($check_data_account_opening_balance) == 0){
                if(count($check_data_account_mutation) == 0){
                    $data_state = true;	
                } else {
                    if(AcctAccountMutation::where("month_period", $sessiondata['start_month_period'])
                    ->where("year_period", $sessiondata['year_period'])
                    ->where("branch_id", $sessiondata['branch_id'])
                    ->delete()){
                        $data_state = true;
                    } else {
                        $data_state = false;
                    }
                }
            } else{
                if(AcctAccountOpeningBalance::where("month_period", $next_month)
                ->where("year_period", $next_year)
                ->where("branch_id", $sessiondata['branch_id'])
                ->delete()){

                    if(count($check_data_account_mutation) == 0){
                        $data_state = true;	
                    } else {
                        if(AcctAccountMutation::where("month_period", $sessiondata['start_month_period'])
                        ->where("year_period", $sessiondata['year_period'])
                        ->where("branch_id", $sessiondata['branch_id'])
                        ->delete()){
                            $data_state = true;
                        } else {
                            $data_state = false;
                        }
                    }
                } else {
                    $data_state = false;
                }
            }

            if($data_state == true){
                AcctAccountOpeningBalance::insert($data_account_opening_balance);
                AcctAccountMutation::insert($data_account_mutation);

                $acctprofitloss_top = AcctProfitLossReport::select('profit_loss_report_id','report_no', 'account_id', 'account_code', 'account_name', 'report_formula', 'report_operator', 'report_type', 'report_tab', 'report_bold')
                ->where('account_name', '!=', ' ')
                ->where('account_type_id', 2)
                ->orderBy('report_no', 'ASC')
                ->get();

                foreach ($acctprofitloss_top as $kp => $vp){
                    if($vp['report_type']	== 3){
                        $accountamountarray 	= AcctAccountMutation::select('last_balance')
                        ->where('acct_account_mutation.account_id', $vp['account_id'])
                        ->where('acct_account_mutation.branch_id', $sessiondata['start_month_period'])
                        ->where('acct_account_mutation.month_period', $sessiondata['year_period'])
                        ->where('acct_account_mutation.year_period', $sessiondata['branch_id'])
                        ->first();

                        if(isset($accountamountarray['last_balance'])){
                            $accountamount = $accountamountarray['last_balance'];
                        }else{
                            $accountamount = 0;
                        }

                        $account_amount[$vp['report_no']] = $accountamount;
                    }

                    if($vp['report_type'] == 5){
                        if(!empty($vp['report_formula']) && !empty($vp['report_operator'])){
                            $report_formula 	= explode('#', $vp['report_formula']);
                            $report_operator 	= explode('#', $vp['report_operator']);

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
                        }
                    }

                    if($vp['report_type'] == 6){
                        if(!empty($vp['report_formula']) && !empty($vp['report_operator'])){
                            $report_formula 	= explode('#', $vp['report_formula']);
                            $report_operator 	= explode('#', $vp['report_operator']);

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
                        }
                    }
                }

                $acctprofitloss_bottom = AcctProfitLossReport::select('profit_loss_report_id', 'report_no', 'account_id', 'account_code', 'account_name', 'report_formula', 'report_operator', 'report_type', 'report_tab', 'report_bold')
                ->where('acct_profit_loss_report.account_name', '!=', ' ')
                ->where('acct_profit_loss_report.account_type_id', 3)
                ->orderBy('acct_profit_loss_report.report_no', 'ASC')
                ->get();

                foreach ($acctprofitloss_bottom as $kb => $vb){
                    if($vb['report_type']	== 3){
                        $accountamountarray 	= AcctAccountMutation::select('last_balance')
                        ->where('account_id', $vb['account_id'])
                        ->where('branch_id', $sessiondata['start_month_period'])
                        ->where('month_period', $sessiondata['year_period'])
                        ->where('year_period', $sessiondata['branch_id'])
                        ->first();

                        if(isset($accountamountarray['last_balance'])){
                            $accountamount = $accountamountarray['last_balance'];
                        }else{
                            $accountamount = 0;
                        }

                        $account_amount[$vb['report_no']] = $accountamount;
                    }

                    if($vb['report_type'] == 5){
                        if(!empty($vb['report_formula']) && !empty($vb['report_operator'])){
                            $report_formula 	= explode('#', $vb['report_formula']);
                            $report_operator 	= explode('#', $vb['report_operator']);

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
                        }
                    }

                    if($vb['report_type'] == 6){
                        if(!empty($vb['report_formula']) && !empty($vb['report_operator'])){
                            $report_formula 	= explode('#', $vb['report_formula']);
                            $report_operator 	= explode('#', $vb['report_operator']);

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
                        }
                    }
                }

                $profit_loss_amount = $grand_total_account_amount1 - $grand_total_account_amount2;

                $data_profit_loss = array (
                    "branch_id"				=> $sessiondata['branch_id'],
                    "profit_loss_amount"	=> $profit_loss_amount,
                    "month_period"			=> $sessiondata['start_month_period'],
                    "year_period"			=> $sessiondata['year_period']

                );

                $check_profit_loss = AcctProfitLoss::select('branch_id', 'profit_loss_amount', 'month_period', 'year_period')
                ->where("branch_id", $data_profit_loss['branch_id'])
                ->where("month_period", $data_profit_loss['month_period'])
                ->where("year_period", $data_profit_loss['year_period'])
                ->get();

                if(count($check_profit_loss) == 0){
                    AcctProfitLoss::create($data_profit_loss);
                } else {
                    AcctProfitLoss::where("branch_id", $data_profit_loss['branch_id'])
                    ->where("month_period", $data_profit_loss['month_period'])
                    ->where("year_period", $data_profit_loss['year_period'])
                    ->delete();

                    AcctProfitLoss::create($data_profit_loss);
                }
            }
        
            DB::commit();
            $message = array(
                'pesan' => 'Proses SHU berhasil dilakukan',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Proses SHU gagal dilakukan',
                'alert' => 'error'
            );
        }

        return redirect('profit-loss-report')->with($message);
    }

    public static function getAccountAmount($account_id, $month_start, $month_end, $year, $profit_loss_report_type, $branch_id){
        if($profit_loss_report_type == 1){
            $account_amount = AcctAccountMutation::where('account_id', $account_id)
            ->where('branch_id', $branch_id)
            ->where('month_period', '>=', $month_start)
            ->where('month_period', '<=', $month_end)
            ->where('year_period', $year)
            ->sum('last_balance');
        }else if($profit_loss_report_type == 2){
            $account_amount = AcctAccountMutation::where('account_id', $account_id)
            ->where('branch_id', $branch_id)
            ->where('year_period', $year)
            ->sum('last_balance');
        }
        
        return $account_amount;
    }
}
