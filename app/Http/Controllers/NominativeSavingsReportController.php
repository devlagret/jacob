<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsProfitSharing;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NominativeSavingsReportController extends Controller
{
    public function index()
    {
        $corebranch                 = CoreBranch::where('data_state', 0)->get();
        $kelompoklaporansimpanan    = Configuration::KelompokLaporanSimpanan();

        return view('content.NominativeSavings.index', compact('corebranch', 'kelompoklaporansimpanan'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "start_date"	=> $request->start_date,
            "kelompok"	    => $request->kelompok,
            "branch_id"		=> $request->branch_id,
            "view"			=> $request->view,
        );

        if($sesi['view'] == 'pdf'){
            $this->processPrinting($sesi);
        } else {
            $this->export($sesi);
        }
    }

    public function processPrinting($sesi){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('logo_koperasi')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);

        if($branch_status == 1){
            if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
                $branch_id = '';
            } else {
                $branch_id = $sesi['branch_id'];
            }
        }
        
        $kelompoklaporansimpanan	= Configuration::KelompokLaporanSimpanan();	
        $acctsavings 				= AcctSavings::select('savings_id', 'savings_name')
        ->where('data_state', 0)
        ->get();
        $period 					= date('mY', strtotime($sesi['start_date']));

        if($sesi['kelompok'] == 0){
            $acctsavingsaccount     = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_account_date', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.savings_id', 'acct_savings.savings_name', 'acct_savings.savings_status', 'acct_savings.savings_interest_rate', 'acct_savings_account.data_state')
            ->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
            ->join('core_member', 'acct_savings_account.member_id', '=', 'core_member.member_id')
            ->where('acct_savings.savings_status', 0)
            ->where('acct_savings_account.data_state', 0)
			->orderBy('acct_savings_account.savings_account_no', 'ASC')
			->orderBy('acct_savings_account.savings_account_id', 'ASC')
			->orderBy('acct_savings_account.member_id', 'ASC')
			->orderBy('core_member.member_name', 'ASC')
			->orderBy('core_member.member_address', 'ASC')
			->orderBy('acct_savings_account.savings_account_date', 'ASC')
			->orderBy('acct_savings_account.savings_account_last_balance', 'ASC')
			->orderBy('acct_savings_account.savings_id', 'ASC')
            ->get();

            foreach ($acctsavingsaccount as $key => $val) {
                $savings_interest_rate	    = $val['savings_interest_rate']/12;
                $savingsinterestrate 	    = round($savings_interest_rate,2);
                $acctsavingsprofitsharing 	= AcctSavingsProfitSharing::select('acct_savings_profit_sharing.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_profit_sharing.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_profit_sharing.savings_profit_sharing_amount', 'acct_savings_profit_sharing.savings_daily_average_balance', 'acct_savings_profit_sharing.savings_account_last_balance')
                ->join('acct_savings_account', 'acct_savings_profit_sharing.savings_account_id', '=', 'acct_savings_account.savings_account_id')
                ->join('core_member', 'acct_savings_profit_sharing.member_id', '=', 'core_member.member_id')
                ->where('acct_savings_profit_sharing.savings_profit_sharing_period', $period);
                
                if(empty($val['savings_account_id'])){
                    $acctsavingsprofitsharing = $acctsavingsprofitsharing->where('acct_savings_profit_sharing.savings_account_id', $val['savings_account_id']);
                }
                if($branch_id != ''){
                    $acctsavingsprofitsharing = $acctsavingsprofitsharing->where('acct_savings_profit_sharing.branch_id', $branch_id);
                }
                $acctsavingsprofitsharing = $acctsavingsprofitsharing->first();

                if(empty($acctsavingsprofitsharing)){
                    $savings_daily_average_balance 	= 0;
                    $savings_profit_sharing_amount 	= 0;
                    $savings_account_last_balance 	= $val['savings_account_last_balance'];
                } else {
                    $savings_daily_average_balance 	= $acctsavingsprofitsharing['savings_daily_average_balance'];
                    $savings_profit_sharing_amount 	= $acctsavingsprofitsharing['savings_profit_sharing_amount'];
                    $savings_account_last_balance 	= $acctsavingsprofitsharing['savings_account_last_balance'];
                }

                $data_acctsavingsaccount[] = array (
                    'savings_account_no'			=> $val['savings_account_no'],
                    'member_name'					=> $val['member_name'],
                    'member_address'				=> $val['member_address'],
                    'savings_interest_rate'			=> $savingsinterestrate,
                    'savings_daily_average_balance'	=> $savings_daily_average_balance,
                    'savings_profit_sharing_amount'	=> $savings_profit_sharing_amount,
                    'savings_account_last_balance'	=> $savings_account_last_balance,
                );
            }
        } else {
            foreach ($acctsavings as $key => $vS) {
                $savings_interest_rate	    = $vS['savings_interest_rate']/12;
                $savingsinterestrate 	    = round($savings_interest_rate,2);
                $acctsavingsaccount = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_account_date', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.savings_id', 'acct_savings.savings_name', 'acct_savings.savings_status')
                ->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
                ->join('core_member', 'acct_savings_account.member_id', '=', 'core_member.member_id')
                ->where('acct_savings_account.data_state', 0)
                ->where('acct_savings_account.savings_id', $vS['savings_id'])
                ->orderBy('acct_savings_account.savings_account_id', 'ASC')
                ->orderBy('acct_savings_account.savings_account_no', 'ASC')
                ->orderBy('acct_savings_account.member_id', 'ASC')
                ->orderBy('core_member.member_name', 'ASC')
                ->orderBy('core_member.member_address', 'ASC')
                ->orderBy('acct_savings_account.savings_account_date', 'ASC')
                ->orderBy('acct_savings_account.savings_account_last_balance', 'ASC')
                ->orderBy('acct_savings_account.savings_id', 'ASC')
                ->orderBy('acct_savings.savings_name', 'ASC')
                ->orderBy('acct_savings.savings_status', 'ASC')
                ->get();

                foreach ($acctsavingsaccount as $key => $val) {
                    $acctsavingsprofitsharing 	 		= AcctSavingsProfitSharing::select('acct_savings_profit_sharing.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_profit_sharing.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_profit_sharing.savings_profit_sharing_amount', 'acct_savings_profit_sharing.savings_daily_average_balance', 'acct_savings_profit_sharing.savings_account_last_balance')
                    ->join('acct_savings_account', 'acct_savings_profit_sharing.savings_account_id', '=', 'acct_savings_account.savings_account_id')
                    ->join('core_member', 'acct_savings_profit_sharing.member_id', '=', 'core_member.member_id')
                    ->where('acct_savings_profit_sharing.savings_profit_sharing_period', $period);
                    if(empty($val['savings_account_id'])){
                        $acctsavingsprofitsharing = $acctsavingsprofitsharing->where('acct_savings_profit_sharing.savings_account_id', $val['savings_account_id']);
                    }
                    if($branch_id != ''){
                        $acctsavingsprofitsharing = $acctsavingsprofitsharing->where('acct_savings_profit_sharing.branch_id', $branch_id);
                    }
                    $acctsavingsprofitsharing = $acctsavingsprofitsharing->first();

                    if(empty($acctsavingsprofitsharing)){
                        $savings_daily_average_balance 	= 0;
                        $savings_profit_sharing_amount 	= 0;
                        $savings_account_last_balance 	= $val['savings_account_last_balance'];
                    } else {
                        $savings_daily_average_balance 	= $acctsavingsprofitsharing['savings_daily_average_balance'];
                        $savings_profit_sharing_amount 	= $acctsavingsprofitsharing['savings_profit_sharing_amount'];
                        $savings_account_last_balance 	= $acctsavingsprofitsharing['savings_account_last_balance'];
                    }

                    $data_acctsavingsaccount[$vS['savings_id']][] = array (
                        'savings_account_no'			=> $val['savings_account_no'],
                        'member_name'					=> $val['member_name'],
                        'member_address'				=> $val['member_address'],
                        'savings_interest_rate'			=> $savingsinterestrate,
                        'savings_daily_average_balance'	=> $savings_daily_average_balance,
                        'savings_profit_sharing_amount'	=> $savings_profit_sharing_amount,
                        'savings_account_last_balance'	=> $savings_account_last_balance,
                    );
                }
            }
        }
        
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

        $pdf::SetFont('helvetica', '', 9);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $export = "";

        if($sesi['kelompok'] == 0){
            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR NOMINATIF SIMPANAN</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: center; font-size:10px\">Periode ".$sesi['start_date']."</div></td>
                </tr>
            </table>";
        } else {
            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR NOMINATIF SIMPANAN PER JENIS</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: center; font-size:10px\">Periode ".$sesi['start_date']."</div></td>
                </tr>
            </table>";
        }

        $export .= "
        <br>
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"11%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Rek</div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                    <td width=\"7%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">Bunga</div></td>
                <td width=\"20%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Saldo Akhir</div></td>
                
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $no         = 1;
        $totalbasil = 0;
        $totalsaldo = 0;
        if($sesi['kelompok'] == 0){
            foreach ($data_acctsavingsaccount as $key => $val) {
                $export .= "
                    <tr>
                        <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                        <td width=\"11%\"><div style=\"text-align: left;\">".$val['savings_account_no']."</div></td>
                        <td width=\"25%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                        <td width=\"30%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                        <td width=\"5%\"><div style=\"text-align: left;\">".$val['savings_interest_rate']."%</div></td>
                        <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['savings_account_last_balance'], 2)."</div></td>
                    </tr>

                ";

                $totalbasil += $val['savings_profit_sharing_amount'];
                $totalsaldo += $val['savings_account_last_balance'];
                $no++;
            }
        } else { 
            $totalglobal    = 0;
            $totalsaldo     = 0;
            foreach ($acctsavings as $key => $vS) {
                if(!empty($data_acctsavingsaccount[$vS['savings_id']])){
                    $export .= "
                        <br>
                        <tr>
                            <td colspan =\"7\" width=\"100%\" style=\"border-bottom: 1px solid black;font-weight:bold\"><div style=\"font-size:10\">".$vS['savings_name']."</div></td>
                        </tr>
                        <br>
                    ";
                    
                    $nov = 1;
                        $subtotalbasil = 0;
                        $subtotalsaldo = 0;
                    foreach ($data_acctsavingsaccount[$vS['savings_id']] as $k => $v) {
                        
                        $export .= "
                            <tr>
                                <td width=\"5%\"><div style=\"text-align: left;\">".$nov."</div></td>
                                <td width=\"11%\"><div style=\"text-align: left;\">".$v['savings_account_no']."</div></td>
                                <td width=\"16%\"><div style=\"text-align: left;\">".$v['member_name']."</div></td>
                                <td width=\"20%\"><div style=\"text-align: left;\">".$v['member_address']."</div></td>
                                <td width=\"17%\"><div style=\"text-align: right;\">".number_format($v['savings_daily_average_balance'], 2)."</div></td>
                                <td width=\"15%\"><div style=\"text-align: right;\">".number_format($v['savings_profit_sharing_amount'], 2)."</div></td>
                                <td width=\"17%\"><div style=\"text-align: right;\">".number_format($v['savings_account_last_balance'], 2)."</div></td>
                            </tr>

                        ";

                        $subtotalbasil += $v['savings_profit_sharing_amount'];
                        $subtotalsaldo += $v['savings_account_last_balance'];
                        $nov++;
                    }
                    $export .= "
                        <br>
                        <tr>
                            <td colspan =\"4\"><div style=\"font-size:10;font-style:italic;text-align:right\"></div></td>
                            <td><div style=\"font-size:10;font-weight:bold;text-align:center\">Subtotal</div></td>
                            <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($subtotalbasil, 2)."</div></td>
                            <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($subtotalsaldo, 2)."</div></td>
                        </tr>
                        <br>
                    ";

                    $totalglobal 	+= $subtotalbasil;
                    $totalsaldo 	+= $subtotalsaldo;
                }
            }

        }

        $export .= "
            <br>
            <tr>
                <td colspan =\"4\"><div style=\"font-size:10;text-align:left;font-style:italic\"></div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Total </div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalsaldo, 2)."</div></td>
            </tr>
            </table>
            <br>
        ";

        // //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Nominatif Simpaman.pdf';
        $pdf::Output($filename, 'I');
    }

    public function export($sesi){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('company_name')->first();
        $spreadsheet        = new Spreadsheet();

        if($branch_status == 1){
            if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
                $branch_id = '';
            } else {
                $branch_id = $sesi['branch_id'];
            }
        }
        
        $kelompoklaporansimpanan	= Configuration::KelompokLaporanSimpanan();	
        $acctsavings 				= AcctSavings::select('savings_id', 'savings_name')
        ->where('data_state', 0)
        ->where('savings_status', 0)
        ->get();
        $period 					= date('mY', strtotime($sesi['start_date']));

        if($sesi['kelompok'] == 0){
            $acctsavingsaccount     = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_account_date', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.savings_id', 'acct_savings.savings_name', 'acct_savings.savings_status', 'acct_savings.savings_interest_rate', 'acct_savings_account.data_state')
            ->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
            ->join('core_member', 'acct_savings_account.member_id', '=', 'core_member.member_id')
            ->where('acct_savings.savings_status', 0)
            ->where('acct_savings_account.data_state', 0)
			->orderBy('acct_savings_account.savings_account_no', 'ASC')
			->orderBy('acct_savings_account.savings_account_id', 'ASC')
			->orderBy('acct_savings_account.member_id', 'ASC')
			->orderBy('core_member.member_name', 'ASC')
			->orderBy('core_member.member_address', 'ASC')
			->orderBy('acct_savings_account.savings_account_date', 'ASC')
			->orderBy('acct_savings_account.savings_account_last_balance', 'ASC')
			->orderBy('acct_savings_account.savings_id', 'ASC')
            ->get();

            foreach ($acctsavingsaccount as $key => $val) {
                $savings_interest_rate	    = $val['savings_interest_rate']/12;
                $savingsinterestrate 	    = round($savings_interest_rate,2);
                $acctsavingsprofitsharing 	= AcctSavingsProfitSharing::select('acct_savings_profit_sharing.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_profit_sharing.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_profit_sharing.savings_profit_sharing_amount', 'acct_savings_profit_sharing.savings_daily_average_balance', 'acct_savings_profit_sharing.savings_account_last_balance')
                ->join('acct_savings_account', 'acct_savings_profit_sharing.savings_account_id', '=', 'acct_savings_account.savings_account_id')
                ->join('core_member', 'acct_savings_profit_sharing.member_id', '=', 'core_member.member_id')
                ->where('acct_savings_profit_sharing.savings_profit_sharing_period', $period);
                
                if(empty($val['savings_account_id'])){
                    $acctsavingsprofitsharing = $acctsavingsprofitsharing->where('acct_savings_profit_sharing.savings_account_id', $val['savings_account_id']);
                }
                if($branch_id != ''){
                    $acctsavingsprofitsharing = $acctsavingsprofitsharing->where('acct_savings_profit_sharing.branch_id', $branch_id);
                }
                $acctsavingsprofitsharing = $acctsavingsprofitsharing->first();

                if(empty($acctsavingsprofitsharing)){
                    $savings_daily_average_balance 	= 0;
                    $savings_profit_sharing_amount 	= 0;
                    $savings_account_last_balance 	= $val['savings_account_last_balance'];
                } else {
                    $savings_daily_average_balance 	= $acctsavingsprofitsharing['savings_daily_average_balance'];
                    $savings_profit_sharing_amount 	= $acctsavingsprofitsharing['savings_profit_sharing_amount'];
                    $savings_account_last_balance 	= $acctsavingsprofitsharing['savings_account_last_balance'];
                }

                $data_acctsavingsaccount[] = array (
                    'savings_account_no'			=> $val['savings_account_no'],
                    'member_name'					=> $val['member_name'],
                    'member_address'				=> $val['member_address'],
                    'savings_interest_rate'			=> $savingsinterestrate,
                    'savings_daily_average_balance'	=> $savings_daily_average_balance,
                    'savings_profit_sharing_amount'	=> $savings_profit_sharing_amount,
                    'savings_account_last_balance'	=> $savings_account_last_balance,
                );
            }
        } else {
            foreach ($acctsavings as $key => $vS) {
                $savings_interest_rate	    = $vS['savings_interest_rate']/12;
                $savingsinterestrate 	    = round($savings_interest_rate,2);
                $acctsavingsaccount = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_account_date', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.savings_id', 'acct_savings.savings_name', 'acct_savings.savings_status')
                ->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
                ->join('core_member', 'acct_savings_account.member_id', '=', 'core_member.member_id')
                ->where('acct_savings_account.data_state', 0)
                ->where('acct_savings_account.savings_id', $vS['savings_id'])
                ->orderBy('acct_savings_account.savings_account_id', 'ASC')
                ->orderBy('acct_savings_account.savings_account_no', 'ASC')
                ->orderBy('acct_savings_account.member_id', 'ASC')
                ->orderBy('core_member.member_name', 'ASC')
                ->orderBy('core_member.member_address', 'ASC')
                ->orderBy('acct_savings_account.savings_account_date', 'ASC')
                ->orderBy('acct_savings_account.savings_account_last_balance', 'ASC')
                ->orderBy('acct_savings_account.savings_id', 'ASC')
                ->orderBy('acct_savings.savings_name', 'ASC')
                ->orderBy('acct_savings.savings_status', 'ASC')
                ->get();

                foreach ($acctsavingsaccount as $key => $val) {
                    $acctsavingsprofitsharing 	 		= AcctSavingsProfitSharing::select('acct_savings_profit_sharing.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_profit_sharing.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_profit_sharing.savings_profit_sharing_amount', 'acct_savings_profit_sharing.savings_daily_average_balance', 'acct_savings_profit_sharing.savings_account_last_balance')
                    ->join('acct_savings_account', 'acct_savings_profit_sharing.savings_account_id', '=', 'acct_savings_account.savings_account_id')
                    ->join('core_member', 'acct_savings_profit_sharing.member_id', '=', 'core_member.member_id')
                    ->where('acct_savings_profit_sharing.savings_profit_sharing_period', $period);
                    if(empty($val['savings_account_id'])){
                        $acctsavingsprofitsharing = $acctsavingsprofitsharing->where('acct_savings_profit_sharing.savings_account_id', $val['savings_account_id']);
                    }
                    if($branch_id != ''){
                        $acctsavingsprofitsharing = $acctsavingsprofitsharing->where('acct_savings_profit_sharing.branch_id', $branch_id);
                    }
                    $acctsavingsprofitsharing = $acctsavingsprofitsharing->first();

                    if(empty($acctsavingsprofitsharing)){
                        $savings_daily_average_balance 	= 0;
                        $savings_profit_sharing_amount 	= 0;
                        $savings_account_last_balance 	= $val['savings_account_last_balance'];
                    } else {
                        $savings_daily_average_balance 	= $acctsavingsprofitsharing['savings_daily_average_balance'];
                        $savings_profit_sharing_amount 	= $acctsavingsprofitsharing['savings_profit_sharing_amount'];
                        $savings_account_last_balance 	= $acctsavingsprofitsharing['savings_account_last_balance'];
                    }

                    $data_acctsavingsaccount[$vS['savings_id']][] = array (
                        'savings_account_no'			=> $val['savings_account_no'],
                        'member_name'					=> $val['member_name'],
                        'member_address'				=> $val['member_address'],
                        'savings_interest_rate'			=> $savingsinterestrate,
                        'savings_daily_average_balance'	=> $savings_daily_average_balance,
                        'savings_profit_sharing_amount'	=> $savings_profit_sharing_amount,
                        'savings_account_last_balance'	=> $savings_account_last_balance,
                    );
                }
            }
        }

        if(count($data_acctsavingsaccount)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan Nominatif Simpaman")
                                            ->setSubject("")
                                            ->setDescription("Laporan Nominatif Simpaman")
                                            ->setKeywords("Laporan Nominatif Simpaman")
                                            ->setCategory("Laporan Nominatif Simpaman");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan Nominatif Simpaman");

            $spreadsheet->getActiveSheet()->mergeCells("B2:H2");
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);		

            $spreadsheet->getActiveSheet()->getStyle('B4:H4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B2:H4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B2')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B4:H4')->getFont()->setBold(true);

            if($sesi['kelompok'] == 0){
                $spreadsheet->getActiveSheet()->setCellValue('B2',"DAFTAR NOMINATIF SIMPANAN");
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('B2',"DAFTAR NOMINATIF SIMPANAN PER JENIS");
            }
                

            $spreadsheet->getActiveSheet()->setCellValue('B4',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C4',"No. Rek");
            $spreadsheet->getActiveSheet()->setCellValue('D4',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('E4',"Alamat");
            $spreadsheet->getActiveSheet()->setCellValue('F4',"SRH");
            $spreadsheet->getActiveSheet()->setCellValue('G4',"Basil");
            $spreadsheet->getActiveSheet()->setCellValue('H4',"Saldo");


            $no         = 0;
            $totalbasil = 0;
            $totalsaldo = 0;
            if($sesi['kelompok'] == 0){
                $row=5;
                foreach($data_acctsavingsaccount as $key=>$val){
                    $no++;

                    $spreadsheet->getActiveSheet()->getStyle('B'.$row.':H'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('C'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('F'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('G'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('H'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                    $spreadsheet->getActiveSheet()->setCellValue('B'.($row), $no);
                    $spreadsheet->getActiveSheet()->setCellValue('C'.($row), $val['savings_account_no']);								
                    $spreadsheet->getActiveSheet()->setCellValue('D'.($row), $val['member_name']);
                    $spreadsheet->getActiveSheet()->setCellValue('E'.($row), $val['member_address']);
                    $spreadsheet->getActiveSheet()->setCellValue('F'.($row), $val['savings_daily_average_balance']);
                    $spreadsheet->getActiveSheet()->setCellValue('G'.($row), number_format($val['savings_profit_sharing_amount'],2));
                    $spreadsheet->getActiveSheet()->setCellValue('H'.($row), number_format($val['savings_account_last_balance'],2));

                    $totalbasil += $val['savings_profit_sharing_amount'];
                    $totalsaldo += $val['savings_account_last_balance'];
                    $row++;
                }
            } else {
                $i=4;
                
                foreach ($acctsavings as $k => $v) {
                    $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getFont()->setBold(true)->setSize(14);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$i.':H'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':H'.$i);
                    $spreadsheet->getActiveSheet()->setCellValue('B'.$i, $v['savings_name']);

                    $nov= 0;
                    $row= $i+1;
                    $subtotalbasil = 0;
                    $subtotalsaldo = 0;
                    foreach($data_acctsavingsaccount[$v['savings_id']] as $key => $val){
                        $nov++;
                        $spreadsheet->setActiveSheetIndex(0);
                        $spreadsheet->getActiveSheet()->getStyle('B'.($row).':H'.($row))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->getStyle('B'.($row))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $spreadsheet->getActiveSheet()->getStyle('F'.($row).':H'.($row))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        
                        $spreadsheet->getActiveSheet()->setCellValue('B'.($row), $nov);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.($row), $val['savings_account_no']);
                        $spreadsheet->getActiveSheet()->setCellValue('D'.($row), $val['member_name']);
                        $spreadsheet->getActiveSheet()->setCellValue('E'.($row), $val['member_address']);
                        $spreadsheet->getActiveSheet()->setCellValue('F'.($row), $val['savings_daily_average_balance']);
                        $spreadsheet->getActiveSheet()->setCellValue('G'.($row), number_format($val['savings_profit_sharing_amount'],2));
                        $spreadsheet->getActiveSheet()->setCellValue('H'.($row), number_format($val['savings_account_last_balance'],2));
                
                        $subtotalbasil += $val['savings_profit_sharing_amount'];
                        $subtotalsaldo += $val['savings_account_last_balance'];
                        $row++;
                    }
                    $m = $row;

                    $spreadsheet->getActiveSheet()->getStyle('B'.$m.':H'.$m)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                    $spreadsheet->getActiveSheet()->getStyle('B'.$m.':H'.$m)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $spreadsheet->getActiveSheet()->mergeCells('B'.$m.':G'.$m);
                    $spreadsheet->getActiveSheet()->setCellValue('B'.$m, 'SubTotal');

                    $spreadsheet->getActiveSheet()->setCellValue('G'.$m, number_format($subtotalbasil,2));
                    $spreadsheet->getActiveSheet()->setCellValue('H'.$m, number_format($subtotalsaldo,2));

                    $i = $m + 1;
                }

                $totalbasil += $subtotalbasil;
                $totalsaldo += $subtotalsaldo;

            }

            $n = $row;

            $spreadsheet->getActiveSheet()->getStyle('B'.$n.':H'.$n)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $spreadsheet->getActiveSheet()->getStyle('B'.$n.':H'.$n)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->mergeCells('B'.$n.':G'.$n);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$n, 'Total');

            $spreadsheet->getActiveSheet()->setCellValue('G'.$n, number_format($totalbasil,2));
            $spreadsheet->getActiveSheet()->setCellValue('H'.$n, number_format($totalsaldo,2));
                
            ob_clean();
            $filename='Laporan Nominatif Simpaman.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }else{
            echo "Maaf data yang di eksport tidak ada !";
        }
    }
}
