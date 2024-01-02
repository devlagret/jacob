<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\PreferenceCollectibility;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DateTime;

class CreditsCollectibilityReportController extends Controller
{
    public function index(){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name', 'savings_profit_sharing_id')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        
        $preferencecollectibility   = PreferenceCollectibility::get();
       

        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $acctcreditsaccount			= AcctCreditsAccount::with('member')->where('credits_account_last_balance', '>=', 1)
            ->where('credits_approve_status', 1)
            ->orderBy('credits_account_serial', 'ASC')	
            ->get();
            
        }else{
            $acctcreditsaccount			= AcctCreditsAccount::with('member')->where('credits_account_last_balance', '>=', 1)
            ->where('credits_approve_status', 1)
            ->where('branch_id', $branch_id)
            ->orderBy('credits_account_serial', 'ASC')	
            ->get();
        }

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
                <td><div style=\"text-align: center; font-size:14px\">KOLEKTIBILITAS PINJAMAN</div></td>
            </tr>
        </table>";
        
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"3%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Akad</div></td>
                <td width=\"18%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                <td width=\"16%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Outstanding</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tenor</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Kolektibilitas</div></td>
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $no                 = 1;
        $total1             = 0;
        $total2             = 0;
        $total3             = 0;
        $total4             = 0;
        $total5             = 0;
        $totaloutstanding   = 0;
        $date 	            = date('Y-m-d');
            
        foreach ($acctcreditsaccount as $key => $val) {
            $date1      = new DateTime($date);
            $date2      = new DateTime($val['credits_account_payment_date']);
            $interval   = $date1->diff($date2);
            $tunggakan  = $interval->days;

            if($date2 >= $date1){
                $tunggakan2 = 0;
            }else{
                $tunggakan2 = $tunggakan;
            }
            
            foreach ($preferencecollectibility as $k => $v) {
                if($tunggakan2 >= $v['collectibility_bottom'] && $tunggakan2 <= $v['collectibility_top']){
                    $collectibility = $v['collectibility_id'];
                    $collectibility_name = $v['collectibility_name'];
                }
            }

            $credits_account_payment_to = ($val['credits_account_payment_to'] + 1); 
            
            $export .= "
            <tr>
                <td width=\"3%\"><div style=\"text-align: left;\">".$no."</div></td>
                <td width=\"10%\"><div style=\"text-align: left;\">".$val['credits_account_serial']."</div></td>
                <td width=\"18%\"><div style=\"text-align: left;\">{$val->member->member_name}</div></td>
                <td width=\"20%\"><div style=\"text-align: left;\">{$val->member->member_address}</div></td>
                <td width=\"12%\"><div style=\"text-align: right;\">".number_format($val['credits_account_last_balance'], 2)."</div></td>
                <td width=\"10%\"><div style=\"text-align: right;\">{$credits_account_payment_to} / ".$val['credits_account_period']."</div></td>
                <td width=\"15%\"><div style=\"text-align: right;\">{$collectibility_name}</div></td>
            </tr>";

            $no++;

            if($collectibility == 1){
                $total1 = $total1 + $val['credits_account_last_balance'];
            } else if($collectibility == 2){
                $total2 = $total2 + $val['credits_account_last_balance'];
            } else if($collectibility == 3){
                $total3 = $total3 + $val['credits_account_last_balance'];
            } else if($collectibility == 4){
                $total4 = $total4 + $val['credits_account_last_balance'];
            } else if($collectibility == 5){
                $total5 = $total5 + $val['credits_account_last_balance'];
            }
            $totaloutstanding += $val['credits_account_last_balance'];
        }			

        $export .= "
            <tr>
                <td colspan =\"3\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Total </div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totaloutstanding, 2)."</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
            </tr>
        </table>";

        $export .= "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"15%\"><div style=\"text-align: left; font-size:12px; font-weight:bold\">REKAPITULASI :</div></td>
                <td width=\"20%\"><div style=\"text-align: left; font-size:12px; font-weight:bold\"></div></td>
                <td width=\"20%\"><div style=\"text-align: left; font-size:12px; font-weight:bold\"></div></td>
            </tr>";

