<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctCreditsPayment;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavingsCashMutation;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TaxReportController extends Controller
{
    public function index()
    {
        $corebranch = CoreBranch::where('data_state', 0)->get();

        return view('content.TaxReport.index', compact('corebranch'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "start_date"    => $request->start_date,
            "end_date"	    => $request->end_date,
            "view"		    => $request->view,
        );

        if($sesi['view'] == 'pdf'){
            $this->processPrinting($sesi);
        }
    }

    public function processPrinting($sesi){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'account_income_tax_id')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $totaltax           = 0;
        
        $datatax            = AcctJournalVoucherItem::select('acct_journal_voucher_item.*', 'acct_journal_voucher.journal_voucher_date')
        ->join('acct_journal_voucher', 'acct_journal_voucher.journal_voucher_id', '=', 'acct_journal_voucher_item.journal_voucher_id')
        ->where('acct_journal_voucher_item.data_state', 0)
        ->where('acct_journal_voucher_item.account_id', $preferencecompany['account_income_tax_id'])	
        ->where('acct_journal_voucher_item.journal_voucher_debit_amount', '>', 0)	
        ->where('acct_journal_voucher.journal_voucher_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))	
        ->where('acct_journal_voucher.journal_voucher_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
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
        
        $export .="
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"100%\"><div style=\"text-align: left; font-size:14px; font-weight:bold\">DAFTAR PAJAK ".$sesi['start_date']." - ".$sesi['end_date']."</div></td>
            </tr>
        </table>
        <br>";

        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"20%\" style=\"font-weight:bold; border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tanggal</div></td>
                <td width=\"50%\" style=\"font-weight:bold; border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Keterangan</div></td>
                <td width=\"30%\" style=\"font-weight:bold; border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Jumlah</div></td>
            </tr>				
        </table>
        <table>";

        if(count($datatax) > 0){
            foreach($datatax as $key => $val){
                $export .= "
                <tr>
                    <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">".$val['journal_voucher_date']."</div></td>
                    <td width=\"50%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">".$val['journal_voucher_description']."</div></td>
                    <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">".number_format($val['journal_voucher_debit_amount'], 2)."</div></td>
                </tr>";
                $totaltax += $val['journal_voucher_debit_amount'];
            }
        }else{
            $export .= "
            <tr>
                <td width=\"100%\" colspan =\"3\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Data Kosong</div></td>
            </tr>";
        }
        
        $export .= "
            <tr>
                <td colspan =\"1\"><div style=\"border-bottom: 1px solid black;border-top: 1px solid black; font-size:10;font-weight:bold;text-align:center\"> </div></td>
                <td><div style=\"border-bottom: 1px solid black;border-top: 1px solid black; font-size:10;font-weight:bold;text-align:center\">Total </div></td>
                <td><div style=\"border-bottom: 1px solid black;border-top: 1px solid black; font-size:10;text-align:right\">".number_format($totaltax, 2)."</div></td>
            </tr>
        </table>
        <br>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Pajak.pdf';
        $pdf::Output($filename, 'I');
    }
}
