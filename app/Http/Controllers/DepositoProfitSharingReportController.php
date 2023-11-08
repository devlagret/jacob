<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\AcctDepositoProfitSharing;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DepositoProfitSharingReportController extends Controller
{
    public function index()
    {
        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $corebranch         = CoreBranch::where('data_state', 0)
            ->get();
        }else{
            $corebranch         = CoreBranch::where('data_state', 0)
            ->where('branch_id', $branch_id)
            ->get();
        }

        return view('content.DepositoProfitSharingReport.index', compact('corebranch'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "start_date"    => $request->start_date,
            "end_date"	    => $request->end_date,
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

        $acctdepositoprofitsharing = AcctDepositoProfitSharing::select('acct_deposito_profit_sharing.deposito_account_id', 'acct_deposito_account.deposito_account_no', 'acct_deposito_profit_sharing.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_deposito_profit_sharing.member_id', 'core_member.member_name', 'acct_deposito_profit_sharing.deposito_profit_sharing_amount', 'acct_deposito_profit_sharing.deposito_account_last_balance', 'acct_deposito_profit_sharing.deposito_profit_sharing_due_date', 'acct_deposito_profit_sharing.deposito_profit_sharing_date')
        ->join('core_member', 'acct_deposito_profit_sharing.member_id', '=', 'core_member.member_id')
        ->join('acct_savings_account', 'acct_deposito_profit_sharing.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('acct_deposito_account', 'acct_deposito_profit_sharing.deposito_account_id', '=', 'acct_deposito_account.deposito_account_id')
        ->where('acct_deposito_profit_sharing.deposito_profit_sharing_due_date', '>=', $sesi['start_date'])
        ->where('acct_deposito_profit_sharing.deposito_profit_sharing_due_date', '<=', $sesi['end_date'])
        ->orderBy('acct_deposito_account.deposito_account_no', 'ASC')
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

        $pdf::AddPage('P');

        $pdf::SetFont('helvetica', '', 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $export = "
        ";

        $export .="
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td><div style=\"text-align: left;font-size:12;\">DAFTAR BUNGA SIMP BERJANGKA BULAN INI</div></td>			       
            </tr>						
        </table>";
        
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Jatuh Tempo</div></td>
                <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Dep</div></td>
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">BG Hasil</div></td>
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Saldo</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Transfer</div></td>
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $no             = 1;
        $totalnominal   = 0;
        foreach ($acctdepositoprofitsharing as $key => $val) {
            $export .= "
            <tr>
                <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                <td width=\"12%\"><div style=\"text-align: left;\">".$val['deposito_profit_sharing_due_date']."</div></td>
                <td width=\"12%\"><div style=\"text-align: left;\">".$val['deposito_account_no']."</div></td>
                <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val['deposito_profit_sharing_amount'], 2)."</div></td>
                <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['deposito_account_last_balance'], 2)."</div></td>
                <td width=\"15%\"><div style=\"text-align: right;\">".$val['savings_account_no']."</div></td>
            </tr>";

            $totalnominal 	+= $val['deposito_account_last_balance'];
            $no++;
        }

        $export .= "
            <tr>
                <td colspan =\"4\" style=\"border-top: 1px solid black;\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Jumlah </div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalnominal, 2)."</div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"></div></td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan BO Simpanan Berjangka.pdf';
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

        $acctdepositoprofitsharing = AcctDepositoProfitSharing::select('acct_deposito_profit_sharing.deposito_account_id', 'acct_deposito_account.deposito_account_no', 'acct_deposito_profit_sharing.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_deposito_profit_sharing.member_id', 'core_member.member_name', 'acct_deposito_profit_sharing.deposito_profit_sharing_amount', 'acct_deposito_profit_sharing.deposito_account_last_balance', 'acct_deposito_profit_sharing.deposito_profit_sharing_due_date', 'acct_deposito_profit_sharing.deposito_profit_sharing_date')
        ->join('core_member', 'acct_deposito_profit_sharing.member_id', '=', 'core_member.member_id')
        ->join('acct_savings_account', 'acct_deposito_profit_sharing.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('acct_deposito_account', 'acct_deposito_profit_sharing.deposito_account_id', '=', 'acct_deposito_account.deposito_account_id')
        ->where('acct_deposito_profit_sharing.deposito_profit_sharing_due_date', '>=', $sesi['start_date'])
        ->where('acct_deposito_profit_sharing.deposito_profit_sharing_due_date', '<=', $sesi['end_date'])
        ->orderBy('acct_deposito_account.deposito_account_no', 'ASC')
        ->get();

        if(count($acctdepositoprofitsharing)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan Jasa Simp Bjk")
                                            ->setSubject("")
                                            ->setDescription("Laporan Jasa Simp Bjk")
                                            ->setKeywords("Laporan, Jasa, Simp, Bjk")
                                            ->setCategory("Laporan Jasa Simp Bjk");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan Jasa Simp Bjk");

            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);			
            
            $spreadsheet->getActiveSheet()->mergeCells("B1:H1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR BUNGA SIMP BERJANGKA BULAN INI");
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"Jatuh Tempo");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"No. Simpanan Berjangka");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Nama");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Bagi Hasil");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Saldo");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Transfer");
            
            $no             = 0;
            $totalnominal   = 0;
            $j              = 4;
            foreach($acctdepositoprofitsharing as $key=>$val){
                $no++;
                $spreadsheet->getActiveSheet()->getStyle('B'.$j.':H'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
                $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['deposito_profit_sharing_due_date']);
                $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['deposito_account_no']);
                $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $val['member_name']);
                $spreadsheet->getActiveSheet()->setCellValue('F'.$j, $val['deposito_profit_sharing_amount']);
                $spreadsheet->getActiveSheet()->setCellValue('G'.$j, $val['deposito_account_last_balance']);
                $spreadsheet->getActiveSheet()->setCellValue('H'.$j, $val['savings_account_no']);
    
                $totalnominal 	+= $val['deposito_account_last_balance'];
                $j++;
            }

            $i = $j;

            $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':F'.$i);
            $spreadsheet->getActiveSheet()->getStyle('B'.$i.':H'.$i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $spreadsheet->getActiveSheet()->getStyle('B'.$i.':H'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$i, 'Total');
            $spreadsheet->getActiveSheet()->setCellValue('G'.$i, $totalnominal);

            ob_clean();
            $filename='Laporan Jasa Simp Bjk.xls';
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
