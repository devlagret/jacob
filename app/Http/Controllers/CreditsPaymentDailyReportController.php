<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPayment;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DateTime;

class CreditsPaymentDailyReportController extends Controller
{
    public function index()
    {
        $corebranch = CoreBranch::where('data_state', 0)->get();

        return view('content.CreditsPaymentDailyReport.index', compact('corebranch'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "start_date"    => $request->start_date,
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
        
        $acctcreditspayment	= AcctCreditsPayment::select('acct_credits_payment.*', 'core_member.member_name', 'acct_credits_account.credits_account_serial')
        ->join('core_member', 'acct_credits_payment.member_id', '=', 'core_member.member_id')
        ->join('acct_credits_account', 'acct_credits_payment.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->where('acct_credits_payment.data_state', 0)
        ->where('acct_credits_payment.credits_payment_date', date('Y-m-d', strtotime($sesi['start_date'])));
        if(!empty($branch_id)){
            $acctcreditspayment	= $acctcreditspayment->where('acct_credits_payment.branch_id', $branch_id);
        }
        $acctcreditspayment	= $acctcreditspayment->get();
        
        $acctcredits 		= AcctCredits::select('acct_credits.credits_id', 'acct_credits.credits_name')
        ->where('acct_credits.data_state', 0)
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

        $pdf::SetFont('helvetica', '', 8);

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
                <td><div style=\"text-align: center; font-size:14px\">DAFTAR ANGSURAN PINJAMAN</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center; font-size:10px\">".$sesi['start_date']."</div></td>
            </tr>
        </table>";
        
        $export .= "
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"3%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:9;\">No.</div></td>
                <td width=\"7%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">No. Kredit</div></td>
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Nama</div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Sisa Pokok Awal</div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Angs Pokok</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Angs Bunga</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Total Angs</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Denda</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Sisa Pokok Akhir</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Angsuran ke</div></td>
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $no 				= 1;
        $totaldenda 		= 0;
        $totalpokokakhir 	= 0;
        $totalangspokok 	= 0;
        $totalangsmargin 	= 0;
        $totaltotal 		= 0;
        if(!empty($acctcreditspayment)){
            foreach ($acctcreditspayment as $key => $val) {
                $export .= "
                <tr>
                    <td width=\"3%\"><div style=\"text-align: left;\">".$no."</div></td>
                    <td width=\"7%\"><div style=\"text-align: left;\">".$val['credits_account_serial']."</div></td>
                    <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                    <td width=\"10%\"><div style=\"text-align: left;\">".number_format($val['credits_principal_opening_balance'], 2)."</div></td>
                    <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_payment_principal'], 2)."</div></td>
                    <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_payment_interest'], 2)."</div></td>
                    <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_payment_amount'], 2)."</div></td>
                    <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_payment_fine'], 2)."</div></td>
                    <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['credits_principal_last_balance'], 2)."</div></td>
                    <td width=\"10%\"><div style=\"text-align: right;\">".$val['credits_payment_to']."</div></td>
                </tr>";

                $totaldenda 		+= $val['credits_payment_fine'];
                $totalangspokok 	+= $val['credits_payment_principal'];
                $totalangsmargin 	+= $val['credits_payment_interest'];
                $totalpokokakhir 	+= $val['credits_principal_last_balance'];
                $totaltotal			+= $val['credits_payment_amount'];
                $no++;
            }
        }

        $export .= "
            <tr>
                <td colspan =\"3\"><div style=\"text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-weight:bold;text-align:center\">Total </div></td>
                <td style=\"font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align:right\">".number_format($totalangspokok, 2)."</div></td>
                <td style=\"font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align:right\">".number_format($totalangsmargin, 2)."</div></td>
                <td style=\"font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align:right\">".number_format($totaltotal, 2)."</div></td>
                <td style=\"font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align:right\">".number_format($totaldenda, 2)."</div></td>
                <td style=\"font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align:right\">".number_format($totalpokokakhir, 2)."</div></td>
                <td style=\"font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align:right\"></div></td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Angsuran Pinjaman Harian.pdf';
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

        $acctcreditspayment	= AcctCreditsPayment::select('acct_credits_payment.*', 'core_member.member_name', 'acct_credits_account.credits_account_serial')
        ->join('core_member', 'acct_credits_payment.member_id', '=', 'core_member.member_id')
        ->join('acct_credits_account', 'acct_credits_payment.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->where('acct_credits_payment.data_state', 0)
        ->where('acct_credits_payment.credits_payment_date', date('Y-m-d', strtotime($sesi['start_date'])));
        if(!empty($branch_id)){
            $acctcreditspayment	= $acctcreditspayment->where('acct_credits_payment.branch_id', $branch_id);
        }
        $acctcreditspayment	= $acctcreditspayment->get();
        
        $acctcredits 		= AcctCredits::select('acct_credits.credits_id', 'acct_credits.credits_name')
        ->where('acct_credits.data_state', 0)
        ->get();

        if(count($acctcreditspayment)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan Angsuran Pinjaman Harian")
                                            ->setSubject("")
                                            ->setDescription("Laporan Angsuran Pinjaman Harian")
                                            ->setKeywords("Laporan, Angsuran, Pinjaman, Harian")
                                            ->setCategory("Laporan Angsuran Pinjaman Harian");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan Agsrn Pjmn Harian");
            
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
            
            $spreadsheet->getActiveSheet()->mergeCells("B1:K1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:K3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:K3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:K3')->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR ANGSURAN PINJAMAN ".$sesi['start_date']);
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"No. Kredit");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Sisa Pokok Awal");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Angsuran Pokok");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Angsuran Bunga");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Total Angsuran");
            $spreadsheet->getActiveSheet()->setCellValue('I3',"Denda");
            $spreadsheet->getActiveSheet()->setCellValue('J3',"Sisa Pokok Akhir");
            $spreadsheet->getActiveSheet()->setCellValue('K3',"Angsuran ke");
            
            $no 				= 0;
            $totaldenda 		= 0;
            $totalpokokakhir 	= 0;
            $totalangspokok 	= 0;
            $totalangsmargin 	= 0;
            $totaltotal 		= 0;
            $j                  = 4;
            foreach($acctcreditspayment as $key=>$val){
                $no++;

                $spreadsheet->getActiveSheet()->getStyle('B'.$j.':K'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
                $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['credits_account_serial']);
                $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
                $spreadsheet->getActiveSheet()->setCellValue('E'.$j, number_format($val['credits_principal_opening_balance'],2));
                $spreadsheet->getActiveSheet()->setCellValue('F'.$j, number_format($val['credits_payment_principal'],2));
                $spreadsheet->getActiveSheet()->setCellValue('G'.$j, number_format($val['credits_payment_interest'],2));
                $spreadsheet->getActiveSheet()->setCellValue('H'.$j, number_format($val['credits_payment_amount'],2));
                $spreadsheet->getActiveSheet()->setCellValue('I'.$j, number_format($val['credits_payment_fine'],2));
                $spreadsheet->getActiveSheet()->setCellValue('J'.$j, number_format($val['credits_principal_last_balance'],2));
                $spreadsheet->getActiveSheet()->setCellValue('K'.$j, $val['credits_payment_to']);
    
                $totaldenda 		+= $val['credits_payment_fine'];
                $totalangspokok 	+= $val['credits_payment_principal'];
                $totalangsmargin 	+= $val['credits_payment_interest'];
                $totalpokokakhir 	+= $val['credits_principal_last_balance'];
                $totaltotal			+= $val['credits_payment_amount'];
                $j++;
            }

            $i = $j;
            
            $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':F'.$i);
            $spreadsheet->getActiveSheet()->getStyle('F'.$j.':J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('B'.$i.':K'.$i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $spreadsheet->getActiveSheet()->getStyle('B'.$i.':K'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->setCellValue('B'.$i, 'Total');
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i, number_format($totalangspokok,2));
            $spreadsheet->getActiveSheet()->setCellValue('G'.$i, number_format($totalangsmargin,2));
            $spreadsheet->getActiveSheet()->setCellValue('H'.$i, number_format($totaltotal,2));
            $spreadsheet->getActiveSheet()->setCellValue('I'.$i, number_format($totaldenda,2));
            $spreadsheet->getActiveSheet()->setCellValue('J'.$i, number_format($totalpokokakhir,2));

            ob_clean();
            $filename='Laporan Angsuran Pinjaman Harian.xls';
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