        foreach ($preferencecollectibility as $k => $v) {
            if($v['collectibility_id'] == 1){
                $persent1 	= ($total1 / $totaloutstanding) * 100;
                // $persent1 	= 0;
                $ppap1 		= ($total1 * $v['collectibility_ppap']) / 100;
                $export .= "
                <tr>
                    <td width=\"15%\"><div style=\"text-align: left; font-size:12px;\">JUMLAH KOLEKT ".$v['collectibility_id']."</div></td>
                    <td width=\"20%\"><div style=\"text-align: right; font-size:12px;\"> ".number_format($total1, 2)." ( ".number_format($persent1, 2)." ) % &nbsp;&nbsp;</div></td>
                    <td width=\"15%\"><div style=\"text-align: left; font-size:12px;\">".$v['collectibility_name']."</div></td>
                    <td width=\"10%\"><div style=\"text-align: left; font-size:12px;\">PPAP (".$v['collectibility_ppap']." %)</div></td>
                    <td width=\"15%\"><div style=\"text-align: right; font-size:12px;\"> ".number_format($ppap1, 2)."</div></td>
                </tr>";
            } else if($v['collectibility_id'] == 2){
                $persent2 	= ($total2 / $totaloutstanding) * 100;
                // $persent2 	= 0;
                $ppap2 		= ($total2 * $v['collectibility_ppap']) / 100;
                $export .= "
                <tr>
                    <td width=\"15%\"><div style=\"text-align: left; font-size:12px;\">JUMLAH KOLEKT ".$v['collectibility_id']."</div></td>
                    <td width=\"20%\"><div style=\"text-align: right; font-size:12px;\"> ".number_format($total2, 2)." ( ".number_format($persent2, 2)." ) % &nbsp;&nbsp;</div></td>
                    <td width=\"15%\"><div style=\"text-align: left; font-size:12px;\">".$v['collectibility_name']."</div></td>
                    <td width=\"10%\"><div style=\"text-align: left; font-size:12px;\">PPAP (".$v['collectibility_ppap']." %)</div></td>
                    <td width=\"15%\"><div style=\"text-align: right; font-size:12px;\"> ".number_format($ppap2, 2)."</div></td>
                </tr>";
            } else if($v['collectibility_id'] == 3){
                $persent3 	= ($total3 / $totaloutstanding) * 100;
                // $persent3 	= 0;
                $ppap3 		= ($total3 * $v['collectibility_ppap']) / 100;
                $export .= "
                <tr>
                    <td width=\"15%\"><div style=\"text-align: left; font-size:12px;\">JUMLAH KOLEKT ".$v['collectibility_id']."</div></td>
                    <td width=\"20%\"><div style=\"text-align: right; font-size:12px;\"> ".number_format($total3, 2)." ( ".number_format($persent3, 2)." ) % &nbsp;&nbsp;</div></td>
                    <td width=\"15%\"><div style=\"text-align: left; font-size:12px;\">".$v['collectibility_name']."</div></td>
                    <td width=\"10%\"><div style=\"text-align: left; font-size:12px;\">PPAP (".$v['collectibility_ppap']." %)</div></td>
                    <td width=\"15%\"><div style=\"text-align: right; font-size:12px;\"> ".number_format($ppap3, 2)."</div></td>
                </tr>";
            } else if($v['collectibility_id'] == 4){
                $persent4 	= ($total4 / $totaloutstanding) * 100;
                // $persent4 	= 0;
                $ppap4 		= ($total4 * $v['collectibility_ppap']) / 100;
                $export .= "
                <tr>
                    <td width=\"15%\"><div style=\"text-align: left; font-size:12px;\">JUMLAH KOLEKT ".$v['collectibility_id']."</div></td>
                    <td width=\"20%\"><div style=\"text-align: right; font-size:12px;\"> ".number_format($total4, 2)." ( ".number_format($persent4, 2)." ) % &nbsp;&nbsp;</div></td>
                    <td width=\"15%\"><div style=\"text-align: left; font-size:12px;\">".$v['collectibility_name']."</div></td>
                    <td width=\"10%\"><div style=\"text-align: left; font-size:12px;\">PPAP (".$v['collectibility_ppap']." %)</div></td>
                    <td width=\"15%\"><div style=\"text-align: right; font-size:12px;\"> ".number_format($ppap4, 2)."</div></td>
                </tr>";
            } else if($v['collectibility_id'] == 5){
                $persent5 	= ($total5 / $totaloutstanding) * 100;
                // $persent5 	= 0;
                $ppap5 		= ($total5 * $v['collectibility_ppap']) / 100;
                $export .= "
                <tr>
                    <td width=\"15%\"><div style=\"text-align: left; font-size:12px;\">JUMLAH KOLEKT ".$v['collectibility_id']."</div></td>
                    <td width=\"20%\"><div style=\"text-align: right; font-size:12px;\"> ".number_format($total5, 2)." ( ".number_format($persent5, 2)." ) % &nbsp;&nbsp;</div></td>
                    <td width=\"15%\"><div style=\"text-align: left; font-size:12px;\">".$v['collectibility_name']."</div></td>
                    <td width=\"10%\"><div style=\"text-align: left; font-size:12px;\">PPAP (".$v['collectibility_ppap']." %)</div></td>
                    <td width=\"15%\"><div style=\"text-align: right; font-size:12px;\"> ".number_format($ppap5, 2)."</div></td>
                </tr>";
            }
        }

        $export .= "</table>";
        
        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Kolektibilitas Pinjaman.pdf';
        $pdf::Output($filename, 'I');
    }
}
