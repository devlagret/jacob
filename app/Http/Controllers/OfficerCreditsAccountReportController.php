<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\CoreOffice;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DateTime;

class OfficerCreditsAccountReportController extends Controller
{
    public function index()
    {  
        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $corebranch         = CoreBranch::where('data_state', 0)
            ->get();
            $coreoffice = CoreOffice::where('data_state', 0)->get();
        }else{
            $corebranch         = CoreBranch::where('data_state', 0)
            ->where('branch_id', $branch_id)
            ->get();
            $coreoffice = CoreOffice::where('branch_id', $branch_id)
            ->where('data_state', 0)->get();
        }

        return view('content.OfficerCreditsAccountReport.index', compact('corebranch', 'coreoffice'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "start_date"    => $request->start_date,
            "end_date"	    => $request->end_date,
            "office_id"	    => $request->office_id,
            "branch_id"	    => $request->branch_id,
            "view"		    => $request->view,
        );

        if($sesi['view'] == 'pdf'){
            $this->processPrinting($sesi);
        }else{
            $this->export($sesi);
        }
    }

    public function processPrinting($sesi){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);

        if($branch_status == 1){
            if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
                $branch_id = '';
            } else {
                $branch_id = $sesi['branch_id'];
            }
        }

        $acctcredits   = AcctCredits::select('credits_id', 'credits_name')
        ->where('data_state', 0)
        ->get();

        $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 6, 6, 6);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage('L');

        $pdf::SetFont('helvetica', '', 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $export = "
        ";
        
        $export .= "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td><div style=\"text-align: left;font-size:10; font-weight:bold\">".$preferencecompany['company_name']."</div></td>		       
            </tr>						
        </table>";

        if(!empty($sesi['office_id'])){
            $office_name = CoreOffice::select('office_name')
            ->where('office_id', $sesi['office_id'])
            ->first()
            ->office_name;

            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">DAFTAR NASABAH PINJAMAN : ".$office_name."</div></td>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">Mulai Tgl. ".$sesi['start_date']." S.D ".$sesi['end_date']."</div></td>			       
                </tr>						
            </table>";

            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td width=\"2%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Kredit</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                    <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Pokok</div></td>
                    <td width=\"8%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bunga</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Sisa Pokok</div></td>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Jangka Waktu</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Angsuran</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Keterlambatan</div></td>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Ak Denda</div></td>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Pdptn Adm</div></td>
                </tr>				
            </table>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

            foreach ($acctcredits as $kC => $vC) {
                $acctcreditsaccount = AcctCreditsAccount::withoutGlobalScopes()
                ->select('acct_credits_account.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_interest', 'acct_credits_account.credits_account_last_balance', 'acct_credits_account.office_id', 'acct_credits_account.credits_account_period', 'acct_credits_account.credits_account_payment_amount', 'acct_credits_account.credits_account_accumulated_fines', 'acct_credits_account.credits_account_payment_date', 'acct_credits_account.credits_account_adm_cost')
                ->join('core_member', 'acct_credits_account.member_id', '=' ,'core_member.member_id')
                ->where('acct_credits_account.credits_id', $vC['credits_id'])
                ->where('acct_credits_account.credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('acct_credits_account.credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))			
                ->where('acct_credits_account.credits_account_last_balance', '>=', 1)
                ->where('acct_credits_account.credits_approve_status', 1)
                ->where('acct_credits_account.data_state', 0);
                if(!empty($sesi['office_id'])){
                    $acctcreditsaccount = $acctcreditsaccount->where('acct_credits_account.office_id', $sesi['office_id']);
                }
                if(!empty($branch_id)){
                    $acctcreditsaccount = $acctcreditsaccount->where('acct_credits_account.branch_id', $branch_id);
                }
                $acctcreditsaccount = $acctcreditsaccount->orderBy('acct_credits_account.credits_account_no', 'ASC')
                ->get();

                if(!empty($acctcreditsaccount)){
                    $export .= "
                        <br>
                        <tr>
                            <td colspan =\"10\" style=\"border-bottom: 1px solid black;\"><div style=\"font-size:10\">".$vC['credits_name']."</div></td>
                        </tr>
                    ";

                    $no                     = 1;
                    $totalprice 		    = 0;
                    $totalmargin 		    = 0;
                    $totalsaldoprice 	    = 0;
                    $totalangs			    = 0;
                    $totalakdenda		    = 0;
                    $totaladm			    = 0;
                    $grandtotalprice 		= 0;
                    $grandtotalsaldoprice 	= 0;
                    $grandtotalangs			= 0;
                    $grandtotalakdenda		= 0;
                    $grandtotaladm			= 0;

                    foreach ($acctcreditsaccount as $key => $val) {	
                        $day 		= date('d-m-Y');
                        $start_ 	= new DateTime($day);
                        $end_ 		= new DateTime($val['credits_account_payment_date']);

                        if($end_ >= $start_){
                            $Keterlambatan 	= '0';
                        }else{
                            $interval 		= $start_->diff($end_);
                            $Keterlambatan 	= $interval->days;
                        }

                        $acctcredits_fine                   = AcctCreditsAccount::withoutGlobalScopes()
                        ->select('acct_credits.credits_fine')
                        ->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
                        ->where('acct_credits_account.credits_account_id',$val['credits_account_id'])
                        ->first();

                        $credits_payment_fine_amount 		= (($val['credits_account_payment_amount'] * $acctcredits_fine['credits_fine']) / 100 ) * $Keterlambatan;
                        $credits_account_accumulated_fines 	= $val['credits_account_accumulated_fines'] + $credits_payment_fine_amount;	

                        $export .= "
                        <tr>
                            <td width=\"2%\"><div style=\"text-align: left;\">".$no."</div></td>
                            <td width=\"10%\"><div style=\"text-align: left;\">".$val['credits_account_serial']."</div></td>
                            <td width=\"10%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                            <td width=\"15%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_account_amount'], 2)."</div></td>
                            <td width=\"8%\"><div style=\"text-align: center;\">".number_format($val['credits_account_interest'], 2)."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_account_last_balance'], 2)."</div></td>
                            <td width=\"5%\"><div style=\"text-align: right;\">".$val['credits_account_period']."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_account_payment_amount'],2)."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".$Keterlambatan."</div></td>
                            <td width=\"5%\"><div style=\"text-align: right;\">".number_format($credits_account_accumulated_fines, 2)."</div></td>
                            <td width=\"5%\"><div style=\"text-align: right;\">".number_format($val['credits_account_adm_cost'], 2)."</div></td>
                        </tr>";

                        $totalprice 		+= $val['credits_account_amount'];
                        $totalsaldoprice 	+= $val['credits_account_last_balance'];
                        $totalangs			+= $val['credits_account_payment_amount'];
                        $totalakdenda		+= $val['credits_account_accumulated_fines'];
                        $totaladm			+= $val['credits_account_adm_cost'];
                        $no++;
                    }

                    $export .= "	
                    <tr>
                        <td colspan =\"3\" style=\"border-top: 1px solid black;\"></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Subtotal </div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalprice, 2)."</div></td>								
                        <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalsaldoprice, 2)."</div></td>
                        <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalangs, 2)."</div></td>
                        <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalakdenda, 2)."</div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totaladm, 2)."</div></td>
                    </tr>";

                    $grandtotalprice 		+= $totalprice;
                    $grandtotalsaldoprice 	+= $totalsaldoprice;
                    $grandtotalangs			+= $totalangs;
                    $grandtotalakdenda		+= $totalakdenda;
                    $grandtotaladm			+= $totaladm;
                }
            }

            $export .= "
                <br>	
                <tr>
                    <td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:9;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Jumlah </div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalprice, 2)."</div></td>							
                    <td colspan =\"2\"  style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalsaldoprice, 2)."</div></td>
                    <td colspan =\"2\"  style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalangs, 2)."</div></td>
                    <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalakdenda, 2)."</div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotaladm, 2)."</div></td>							
                </tr>								
            </table>";
        } else {
            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">DAFTAR NASABAH PINJAMAN</div></td>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">Mulai Tgl. ".$sesi['start_date']." S.D ".$sesi['end_date']."</div></td>			       
                </tr>						
            </table>";

            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td width=\"2%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Kredit</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                    <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                    <td width=\"3%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">BO</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Pokok</div></td>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bunga</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sisa Pokok</div></td>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Jangka Waktu</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Angsuran</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Keterlambatan</div></td>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Ak Denda</div></td>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Pdptn Adm</div></td>
                </tr>				
            </table>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

            $no   = 1;
            foreach ($acctcredits as $kC => $vC) {
                $acctcreditsaccount = AcctCreditsAccount::withoutGlobalScopes()
                ->select('acct_credits_account.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_interest', 'acct_credits_account.credits_account_last_balance', 'acct_credits_account.office_id', 'acct_credits_account.credits_account_period', 'acct_credits_account.credits_account_payment_amount', 'acct_credits_account.credits_account_accumulated_fines', 'acct_credits_account.credits_account_payment_date', 'acct_credits_account.credits_account_adm_cost')
                ->join('core_member', 'acct_credits_account.member_id', '=' ,'core_member.member_id')
                ->where('acct_credits_account.credits_id', $vC['credits_id'])
                ->where('acct_credits_account.credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('acct_credits_account.credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))			
                ->where('acct_credits_account.credits_account_last_balance', '>=', 1)
                ->where('acct_credits_account.credits_approve_status', 1)
                ->where('acct_credits_account.data_state', 0);
                if(!empty($sesi['office_id'])){
                    $acctcreditsaccount = $acctcreditsaccount->where('acct_credits_account.office_id', $sesi['office_id']);
                }
                if(!empty($branch_id)){
                    $acctcreditsaccount = $acctcreditsaccount->where('acct_credits_account.branch_id', $branch_id);
                }
                $acctcreditsaccount = $acctcreditsaccount->orderBy('acct_credits_account.credits_account_no', 'ASC')
                ->get();

                if(!empty($acctcreditsaccount)){
                    $export .= "
                    <br>
                    <tr>
                        <td colspan =\"11\" style=\"border-bottom: 1px solid black;\"><div style=\"font-size:10\">".$vC['credits_name']."</div></td>
                    </tr>";

                    $no                     = 1;
                    $totalprice 		    = 0;
                    $totalmargin 		    = 0;
                    $totalsaldoprice 	    = 0;
                    $totalangs			    = 0;
                    $totalakdenda		    = 0;
                    $totaladm			    = 0;
                    $grandtotalprice 		= 0;
                    $grandtotalsaldoprice 	= 0;
                    $grandtotalangs			= 0;
                    $grandtotalakdenda		= 0;
                    $grandtotaladm			= 0;
                    foreach ($acctcreditsaccount as $key => $val) {	
                        $day 		= date('d-m-Y');
                        $start_ 	= new DateTime($day);
                        $end_ 		= new DateTime($val['credits_account_payment_date']);

                        if($end_ >= $start_){
                            $Keterlambatan 	= '0';
                        }else{
                            $interval 		= $start_->diff($end_);
                            $Keterlambatan 	= $interval->days;
                        }

                        $acctcredits_fine                   = AcctCreditsAccount::withoutGlobalScopes()
                        ->select('acct_credits.credits_fine')
                        ->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
                        ->where('acct_credits_account.credits_account_id', $val['credits_account_id'])
                        ->first();

                        $credits_payment_fine_amount 		= (($val['credits_account_payment_amount'] * $acctcredits_fine['credits_fine']) / 100 ) * $Keterlambatan;
                        $credits_account_accumulated_fines 	= $val['credits_account_accumulated_fines'] + $credits_payment_fine_amount;	

                        $office_code = CoreOffice::select('office_code')
                        ->where('office_id', $val['office_id'])
                        ->first()
                        ->office_code;
        
                        $export .= "
                        <tr>
                            <td width=\"2%\"><div style=\"text-align: left;\">".$no."</div></td>
                            <td width=\"10%\"><div style=\"text-align: left;\">".$val['credits_account_serial']."</div></td>
                            <td width=\"10%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                            <td width=\"15%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                            <td width=\"3%\"><div style=\"text-align: center;\">".$office_code."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_account_amount'], 2)."</div></td>
                            <td width=\"5%\"><div style=\"text-align: right;\">".number_format($val['credits_account_interest'], 2)."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_account_last_balance'], 2)."</div></td>
                            <td width=\"5%\"><div style=\"text-align: center;\">".$val['credits_account_period']."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_account_payment_amount'],2)."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".$Keterlambatan."</div></td>
                            <td width=\"5%\"><div style=\"text-align: right;\">".number_format($credits_account_accumulated_fines, 2)."</div></td>
                            <td width=\"5%\"><div style=\"text-align: right;\">".number_format($val['credits_account_adm_cost'], 2)."</div></td>
                        </tr>";
                        $no++;

                        $totalprice 		+= $val['credits_account_amount'];
                        $totalsaldoprice 	+= $val['credits_account_last_balance'];
                        $totalangs			+= $val['credits_account_payment_amount'];
                        $totalakdenda		+= $val['credits_account_accumulated_fines'];
                        $totaladm			+= $val['credits_account_adm_cost'];
                    }

                    $export .= "	
                    <tr>
                        <td colspan =\"5\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:right\">Subtotal </div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalprice, 2)."</div></td>									
                        <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalsaldoprice, 2)."</div></td>
                        <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalangs, 2)."</div></td>
                        <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalakdenda, 2)."</div></td>		
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totaladm, 2)."</div></td>									
                    </tr>";

                    $grandtotalprice 		+= $totalprice;
                    $grandtotalsaldoprice 	+= $totalsaldoprice;
                    $grandtotalangs			+= $totalangs;
                    $grandtotalakdenda		+= $totalakdenda;
                    $grandtotaladm			+= $totaladm;
                }
            }

            $export .= "
                <br>	
                <tr>
                    <td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:9;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                    <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:right\">Jumlah </div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalprice, 2)."</div></td>							
                    <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalsaldoprice, 2)."</div></td>
                    <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalangs, 2)."</div></td>	
                    <td colspan =\"2\" style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalakdenda, 2)."</div></td>	
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotaladm, 2)."</div></td>	
                </tr>								
            </table>";
        }

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan BO Pinjaman.pdf';
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

        $acctcredits   = AcctCredits::select('credits_id', 'credits_name')
        ->where('data_state', 0)
        ->get();

        if(count($acctcredits)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan BO Pinjaman")
                                            ->setSubject("")
                                            ->setDescription("Laporan BO Pinjaman")
                                            ->setKeywords("Laporan, BO, Pinjaman")
                                            ->setCategory("Laporan BO Pinjaman");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan BO Pinjaman");

            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);		
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);	
            $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);	
            $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);	
            $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);	

            if(empty($sesi['office_id'])){
                $spreadsheet->getActiveSheet()->mergeCells("B1:M1");
                $spreadsheet->getActiveSheet()->mergeCells("B2:M2");
                $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
                $spreadsheet->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B2')->getFont()->setSize(11);
                $spreadsheet->getActiveSheet()->getStyle('B3:M3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B3:M3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B3:M3')->getFont()->setBold(true);

                $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR NASABAH PINJAMAN");
            } else {
                $spreadsheet->getActiveSheet()->mergeCells("B1:M1");
                $spreadsheet->getActiveSheet()->mergeCells("B2:M2");
                $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
                $spreadsheet->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B2')->getFont()->setSize(11);
                $spreadsheet->getActiveSheet()->getStyle('B3:M3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B3:M3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B3:M3')->getFont()->setBold(true);

                $office_name = CoreOffice::select('office_name')
                ->where('office_id', $sesi['office_id'])
                ->first()
                ->office_name;

                $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR NASABAH PINJAMAN ".$office_name);
            }

            $spreadsheet->getActiveSheet()->setCellValue('B2',"Periode : ".$sesi['start_date']." S.D ".$sesi['end_date']);
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"No. Rek");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Alamat");				
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Pokok");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Bunga");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Sisa Pokok");
            $spreadsheet->getActiveSheet()->setCellValue('I3',"Jangka Waktu");
            $spreadsheet->getActiveSheet()->setCellValue('J3',"Angsuran");
            $spreadsheet->getActiveSheet()->setCellValue('K3',"Keterlambatan");
            $spreadsheet->getActiveSheet()->setCellValue('L3',"Denda");
            $spreadsheet->getActiveSheet()->setCellValue('M3',"Pendapatan Administrasi");
                            
            $no             = 0;
            $totalnominal   = 0;

            if(empty($sesi['office_id'])){
                $i = 4;
                foreach ($acctcredits as $k => $v) {
                    $acctcreditsaccount = AcctCreditsAccount::withoutGlobalScopes()
                    ->select('acct_credits_account.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_interest', 'acct_credits_account.credits_account_last_balance', 'acct_credits_account.office_id', 'acct_credits_account.credits_account_period','acct_credits_account.credits_account_payment_amount', 'acct_credits_account.credits_account_accumulated_fines', 'acct_credits_account.credits_account_payment_date', 'acct_credits_account.credits_account_adm_cost')
                    ->join('core_member', 'acct_credits_account.member_id', '=', 'core_member.member_id')
                    ->where('acct_credits_account.credits_id', $v['credits_id'])
                    ->where('acct_credits_account.credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                    ->where('acct_credits_account.credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))			
                    ->where('acct_credits_account.credits_account_last_balance', '>=', 1)
                    ->where('acct_credits_account.credits_approve_status', 1)
                    ->where('acct_credits_account.data_state', 0);
                    if(!empty($sesi['office_id'])){
                        $acctcreditsaccount = $acctcreditsaccount->where('acct_credits_account.office_id', $sesi['office_id']);
                    }
                    if(!empty($branch_id)){
                        $acctcreditsaccount = $acctcreditsaccount->where('acct_credits_account.branch_id', $branch_id);
                    }
                    $acctcreditsaccount = $acctcreditsaccount->orderBy('acct_credits_account.credits_account_no', 'ASC')
                    ->get();

                    if(!empty($acctcreditsaccount)){
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':K'.$i);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getFont()->setBold(true)->setSize(14);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i.':K'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                        $spreadsheet->getActiveSheet()->setCellValue('B'.$i, $v['credits_name']);

                        foreach ($acctcreditsaccount as $k => $v) {								
                            $day 		= date('d-m-Y');
                            $start_ 	= new DateTime($day);
                            $end_ 		= new DateTime($v['credits_account_payment_date']);
                            if($end_ >= $start_){
                                $Keterlambatan 	= '0';
                            }else{
                                $interval 		= $start_->diff($end_);
                                $Keterlambatan 	= $interval->days;
                            }
                        }

                        $nov                    = 0;
                        $j                      = $i+1;
                        $subtotalpokok 			= 0;
                        $subtotalbunga			= 0;
                        $subtotalsisapokok 		= 0;
                        $subtotalangs			= 0;
                        $subtotalakdenda 		= 0;
                        $subtotaladm 			= 0;
                        $grandtotalpokok 		= 0;
                        $grandtotalsisapokok 	= 0;
                        $grandtotalangs 		= 0;
                        $grandtotalakdenda 		= 0;
                        $grandtotaladm 			= 0;

                        foreach($acctcreditsaccount as $key=>$val){
                            $acctcredits_fine                   = AcctCreditsAccount::withoutGlobalScopes()
                            ->select('acct_credits.credits_fine')
                            ->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
                            ->where('acct_credits_account.credits_account_id', $val['credits_account_id'])
                            ->first();

                            $credits_payment_fine_amount 		= (($val['credits_account_payment_amount'] * $acctcredits_fine['credits_fine']) / 100 ) * $Keterlambatan;
                            $credits_account_accumulated_fines 	= $val['credits_account_accumulated_fines'] + $credits_payment_fine_amount;	

                            $nov++;
                                
                            $spreadsheet->setActiveSheetIndex(0);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$j.':M'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('L'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('M'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                            $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $nov);
                            $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['credits_account_serial']);
                            $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
                            $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $val['member_address']);
                            $spreadsheet->getActiveSheet()->setCellValue('F'.$j, number_format($val['credits_account_amount'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('G'.$j, number_format($val['credits_account_interest'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('H'.$j, number_format($val['credits_account_last_balance'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('I'.$j, $val['credits_account_period']);
                            $spreadsheet->getActiveSheet()->setCellValue('J'.$j, number_format($val['credits_account_payment_amount'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('K'.$j, $Keterlambatan);
                            $spreadsheet->getActiveSheet()->setCellValue('L'.$j, number_format($credits_account_accumulated_fines,2));
                            $spreadsheet->getActiveSheet()->setCellValue('M'.$j, number_format($val['credits_account_adm_cost'],2));

                            $j++;
                            $subtotalpokok 		+= $val['credits_account_amount'];
                            $subtotalsisapokok 	+= $val['credits_account_last_balance'];
                            $subtotalangs		+= $val['credits_account_payment_amount'];
                            $subtotalakdenda 	+= $credits_account_accumulated_fines;
                            $subtotaladm 		+= $val['credits_account_adm_cost'];
                        }

                        $m = $j;
                        
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$m.':E'.$m);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$m.':M'.$m)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        $spreadsheet->getActiveSheet()->getStyle('B'.$m.':M'.$m)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                        $spreadsheet->getActiveSheet()->setCellValue('B'.$m, 'SubTotal');
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$m, number_format($subtotalpokok,2));
                        $spreadsheet->getActiveSheet()->setCellValue('H'.$m, number_format($subtotalsisapokok,2));
                        $spreadsheet->getActiveSheet()->setCellValue('J'.$m, number_format($subtotalangs,2));
                        $spreadsheet->getActiveSheet()->setCellValue('L'.$m, number_format($subtotalakdenda,2));
                        $spreadsheet->getActiveSheet()->setCellValue('M'.$m, number_format($subtotaladm,2));

                        $i = $m + 1;
                    
                        $grandtotalpokok 		+= $subtotalpokok;
                        $grandtotalsisapokok 	+= $subtotalsisapokok;
                        $grandtotalangs 		+= $subtotalangs;
                        $grandtotalakdenda 		+= $subtotalakdenda;
                        $grandtotaladm 			+= $subtotaladm;
                    }
                }
            } else {

                $i=4;
                foreach ($acctcredits as $k => $v) {
                    $acctcreditsaccount = AcctCreditsAccount::withoutGlobalScopes()
                    ->select('acct_credits_account.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_interest', 'acct_credits_account.credits_account_last_balance', 'acct_credits_account.office_id', 'acct_credits_account.credits_account_period','acct_credits_account.credits_account_payment_amount', 'acct_credits_account.credits_account_accumulated_fines', 'acct_credits_account.credits_account_payment_date', 'acct_credits_account.credits_account_adm_cost')
                    ->join('core_member', 'acct_credits_account.member_id', '=', 'core_member.member_id')
                    ->where('acct_credits_account.credits_id', $v['credits_id'])
                    ->where('acct_credits_account.credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                    ->where('acct_credits_account.credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))			
                    ->where('acct_credits_account.credits_account_last_balance', '>=', 1)
                    ->where('acct_credits_account.credits_approve_status', 1)
                    ->where('acct_credits_account.data_state', 0);
                    if(!empty($sesi['office_id'])){
                        $acctcreditsaccount = $acctcreditsaccount->where('acct_credits_account.office_id', $sesi['office_id']);
                    }
                    if(!empty($branch_id)){
                        $acctcreditsaccount = $acctcreditsaccount->where('acct_credits_account.branch_id', $branch_id);
                    }
                    $acctcreditsaccount = $acctcreditsaccount->orderBy('acct_credits_account.credits_account_no', 'ASC')
                    ->get();

                    if(!empty($acctcreditsaccount)){
                    
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':M'.$i);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getFont()->setBold(true)->setSize(14);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i.':M'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$i, $v['credits_name']);

                        foreach ($acctcreditsaccount as $k => $v) {								
                            $day 		= date('d-m-Y');
                            $start_ 	= new DateTime($day);
                            $end_ 		= new DateTime($v['credits_account_payment_date']);
                            if($end_ >= $start_){
                                $Keterlambatan 	= '0';
                            }else{
                                $interval 		= $start_->diff($end_);
                                $Keterlambatan 	= $interval->days;
                            }
                        }

                        $nov                    = 0;
                        $j                      = $i+1;
                        $subtotalpokok 			= 0;
                        $subtotalbunga			= 0;
                        $subtotalsisapokok 		= 0;
                        $subtotalangs			= 0;
                        $subtotalakdenda 		= 0;
                        $subtotaladm	 		= 0;
                        $grandtotalpokok 		= 0;
                        $grandtotalsisapokok 	= 0;
                        $grandtotalangs 		= 0;
                        $grandtotalakdenda 		= 0;
                        $grandtotaladm 			= 0;

                        foreach($acctcreditsaccount as $key=>$val){
                            $acctcredits_fine                   = AcctCreditsAccount::withoutGlobalScopes()
                            ->select('acct_credits.credits_fine')
                            ->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
                            ->where('acct_credits_account.credits_account_id', $val['credits_account_id'])
                            ->first();
                            
                            $credits_payment_fine_amount 		= (($val['credits_account_payment_amount'] * $acctcredits_fine['credits_fine']) / 100 ) * $Keterlambatan;
                            $credits_account_accumulated_fines 	= $val['credits_account_accumulated_fines'] + $credits_payment_fine_amount;	
                            $nov++;
                            
                            $spreadsheet->setActiveSheetIndex(0);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$j.':M'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('L'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('M'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        
                            $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $nov);
                            $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['credits_account_serial']);
                            $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
                            $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $val['member_address']);
                            $spreadsheet->getActiveSheet()->setCellValue('F'.$j, number_format($val['credits_account_amount'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('G'.$j, number_format($val['credits_account_interest'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('H'.$j, number_format($val['credits_account_last_balance'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('I'.$j, $val['credits_account_period']);
                            $spreadsheet->getActiveSheet()->setCellValue('J'.$j, number_format($val['credits_account_payment_amount'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('K'.$j, $Keterlambatan);
                            $spreadsheet->getActiveSheet()->setCellValue('L'.$j, number_format($credits_account_accumulated_fines ,2));
                            $spreadsheet->getActiveSheet()->setCellValue('M'.$j, number_format($val['credits_account_adm_cost'] ,2));

                            $subtotalpokok 		+= $val['credits_account_amount'];
                            $subtotalsisapokok 	+= $val['credits_account_last_balance'];
                            $subtotalangs		+= $val['credits_account_payment_amount'];
                            $subtotalakdenda 	+= $credits_account_accumulated_fines;
                            $subtotaladm 		+= $val['credits_account_adm_cost'];
                            $j++;
                        }

                        $grandtotalpokok 		+= $subtotalpokok;
                        $grandtotalsisapokok 	+= $subtotalsisapokok;
                        $grandtotalangs 		+= $subtotalangs;
                        $grandtotalakdenda 		+= $subtotalakdenda;
                        $grandtotaladm 			+= $subtotaladm;
                        $m                      = $j;
                        
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$m.':E'.$m);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$m.':M'.$m)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        $spreadsheet->getActiveSheet()->getStyle('B'.$m.':M'.$m)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$m, 'SubTotal');
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$m, number_format($subtotalpokok,2));
                        $spreadsheet->getActiveSheet()->setCellValue('H'.$m, number_format($subtotalsisapokok,2));
                        $spreadsheet->getActiveSheet()->setCellValue('J'.$m, number_format($subtotalangs,2));
                        $spreadsheet->getActiveSheet()->setCellValue('L'.$m, number_format($subtotalakdenda,2));
                        $spreadsheet->getActiveSheet()->setCellValue('M'.$m, number_format($subtotaladm,2));

                        $i = $m + 1;
                    }
                }
            }
            $n = $i;

            $spreadsheet->getActiveSheet()->mergeCells('B'.$n.':E'.$n);
            $spreadsheet->getActiveSheet()->getStyle('B'.$n.':M'.$n)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B'.$n.':M'.$n)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $spreadsheet->getActiveSheet()->setCellValue('B'.$n, 'Total');
            $spreadsheet->getActiveSheet()->setCellValue('F'.$n, number_format($grandtotalpokok,2));
            $spreadsheet->getActiveSheet()->setCellValue('H'.$n, number_format($grandtotalsisapokok,2));
            $spreadsheet->getActiveSheet()->setCellValue('J'.$n, number_format($grandtotalangs,2));
            $spreadsheet->getActiveSheet()->setCellValue('L'.$n, number_format($grandtotalakdenda,2));
            $spreadsheet->getActiveSheet()->setCellValue('M'.$n, number_format($grandtotaladm,2));

            ob_clean();
            $filename='Laporan BO Pinjaman.xls';
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
