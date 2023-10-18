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
use DateTime;

class SavingsProfitSharingReportController extends Controller
{
    public function index(){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name', 'savings_profit_sharing_id')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $date 	            = date('Y-m-d');
        $month 	            = date('m', strtotime($date));
        $year 	            = date('Y', strtotime($date));

        if($month == 1){
            $month 	= 12;
            $year 	= $year - 1;
        } else {
            $month 	= $month - 1;
            $year 	= $year;
        }
        $period                     = $month.$year;
        $acctsavingsprofitsharing 	= AcctSavingsProfitSharing::select('acct_savings_profit_sharing.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_profit_sharing.member_id', 'core_member.member_name', 'acct_savings_profit_sharing.savings_profit_sharing_amount', 'acct_savings_profit_sharing.savings_account_last_balance')
        ->join('core_member', 'acct_savings_profit_sharing.member_id', '=', 'core_member.member_id')
        ->join('acct_savings_account', 'acct_savings_profit_sharing.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->where('savings_profit_sharing_period', $period)
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
        
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">DAFTAR BUNGA TABUNGAN SIMPANAN BULAN INI</div></td>
            </tr>
        </table>";
        
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"3%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">No</div></td>
                <td width=\"15%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">No. Rekening</div></td>
                <td width=\"25%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Nama</div></td>
                <td width=\"7%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Sandi</div></td>
                <td width=\"20%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Nominal</div></td>
                <td width=\"20%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Saldo</div></td>
            </tr>			
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $no     = 1;
        $total  = 0;
        foreach ($acctsavingsprofitsharing as $key => $val) {
            $mutation_code = AcctMutation::select('mutation_code')
            ->where('mutation_id', $preferencecompany['savings_profit_sharing_id'])
            ->first()
            ->mutation_code;

            $export .= "
            <tr>
                <td width=\"3%\"><div style=\"text-align: left;\">$no</div></td>
                <td width=\"15%\"><div style=\"text-align: left;\">".$val['savings_account_no']."</div></td>
                <td width=\"25%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                <td width=\"7%\"><div style=\"text-align: center;\">".$mutation_code."</div></td>
                <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['savings_profit_sharing_amount'], 2)."</div></td>
                <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['savings_account_last_balance'], 2)."</div></td>
            </tr>";

            $total += $val['savings_profit_sharing_amount'];
            $no++;
        }
        
        $export .= "
            <tr>
                <td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Jumlah </div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($total, 2)."</div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"></div></td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan BO Pinjaman.pdf';
        $pdf::Output($filename, 'I');
    }
}
