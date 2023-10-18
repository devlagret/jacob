<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPayment;
use App\Models\AcctSavingsAccount;
use App\Models\CoreBranch;
use App\Models\PreferenceCompany;
use App\DataTables\AcctCreditsAccountHistoryDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctCreditsAccountHistoryController extends Controller
{
    public function index(AcctCreditsAccountHistoryDataTable $dataTable)
    {
        $sessiondata = session()->get('filter_creditsaccounthistory');

        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state', 0)
        ->get();

        $acctcredits = AcctCredits::select('credits_id', 'credits_name')
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctCreditsAccountHistory.List.index', compact('corebranch', 'acctcredits', 'sessiondata'));
    }

    public function filter(Request $request){
        if($request->credits_id){
            $credits_id = $request->credits_id;
        }else{
            $credits_id = null;
        }

        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = null;
        }

        $sessiondata = array(
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'credits_id'    => $credits_id,
            'branch_id'     => $branch_id
        );

        session()->put('filter_creditsaccounthistory', $sessiondata);

        return redirect('credits-account-history');
    }

    public function filterReset(){
        session()->forget('filter_creditsaccounthistory');

        return redirect('credits-account-history');
    }

    public function detail($credits_account_id){
        $config                 = theme()->getOption('page', 'view');
        $memberidentity         = array_filter(Configuration::MemberIdentity());
        $paymenttype            = array_filter(Configuration::PaymentType());

        $acctcreditsaccount     = AcctCreditsAccount::select('acct_credits_account.*', 'core_member.member_name', 'core_member.member_no', 'core_member.member_address', 'acct_credits.credits_id', 'core_member.member_identity', 'core_member.member_identity_no', 'acct_credits.credits_name', 'core_member.member_phone', 'core_member.member_mandatory_savings_last_balance', 'core_member.member_principal_savings_last_balance')
        ->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
        ->join('core_member', 'acct_credits_account.member_id', '=', 'core_member.member_id')
        ->join('core_member_working', 'acct_credits_account.member_id', '=', 'core_member_working.member_id')
        ->where('acct_credits_account.data_state', 0)
        ->where('acct_credits_account.credits_account_id', $credits_account_id)
        ->first();

        $acctcreditspayment     = AcctCreditsPayment::select('acct_credits_payment.credits_payment_date', 'acct_credits_payment.credits_payment_amount', 'acct_credits_payment.credits_payment_principal', 'acct_credits_payment.credits_payment_interest', 'acct_credits_payment.credits_principal_last_balance', 'acct_credits_payment.credits_interest_last_balance', 'acct_credits_payment.credits_payment_fine', 'acct_credits_payment.credits_payment_fine_last_balance')
        ->join('acct_credits_account','acct_credits_payment.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->where('acct_credits_payment.data_state', 0)
        ->where('acct_credits_payment.credits_account_id', $credits_account_id)
        ->get();

        return view('content.AcctCreditsAccountHistory.Detail.index', compact('memberidentity', 'paymenttype', 'acctcreditsaccount', 'acctcreditspayment'));
    
    }

    public function printPaymentHistory($credits_account_id){
        $branch_id              = auth()->user()->branch_id;
        $branch_status          = auth()->user()->branch_status;
        $preferencecompany	    = PreferenceCompany::select('logo_koperasi')->first();
        $path                   = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $memberidentity			= Configuration::MemberIdentity();
        $acctcreditsaccount     = AcctCreditsAccount::select('acct_credits_account.*', 'core_member.member_name', 'core_member.member_no', 'core_member.member_address', 'acct_credits.credits_id', 'core_member.member_identity', 'core_member.member_identity_no', 'acct_credits.credits_name', 'core_member.member_phone', 'core_member.member_mandatory_savings_last_balance', 'core_member.member_principal_savings_last_balance')
        ->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
        ->join('core_member', 'acct_credits_account.member_id', '=', 'core_member.member_id')
        ->join('core_member_working', 'acct_credits_account.member_id', '=', 'core_member_working.member_id')
        ->where('acct_credits_account.data_state', 0)
        ->where('acct_credits_account.credits_account_id', $credits_account_id)
        ->first();

        $acctcreditspayment     = AcctCreditsPayment::select('acct_credits_payment.credits_payment_date', 'acct_credits_payment.credits_payment_amount', 'acct_credits_payment.credits_payment_principal', 'acct_credits_payment.credits_payment_interest', 'acct_credits_payment.credits_principal_last_balance', 'acct_credits_payment.credits_interest_last_balance', 'acct_credits_payment.credits_payment_fine', 'acct_credits_payment.credits_payment_fine_last_balance')
        ->join('acct_credits_account','acct_credits_payment.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->where('acct_credits_payment.data_state', 0)
        ->where('acct_credits_payment.credits_account_id', $credits_account_id)
        ->get();
        
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

        $pdf::SetFont('helvetica', '', 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $export = "
        
        <table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td style=\"text-align:center;\" width=\"100%\">
                    <div style=\"font-size:14px\";><b>HISTORI ANGSURAN PINJAMAN</b></div>
                </td>			
             </tr>
         </table>
         <br><br>";		

        $export .= "
        <table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px\";><b>Nama</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"80%\">
                    <div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['member_name']."</b></div>
                </td>
            </tr>
            <tr>
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px\";><b>No. Perjanjian Kredit</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"30%\">
                    <div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_serial']."</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px;font-weight:bold\">
                        Jangka Waktu
                    </div>
                </td>
                <td style=\"text-align:left; \" width=\"30%\">
                    <div style=\"font-size:12px;font-weight:bold\">
                        : ".$acctcreditsaccount['credits_account_period']."
                    </div>
                </td>
            </tr>
            <tr>
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px\";><b>Tanggal Realisasi</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"30%\">
                    <div style=\"font-size:12px\";><b>: ".date('d-m-Y', strtotime($acctcreditsaccount['credits_account_date']))."</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px;font-weight:bold\">
                        Pinjaman
                    </div>
                </td>
                <td style=\"text-align:left; \" width=\"30%\">
                    <div style=\"font-size:12px;font-weight:bold\">
                        : ".number_format($acctcreditsaccount['credits_account_amount'], 2)."
                    </div>
                </td>
            </tr>
            <tr>
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px\";><b>Alamat</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"80%\">
                    <div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['member_address']."</b></div>
                </td>
            </tr>
            <br><br>";

        $export .= "
        <table id=\"items\" width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" border=\"1\">
            <tr>
                <td style=\"text-align:center;\" width=\"5%\">
                    <div style=\"font-size:10px\">
                        <b>No</b>
                    </div>
                </td>
                <td style=\"text-align:center;\" width=\"10%\">
                    <div style=\"font-size:10px\">
                        <b>Tanggal Angsuran</b>
                    </div>
                </td>
                <td style=\"text-align:center;\" width=\"15%\">
                    <div style=\"font-size:10px\">
                        <b>Angsuran Pokok</b>
                    </div>
                </td>
                <td style=\"text-align:center;\" width=\"15%\">
                    <div style=\"font-size:10px\">
                        <b>Angsuran Bunga</b>
                    </div>
                </td>
                <td style=\"text-align:center;\" width=\"15%\">
                    <div style=\"font-size:10px\">
                        <b>Saldo Pokok</b>
                    </div>
                </td>
                <td style=\"text-align:center;\" width=\"15%\">
                    <div style=\"font-size:10px\">
                        <b>Saldo Bunga</b>
                    </div>
                </td>
                <td style=\"text-align:center;\" width=\"10%\">
                    <div style=\"font-size:10px\">
                        <b>Sanksi Dibayarkan</b>
                    </div>
                </td>
                <td style=\"text-align:center;\" width=\"15%\">
                    <div style=\"font-size:10px\">
                        <b>Akumulasi Sanksi</b>
                    </div>
                </td>
            </tr>";

        $no = 1;
        foreach($acctcreditspayment as $key=>$val){
            $export .= "
            <tr>
                <td style=\"text-align:center;\" width=\"5%\">
                    <div style=\"font-size:10px\">
                        ".$no."
                    </div>
                </td>
                <td style=\"text-align:left;\" width=\"10%\">
                    <div style=\"font-size:10px\">
                        ".date('d-m-Y', strtotime($val['credits_payment_date']))."
                    </div>
                </td>
                <td style=\"text-align:right;\" width=\"15%\">
                    <div style=\"font-size:10px\">
                        ".number_format($val['credits_payment_principal'], 2)."
                    </div>
                </td>
                <td style=\"text-align:right;\" width=\"15%\">
                    <div style=\"font-size:10px\">
                        ".number_format($val['credits_payment_interest'], 2)."
                    </div>
                </td>
                <td style=\"text-align:right;\" width=\"15%\">
                    <div style=\"font-size:10px\">
                        ".number_format($val['credits_principal_last_balance'], 2)."
                    </div>
                </td>
                <td style=\"text-align:right;\" width=\"15%\">
                    <div style=\"font-size:10px\">
                        ".number_format($val['credits_interest_last_balance'], 2)."
                    </div>
                </td>
                <td style=\"text-align:right;\" width=\"10%\">
                    <div style=\"font-size:10px\">
                        ".number_format($val['credits_payment_fine'], 2)."
                    </div>
                </td>
                <td style=\"text-align:right;\" width=\"15%\">
                    <div style=\"font-size:10px\">
                        ".number_format($val['credits_payment_fine_last_balance'], 2)."
                    </div>
                </td>
            </tr>";

            $no++;
        }

        $export .= "
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'History Angsuran Pinjaman.pdf';
        $pdf::Output($filename, 'I');
    }

    public function printPaymentSchedule($credits_account_id){
        $branch_id              = auth()->user()->branch_id;
        $branch_status          = auth()->user()->branch_status;
        $preferencecompany	    = PreferenceCompany::select('logo_koperasi')->first();
        $path                   = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $paymenttype 			= Configuration::PaymentType();
        $paymentperiod 			= Configuration::CreditsPaymentPeriod();
        $totalpokok             = 0;
        $totalmargin            = 0;
        $total                  = 0;
        $datapola               = array();
        
        $acctcreditsaccount     = AcctCreditsAccount::select('acct_credits_account.*', 'core_member.member_name', 'core_member.member_no', 'core_member.member_address', 'acct_credits.credits_id', 'core_member.member_identity', 'core_member.member_identity_no', 'acct_credits.credits_name', 'core_member.member_phone', 'core_member.member_mandatory_savings_last_balance', 'core_member.member_principal_savings_last_balance')
        ->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
        ->join('core_member', 'acct_credits_account.member_id', '=', 'core_member.member_id')
        ->join('core_member_working', 'acct_credits_account.member_id', '=', 'core_member_working.member_id')
        ->where('acct_credits_account.data_state', 0)
        ->where('acct_credits_account.credits_account_id', $credits_account_id)
        ->first();

        if($acctcreditsaccount['payment_type_id'] == '' || $acctcreditsaccount['payment_type_id'] == 1){
            $datapola = $this->flat($credits_account_id);
        }else if ($acctcreditsaccount['payment_type_id'] == 2){
            $datapola = $this->anuitas($credits_account_id);
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

        $pdf::SetFont('helvetica', '', 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $export = "
        
        <table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td style=\"text-align:center;\" width=\"100%\">
                    <div style=\"font-size:14px\";><b>JADWAL ANGSURAN</b></div>
                </td>			
             </tr>
         </table>
         <br><br>";

        $export .= "
        <table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px\";><b>No. Pinjaman</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"45%\">
                    <div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_serial']."</b></div>
                </td>

                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px\";><b>Jenis Pinjaman</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"50%\">
                    <div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_name']."</b></div>
                </td>		
            </tr>
            <tr>
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px\";><b>Nama</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"45%\">
                    <div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['member_name']."</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px\";><b>Jangka Waktu</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"50%\">
                    <div style=\"font-size:12px\";><b>: ".$acctcreditsaccount['credits_account_period']." ".$paymentperiod[$acctcreditsaccount['credits_payment_period']]."</b></div>
                </td>			
            </tr>
            <tr>
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px\";><b>Tipe Angsuran</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"45%\">
                    <div style=\"font-size:12px\";><b>: ".$paymenttype[$acctcreditsaccount['payment_type_id']]."</b></div>
                </td>	
                <td style=\"text-align:left;\" width=\"20%\">
                    <div style=\"font-size:12px\";><b>Plafon</b></div>
                </td>
                <td style=\"text-align:left;\" width=\"50%\">
                    <div style=\"font-size:12px\";><b>: Rp.".number_format($acctcreditsaccount['credits_account_amount'])."</b></div>
                </td>			
            </tr>
        </table>
        <br><br>";
             
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
            <tr>
                <td width=\"5%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Ke</div></td>
                <td width=\"12%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Tanggal Angsuran</div></td>
                <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Saldo Pokok</div></td>
                <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Angsuran Pokok</div></td>
                <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Angsuran Bunga</div></td>
                <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Total Angsuran</div></td>
                <td width=\"18%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Sisa Pokok</div></td>
            </tr>				
        </table>";

         $no        = 1;
         $export   .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">";
         foreach ($datapola as $key => $val) {
             $export .= "
                 <tr>
                     <td width=\"5%\"><div style=\"text-align: left;\">&nbsp; ".$val['ke']."</div></td>
                     <td width=\"12%\"><div style=\"text-align: right;\">".date('d-m-Y', strtotime($val['tanggal_angsuran']))." &nbsp; </div></td>
                     <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['opening_balance'], 2)." &nbsp; </div></td>
                     <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['angsuran_pokok'], 2)." &nbsp; </div></td>
                     <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['angsuran_bunga'], 2)." &nbsp; </div></td>
                     <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['angsuran'], 2)." &nbsp; </div></td>
                     <td width=\"18%\"><div style=\"text-align: right;\">".number_format($val['last_balance'], 2)." &nbsp; </div></td>
                 </tr>
             ";

             $totalpokok  += $val['angsuran_pokok'];
             $totalmargin += $val['angsuran_bunga'];
             $total       += $val['angsuran'];
             $no++;
         }

        $export .= "
            <tr>
                <td colspan=\"3\"><div style=\"text-align: right;font-weight:bold\">Total</div></td>
                <td><div style=\"text-align: right;font-weight:bold\">".number_format($totalpokok, 2)."</div></td>
                <td><div style=\"text-align: right;font-weight:bold\">".number_format($totalmargin, 2)."</div></td>
                <td><div style=\"text-align: right;font-weight:bold\">".number_format($total, 2)."</div></td>
            </tr>							
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Jadwal Angsuran Pinjaman.pdf';
        $pdf::Output($filename, 'I');
    }
    
    public const EPSILON = 1e-6;

    private static function checkZero(float $value, float $epsilon = self::EPSILON): float
    {
        return \abs($value) < $epsilon ? 0.0 : $value;
    }
		
    public static function fv(float $rate, int $periods, float $payment, float $present_value, bool $beginning = false): float
    {
        $when = $beginning ? 1 : 0;
        if ($rate == 0) {
            $fv = -($present_value + ($payment * $periods));
            return self::checkZero($fv);
        }

        $initial  = 1 + ($rate * $when);
        $compound = \pow(1 + $rate, $periods);
        $fv       = - (($present_value * $compound) + (($payment * $initial * ($compound - 1)) / $rate));

        return self::checkZero($fv);
    }
		
    public static function pmt(float $rate, int $periods, float $present_value, float $future_value = 0.0, bool $beginning = false): float
    {
        $when = $beginning ? 1 : 0;
        if ($rate == 0) {
            return - ($future_value + $present_value) / $periods;
        }

        return - ($future_value + ($present_value * \pow(1 + $rate, $periods)))
            /
            ((1 + $rate * $when) / $rate * (\pow(1 + $rate, $periods) - 1));
    }
		
    public static function ipmt(float $rate, int $period, int $periods, float $present_value, float $future_value = 0.0, bool $beginning = false): float
    {
        if ($period < 1 || $period > $periods) {
            return \NAN;
        }

        if ($rate == 0) {
            return 0;
        }

        if ($beginning && $period == 1) {
            return 0.0;
        }

        $payment = self::pmt($rate, $periods, $present_value, $future_value, $beginning);
        if ($beginning) {
            $interest = (self::fv($rate, $period - 2, $payment, $present_value, $beginning) - $payment) * $rate;
        } else {
            $interest = self::fv($rate, $period - 1, $payment, $present_value, $beginning) * $rate;
        }

        return self::checkZero($interest);
    }

    public static function ppmt(float $rate, int $period, int $periods, float $present_value, float $future_value = 0.0, bool $beginning = false): float
    {
        $payment = self::pmt($rate, $periods, $present_value, $future_value, $beginning);
        $ipmt    = self::ipmt($rate, $period, $periods, $present_value, $future_value, $beginning);

        return $payment - $ipmt;
    }

    public function flat($id){
        $credistaccount					= AcctCreditsAccount::findOrFail($id);
        $total_credits_account 			= $credistaccount['credits_account_amount'];
        $credits_account_interest 		= $credistaccount['credits_account_interest'];
        $credits_account_period 		= $credistaccount['credits_account_period'];
        $installment_pattern			= array();
        $opening_balance				= $total_credits_account;
        for($i=1; $i<=$credits_account_period; $i++){
            if($credistaccount['credits_payment_period'] == 2){
                $a = $i * 7;
                $tanggal_angsuran 								= date('d-m-Y', strtotime("+".$a." days", strtotime($credistaccount['credits_account_date'])));
            } else {
                $tanggal_angsuran 								= date('d-m-Y', strtotime("+".$i." months", strtotime($credistaccount['credits_account_date'])));
            }
            
            $angsuran_pokok									= $credistaccount['credits_account_principal_amount'];				
            $angsuran_margin								= $credistaccount['credits_account_interest_amount'];				
            $angsuran 										= $angsuran_pokok + $angsuran_margin;
            $last_balance 									= $opening_balance - $angsuran_pokok;
            $installment_pattern[$i]['opening_balance']		= $opening_balance;
            $installment_pattern[$i]['ke'] 					= $i;
            $installment_pattern[$i]['tanggal_angsuran'] 	= $tanggal_angsuran;
            $installment_pattern[$i]['angsuran'] 			= $angsuran;
            $installment_pattern[$i]['angsuran_pokok']		= $angsuran_pokok;
            $installment_pattern[$i]['angsuran_bunga'] 		= $angsuran_margin;
            $installment_pattern[$i]['last_balance'] 		= $last_balance;
            $opening_balance 								= $last_balance;
        }
        
        return $installment_pattern;
    }

    public function slidingrate($id){
        $credistaccount					= AcctCreditsAccount::findOrFail($id);
        $total_credits_account 			= $credistaccount['credits_account_amount'];
        $credits_account_interest 		= $credistaccount['credits_account_interest'];
        $credits_account_period 		= $credistaccount['credits_account_period'];
        $installment_pattern			= array();
        $opening_balance				= $total_credits_account;
        for($i=1; $i<=$credits_account_period; $i++){
            if($credistaccount['credits_payment_period'] == 2){
                $a = $i * 7;
                $tanggal_angsuran 								= date('d-m-Y', strtotime("+".$a." days", strtotime($credistaccount['credits_account_date'])));
            } else {
                $tanggal_angsuran 								= date('d-m-Y', strtotime("+".$i." months", strtotime($credistaccount['credits_account_date'])));
            }
            
            $angsuran_pokok									= $credistaccount['credits_account_amount']/$credits_account_period;				
            $angsuran_margin								= $opening_balance*$credits_account_interest/100;				
            $angsuran 										= $angsuran_pokok + $angsuran_margin;
            $last_balance 									= $opening_balance - $angsuran_pokok;
            $installment_pattern[$i]['opening_balance']		= $opening_balance;
            $installment_pattern[$i]['ke'] 					= $i;
            $installment_pattern[$i]['tanggal_angsuran'] 	= $tanggal_angsuran;
            $installment_pattern[$i]['angsuran'] 			= $angsuran;
            $installment_pattern[$i]['angsuran_pokok']		= $angsuran_pokok;
            $installment_pattern[$i]['angsuran_bunga'] 		= $angsuran_margin;
            $installment_pattern[$i]['last_balance'] 		= $last_balance;
            $opening_balance 								= $last_balance;
        }
        
        return $installment_pattern;
    }

    public function menurunharian($id){
        $credistaccount					= AcctCreditsAccount::findOrFail($id);
        $total_credits_account 			= $credistaccount['credits_account_amount'];
        $credits_account_interest 		= $credistaccount['credits_account_interest'];
        $credits_account_period 		= $credistaccount['credits_account_period'];
        $installment_pattern			= array();
        $opening_balance				= $total_credits_account;
        
        return $installment_pattern;
    }
		
    public function rate1($nper, $pmt, $pv, $fv = 0.0, $type = 0, $guess = 0.1) {
        $rate = $guess;
        if (abs($rate) < FINANCIAL_PRECISION) {
            $y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
        } else {
            $f = exp($nper * log(1 + $rate));
            $y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
        }
        $y0 = $pv + $pmt * $nper + $fv;
        $y1 = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
        $i  = $x0 = 0.0;
        $x1 = $rate;
        while ((abs($y0 - $y1) > FINANCIAL_PRECISION) && ($i < FINANCIAL_MAX_ITERATIONS)) {
            $rate   = ($y1 * $x0 - $y0 * $x1) / ($y1 - $y0);
            $x0     = $x1;
            $x1     = $rate;
            if (abs($rate) < FINANCIAL_PRECISION) {
                $y  = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
            } else {
                $f  = exp($nper * log(1 + $rate));
                $y  = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
            }
            $y0 = $y1;
            $y1 = $y;
            ++$i;
        }
        return $rate;
    }

    public function rate4(Request $request) {
        $nprest 	= $request->nprest;
        $vlrparc 	= $request->vlrparc;
        $vp 		= $request->vp;
        $guess 		= 0.25;
        $maxit 		= 100;
        $precision 	= 14;
        $check 		= 1;
        $guess 		= round($guess,$precision);
        for ($i=0 ; $i<$maxit ; $i++) {
            $divdnd = $vlrparc - ( $vlrparc * (pow(1 + $guess , -$nprest)) ) - ($vp * $guess);
            $divisor = $nprest * $vlrparc * pow(1 + $guess , (-$nprest - 1)) - $vp;
            $newguess = $guess - ( $divdnd / $divisor );
            $newguess = round($newguess, $precision);
            if ($newguess == $guess) {
                if($check == 1){
                echo $newguess;
                $check++;
                }
            } else {
                $guess = $newguess;
            }
        }
        echo null;
    }

    function rate3($nprest, $vlrparc, $vp, $guess = 0.25) {
        $maxit      = 100;
        $precision  = 14;
        $guess      = round($guess,$precision);
        for ($i=0 ; $i<$maxit ; $i++) {
            $divdnd = $vlrparc - ( $vlrparc * (pow(1 + $guess , -$nprest)) ) - ($vp * $guess);
            $divisor = $nprest * $vlrparc * pow(1 + $guess , (-$nprest - 1)) - $vp;
            $newguess = $guess - ( $divdnd / $divisor );
            $newguess = round($newguess, $precision);
            if ($newguess == $guess) {
                return $newguess;
            } else {
                $guess = $newguess;
            }
        }
        return null;
    }
		
    public function anuitas($id){
        $creditsaccount 	= AcctCreditsAccount::findOrFail($id);
        $pinjaman 	        = $creditsaccount['credits_account_amount'];
        $bunga 		        = $creditsaccount['credits_account_interest'] / 100;
        $period 	        = $creditsaccount['credits_account_period'];
        $bungaA 		    = pow((1 + $bunga), $period);
        $bungaB 		    = pow((1 + $bunga), $period) - 1;
        $bAnuitas 		    = ($bungaA / $bungaB);
        $totalangsuran 	    = $pinjaman * $bunga * $bAnuitas;
        $rate			    = $this->rate3($period, $totalangsuran, $pinjaman);

        $sisapinjaman = $pinjaman;
        for ($i=1; $i <= $period ; $i++) {
            if($creditsaccount['credits_payment_period'] == 1){
                $tanggal_angsuran 	= date('d-m-Y', strtotime("+".$i." months", strtotime($creditsaccount['credits_account_date']))); 
            } else {
                $a = $i * 7;

                $tanggal_angsuran 	= date('d-m-Y', strtotime("+".$a." days", strtotime($creditsaccount['credits_account_date']))); 
            }
            
            $angsuranbunga 		= $sisapinjaman * $rate;
            $angsuranpokok 		= $totalangsuran - $angsuranbunga;
            $sisapokok 			= $sisapinjaman - $angsuranpokok;

            $pola[$i]['ke']					= $i;
            $pola[$i]['tanggal_angsuran']	= $tanggal_angsuran;
            $pola[$i]['opening_balance']	= $sisapinjaman;
            $pola[$i]['angsuran']			= $totalangsuran;
            $pola[$i]['angsuran_pokok']		= $angsuranpokok;
            $pola[$i]['angsuran_bunga']		= $angsuranbunga;
            $pola[$i]['last_balance']		= $sisapokok;
            $sisapinjaman                   = $sisapinjaman - $angsuranpokok;
        }
        return $pola;
    }
		
    function rate2($nper, $pmt, $pv, $fv = 0.0, $type = 0, $guess = 0.1) {
        $rate = $guess;
        if (abs($rate) < $this->FINANCIAL_PRECISION) {
            $y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
        } else {
            $f = exp($nper * log(1 + $rate));
            $y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
        }
        $y0 = $pv + $pmt * $nper + $fv;
        $y1 = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
        $i  = $x0 = 0.0;
        $x1 = $rate;
        while ((abs($y0 - $y1) > $this->FINANCIAL_PRECISION) && ($i < $this->FINANCIAL_MAX_ITERATIONS)) {
            $rate = ($y1 * $x0 - $y0 * $x1) / ($y1 - $y0);
            $x0 = $x1;
            $x1 = $rate;

            if (abs($rate) < $this->FINANCIAL_PRECISION) {
                $y = $pv * (1 + $nper * $rate) + $pmt * (1 + $rate * $type) * $nper + $fv;
            } else {
                $f = exp($nper * log(1 + $rate));
                $y = $pv * $f + $pmt * (1 / $rate + $type) * ($f - 1) + $fv;
            }
            $y0 = $y1;
            $y1 = $y;
            ++$i;
        }
        return $rate;
    }
}
