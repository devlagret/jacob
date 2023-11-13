<?php

namespace App\Http\Controllers;

use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsProfitSharing;
use App\Models\AcctSourceFund;
use App\Models\CoreBranch;
use App\Models\CoreMember;
use App\Models\PreferenceCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NominativeRecapReportController extends Controller
{
    public function index()
    {
        $corebranch = CoreBranch::where('data_state', 0)->get();
        $kelompok   = Configuration::KelompokLaporanPembiayaan();

        return view('content.NominativeRecap.index', compact('corebranch', 'kelompok'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "start_date"	=> $request->start_date,
            "end_date"	    => $request->end_date,
            "view"			=> $request->view,
        );

        if($sesi['view'] == 'pdf'){
            $this->processPrinting($sesi);
        }
    }

    public function processPrinting($sesi){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('logo_koperasi')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $kelompok       	= Configuration::KelompokLaporanSimpanan();	
        $period 			= date('mY', strtotime($sesi['start_date']));

        $acctsavings        = AcctSavings::select('savings_id', 'savings_name')
        ->where('savings_status', 0)
        ->where('data_state', 0)
        ->get();

        $acctdeposito       = AcctDeposito::select('deposito_id', 'deposito_name')
        ->where('data_state', 0)
        ->get();

        $acctcredits        = AcctCredits::select('credits_id', 'credits_name')
        ->where('data_state', 0)
        ->get();

        $acctsourcefund     = AcctSourceFund::select('source_fund_id', 'source_fund_name')
        ->where('data_state', 0)
        ->get();

        foreach ($acctsavings as $key => $vS) {
            $acctsavingsaccount = AcctSavingsAccount::
            withoutGlobalScopes()->select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_account_date', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.savings_id', 'acct_savings.savings_name', 'acct_savings.savings_status')
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
                $acctsavingsprofitsharing   = AcctSavingsProfitSharing::withoutGlobalScopes()->select('acct_savings_profit_sharing.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_profit_sharing.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_profit_sharing.savings_profit_sharing_amount', 'acct_savings_profit_sharing.savings_daily_average_balance', 'acct_savings_profit_sharing.savings_account_last_balance')
                ->join('acct_savings_account', 'acct_savings_profit_sharing.savings_account_id', '=', 'acct_savings_account.savings_account_id')
                ->join('core_member', 'acct_savings_profit_sharing.member_id' ,'=', 'core_member.member_id')
                ->where('acct_savings_profit_sharing.savings_profit_sharing_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('acct_savings_profit_sharing.savings_profit_sharing_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                ->where('acct_savings_profit_sharing.savings_account_id', $val['savings_account_id'])
                ->first();

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
                    // 'savings_interest_rate'			=> $savingsinterestrate,
                    'savings_daily_average_balance'	=> $savings_daily_average_balance,
                    'savings_profit_sharing_amount'	=> $savings_profit_sharing_amount,
                    'savings_account_last_balance'	=> $savings_account_last_balance,
                );
            }
        }
        
        $pdf = new TCPDF('L', PDF_UNIT, array(610, 630), true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 6, 6, 6);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::AddPage('L', array(610, 630));

        $pdf::SetFont('helvetica', '', 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $export = "
        ";

        $export .= "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR NOMINATIF SIMPANAN </div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center; font-size:10px\">Periode ".$sesi['start_date']." S.D. ".$sesi['end_date']."</div></td>
            </tr>
        </table>";
		
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"35%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">Jenis Simpanan</div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sub Total Bagi Hasil</div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sub Total Saldo Akhir</div></td>     
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
        
        $no             = 1;
        $totalglobal    = 0;
        $totalsaldo     = 0;
        $subtotalbasil  = 0;
        $subtotalsaldo  = 0;
        $no             = 1;
        foreach ($acctsavings as $key => $vS) {
            if(!empty($data_acctsavingsaccount[$vS['savings_id']])){
                foreach ($data_acctsavingsaccount[$vS['savings_id']] as $v) {
                    $subtotalbasil += $v['savings_profit_sharing_amount'];
                    $subtotalsaldo += $v['savings_account_last_balance'];
                }
            
                $export .= "
                <tr>
                    <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                    <td width=\"35%\" style=\"font-weight:bold\"><div style=\"font-size:10\">".$vS['savings_name']."</div></td>
                    <td width=\"30%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($subtotalbasil, 2)."</div></td>
                    <td width=\"30%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($subtotalsaldo, 2)."</div></td>
                </tr>";
                $totalglobal 	+= $subtotalbasil;
                $totalsaldo 	+= $subtotalsaldo;	
            }
            $no = $no+1;
        }

        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\"></div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\"></div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold\">Saldo Akhir</div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold\">Bagi Hasil</div></td>     
            </tr>
            <tr>
                <td width=\"35%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\"></div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold\">Total</div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($totalglobal,2)."</div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($totalsaldo,2)."</div></td>     
            </tr>
        </table>";

        $export .= "
        <br><br><br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR NOMINATIF SIMPANAN BERJANGKA </div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center; font-size:10px\">Periode ".$sesi['start_date']." S.D. ".$sesi['end_date']."</div></td>
            </tr>
        </table>";

        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"50%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">Jenis Simpanan Berjangka</div></td>
                <td width=\"45%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sub Total Saldo Akhir</div></td>     
            </tr>				
        </table>";

        $totalperjenis = 0;
        foreach ($acctdeposito as $kSavings => $vSavings) {					
            $acctdepositoaccount_deposito = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_status', 'acct_deposito.deposito_interest_rate')
			->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
			->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
			->where('acct_deposito_account.deposito_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
			->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
			->where('acct_deposito_account.deposito_id', $vSavings['deposito_id'])
			->where('acct_deposito_account.data_state', 0)
			->where('acct_deposito_account.deposito_account_status', 0)
			->orderBy('acct_deposito_account.deposito_account_id', 'ASC')
			->orderBy('acct_deposito_account.deposito_id', 'ASC')
			->orderBy('acct_deposito_account.member_id', 'ASC')
			->orderBy('core_member.member_name', 'ASC')
			->orderBy('acct_deposito_account.deposito_account_date', 'ASC')
			->orderBy('acct_deposito_account.deposito_account_due_date', 'ASC')
			->orderBy('acct_deposito_account.deposito_account_amount', 'ASC')
			->orderBy('acct_deposito_account.deposito_account_no', 'ASC')
			->orderBy('acct_deposito_account.deposito_account_period', 'ASC')
			->orderBy('acct_deposito_account.deposito_account_status', 'ASC')
            ->get();
            
            if(!empty($acctdepositoaccount_deposito)){
                $subtotalperjenis = 0;
                foreach ($acctdepositoaccount_deposito as $v) {
                    $subtotalperjenis += $v['deposito_account_amount'];
                }
                $export .= "
                <tr>
                    <td width=\"5%\"><div style=\"text-align: left;\">".$kSavings++."</div></td>
                    <td width=\"50%\" style=\"font-weight:bold\"><div style=\"font-size:10\">".$vSavings['deposito_name']."</div></td>
                    <td width=\"45%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($subtotalperjenis, 2)."</div></td>
                </tr>";
                $totalperjenis += $subtotalperjenis;
            }
        }
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"40%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\"></div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\"></div></td>
                <td width=\"45%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold\">Saldo Akhir</div></td>     
            </tr>		
            <tr>
            <td width=\"40%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\"></div></td>
            <td width=\"35%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold\">Total</div></td>
            <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($totalperjenis,2)."</div></td>     
        </tr>		
        </table>";

        $export .= "
        <br><br><br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR NOMINATIF PINJAMAN</div></td>
            </tr>
            <tr>
            <td><div style=\"text-align: center; font-size:10px\">Periode ".$sesi['start_date']." S.D. ".$sesi['end_date']."</div></td>
            </tr>
        </table>";

        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">Jenis Pinjaman</div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sub Total Saldo Pokok</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sub Total Sisa Bagi Hasil</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sub Total Sisa Pokok</div></td>     
            </tr>				
        </table>";
    
        $totalpokok 		    = 0;
        $totalsisapokok		    = 0;
        $totalsisamargin	    = 0;
        $subtotalpokok 		    = 0;
        $subtotalsisapokok 	    = 0;
        $subtotalsisamargin 	= 0;
        $tblcredit2             = '';

        foreach ($acctcredits as $kCredits => $vCredits) {
            $acctcreditsaccount_credits = AcctCreditsAccount::select('acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'acct_credits_account.credits_account_last_balance', 'acct_credits_account.credits_account_date', 'acct_credits_account.credits_account_due_date', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_interest_last_balance', 'acct_credits_account.credits_account_interest', 'acct_credits_account.credits_account_period', 'acct_credits_account.credits_account_interest_amount', 'acct_credits_account.credits_account_interest_last_balance', 'acct_credits_account.credits_account_payment_to', 'acct_credits_account.credits_account_payment_amount')
			->where('acct_credits_account.credits_approve_status', 1)
			->where('acct_credits_account.data_state', 0)
			->where('acct_credits_account.credits_account_last_balance', '>', 0)
			->where('acct_credits_account.credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
			->where('acct_credits_account.credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])));
			if(!empty($vCredits['credits_id'])){
			    $acctcreditsaccount_credits = $acctcreditsaccount_credits->where('acct_credits_account.credits_id', $vCredits['credits_id']);
			}
			$acctcreditsaccount_credits = $acctcreditsaccount_credits->orderBy('acct_credits_account.credits_account_serial', 'ASC')
			->orderBy('acct_credits_account.member_id', 'ASC')
			->orderBy('acct_credits_account.credits_account_last_balance', 'ASC')
			->orderBy('acct_credits_account.credits_account_date', 'ASC')
			->orderBy('acct_credits_account.credits_account_due_date', 'ASC')
			->orderBy('acct_credits_account.credits_account_amount', 'ASC')
			->orderBy('acct_credits_account.credits_account_interest_last_balance', 'ASC')
			->orderBy('acct_credits_account.credits_account_interest', 'ASC')
			->orderBy('acct_credits_account.credits_account_period', 'ASC')
            ->get();

            foreach ($acctcreditsaccount_credits as $v) {
                $credits_account_interest_last_balance = ($v['credits_account_interest_amount']*$v['credits_account_period'])-($v['credits_account_payment_to']*$v['credits_account_interest_amount']);

                $subtotalpokok += $v['credits_account_amount'];
                $subtotalsisapokok += $v['credits_account_last_balance'];
                $subtotalsisamargin += $credits_account_interest_last_balance;
            }

            $export .= "
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">".($kCredits+1)."</div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">".$vCredits['credits_name']."</div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($subtotalpokok, 2)."</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($subtotalsisamargin, 2)."</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($subtotalsisapokok, 2)."</div></td>     
            </tr>";

            $totalpokok         += $subtotalpokok;
            $totalsisapokok     += $subtotalsisapokok;
            $totalsisamargin    += $subtotalsisamargin;
            $subtotalpokok      = 0;
            $subtotalsisapokok  = 0;
            $subtotalsisamargin = 0;
        }
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\"></div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\"></div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold;\">Saldo Pokok</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold;\">Sisa Bagi Hasil</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold;\">Sisa Pokok</div></td>     
            </tr>	
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\"></div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold;\">Total</div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($totalpokok, 2)."</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($totalsisamargin, 2)."</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($totalsisapokok, 2)."</div></td>     
            </tr>		
        </table>";
            
        $export .= "
        <br><br><br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR NOMINATIF SUMBER DANA</div></td>
            </tr>
            <tr>
            <td><div style=\"text-align: center; font-size:10px\">Periode ".$sesi['start_date']." S.D. ".$sesi['end_date']."</div></td>
            </tr>
        </table>";

        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: Left;font-size:10;\">Jenis Pinjaman</div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sub Total Saldo Pokok</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sub Total Sisa Bagi Hasil</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sub Total Sisa Pokok</div></td>     
            </tr>				
        </table>";
    
        $totalpokok 		= 0;
        $totalsisapokok 	= 0;
        $totalsisamargin 	= 0;
        $branch_id          = '';
        $tblsourcefund2     = '';

        foreach ($acctsourcefund as $kCredits => $vCredits) {
            $acctcreditsaccount_sourcefund = AcctCreditsAccount::select('acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_credits_account.credits_account_date', 'acct_credits_account.credits_account_due_date', 'acct_credits_account.credits_account_interest', 'acct_credits_account.credits_account_last_balance', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_interest_last_balance', 'acct_credits_account.credits_account_period', 'acct_credits_account.credits_account_interest_amount', 'acct_credits_account.credits_account_interest_last_balance', 'acct_credits_account.credits_account_payment_to', 'acct_credits_account.credits_account_payment_amount')
			->join('core_member', 'acct_credits_account.member_id', '=', 'core_member.member_id')
			->where('acct_credits_account.credits_account_last_balance', '>', 0)
			->where('acct_credits_account.credits_approve_status', 1)
			->where('acct_credits_account.data_state', 0)
			->where('acct_credits_account.credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
			->where('acct_credits_account.credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
			->where('acct_credits_account.source_fund_id', $vCredits['source_fund_id'])
			->orderBy('acct_credits_account.credits_account_serial', 'ASC')
			->orderBy('acct_credits_account.member_id', 'ASC')
			->orderBy('core_member.member_name', 'ASC')
			->orderBy('core_member.member_address', 'ASC')
			->orderBy('acct_credits_account.credits_account_date', 'ASC')
			->orderBy('acct_credits_account.credits_account_due_date', 'ASC')
			->orderBy('acct_credits_account.credits_account_interest', 'ASC')
			->orderBy('acct_credits_account.credits_account_last_balance', 'ASC')
			->orderBy('acct_credits_account.credits_account_amount', 'ASC')
			->orderBy('acct_credits_account.credits_account_interest_last_balance', 'ASC')
			->orderBy('acct_credits_account.credits_account_period', 'ASC')
            ->get();
                    
            foreach ($acctcreditsaccount_sourcefund as $v) {
                $credits_account_interest_last_balance = ($v['credits_account_interest_amount']*$v['credits_account_period'])-($v['credits_account_payment_to']*$v['credits_account_interest_amount']);

                $subtotalpokok      += $v['credits_account_amount'];
                $subtotalsisapokok  += $v['credits_account_last_balance'];
                $subtotalsisamargin += $credits_account_interest_last_balance;
            }

            $export .= "
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">".($kCredits+1)."</div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">".$vCredits['source_fund_name']."</div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($subtotalpokok, 2)."</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($subtotalsisamargin, 2)."</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($subtotalsisapokok, 2)."</div></td>     
            </tr>";
            $totalpokok         += $subtotalpokok;
            $totalsisapokok     += $subtotalsisapokok;
            $totalsisamargin    += $subtotalsisamargin;
            $subtotalpokok      = 0;
            $subtotalsisapokok  = 0;
            $subtotalsisamargin = 0;
        }
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\"></div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\"></div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold;\">Saldo Pokok</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold;\">Sisa Bagi Hasil</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold;\">Sisa Pokok</div></td>     
            </tr>	
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;font-weight:bold;\">Total</div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($totalpokok, 2)."</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($totalsisamargin, 2)."</div></td>     
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($totalsisapokok, 2)."</div></td>     
            </tr>	
            <tr>
                <br><br><br><br>
                <td colspan =\"3\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."<br>".auth()->user()->username."</div></td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Nominatif Rekap.pdf';
        $pdf::Output($filename, 'I');
    }
}
