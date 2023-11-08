<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
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

class AcctCreditsAccountPaidOffReportController extends Controller
{
    public function index(){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name', 'savings_profit_sharing_id')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        
        $acctcreditspayment	= AcctCreditsAccount::withoutGlobalScopes()
        ->select('acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_credits_account.credits_account_principal_amount', 'acct_credits_account.credits_account_interest_amount', 'acct_credits_account.credits_account_period', 'acct_credits_account.credits_account_due_date', 'acct_credits_account.credits_account_last_balance')
        ->join('core_member', 'acct_credits_account.member_id', '=' ,'core_member.member_id')
        ->where('acct_credits_account.data_state', 0)
        ->where('acct_credits_account.credits_approve_status', 1)
        ->where('acct_credits_account.credits_account_status', 1)
        ->orWhere('acct_credits_account.credits_account_status', 2)
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
        
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td><div style=\"text-align: left;font-size:12;font-weight:bold\">DAFTAR NASABAH PINJAMAN YANG SUDAH LUNAS</div></td>		
            </tr>					
        </table>";
        
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">NO.</div></td>
                <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">NO. AKAD</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">NAMA</div></td>
                <td width=\"18%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">ALAMAT</div></td>
                <td width=\"13%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">POKOK</div></td>
                <td width=\"13%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">BUNGA</div></td>
                <td width=\"13%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">SALDO POKOK</div></td>			       
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $no             = 1;
        $totalpokok     = 0;
        $totalmargin    = 0;
        foreach ($acctcreditspayment as $key => $val) {
            $export .= "
            <tr>
                <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                <td width=\"12%\"><div style=\"text-align: left;\">".$val['credits_account_serial']."</div></td>
                <td width=\"15%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                <td width=\"18%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                <td width=\"13%\"><div style=\"text-align: right;\">".number_format($val['credits_account_principal_amount'])."</div></td>
                <td width=\"13%\"><div style=\"text-align: right;\">".number_format($val['credits_account_interest_amount'], 2)."</div></td>
                <td width=\"13%\"><div style=\"text-align: right;\">".number_format($val['credits_account_last_balance'])."</div></td>
            </tr>";

            $totalpokok 	+= $val['credits_account_principal_amount'];
            $totalmargin 	+= $val['credits_account_interest_amount'];
            $no++;
        }

        $export .= "
            <tr>
                <td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Jumlah </div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalpokok, 2)."</div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalmargin, 2)."</div></td>
                <td style=\"border-top: 1px solid black\"></td>
            </tr>
        </table>";
        
        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Pinjaman Lunas.pdf';
        $pdf::Output($filename, 'I');
    }
}
