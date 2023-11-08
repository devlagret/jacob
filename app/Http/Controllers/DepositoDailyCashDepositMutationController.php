<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctMutation;
use App\Models\AcctSavingsTransferMutation;
use App\Models\AcctSavingsTransferMutationFrom;
use App\Models\AcctSavingsTransferMutationTo;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DepositoDailyCashDepositMutationController extends Controller
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

        return view('content.DepositoDailyCashMutation.addCashDeposit.index', compact('corebranch'));
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

        $acctdepositomutation    = AcctDepositoAccount::select('*')
        ->join('acct_deposito', 'acct_deposito.deposito_id' ,'=', 'acct_deposito_account.deposito_id')
        ->join('core_member', 'acct_deposito_account.member_id' ,'=', 'core_member.member_id')
        ->where('acct_deposito_account.deposito_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
        ->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
        ->where('acct_deposito_account.data_state', 0);
        if(!empty($branch_id)){
            $acctdepositomutation = $acctdepositomutation->where('acct_deposito_account.branch_id', $branch_id);
        }			
        $acctdepositomutation = $acctdepositomutation->get();

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
                <td><div style=\"text-align: left;font-size:12;\">".$preferencecompany['company_name']."</div></td>			       
            </tr>	
            <tr>
                <td><div style=\"text-align: left;font-size:12;font-weight:bold\">MUTASI PEMBUKAAN SIMPANAN BERJANGKA TGL : &nbsp;&nbsp; ".$sesi['start_date']."&nbsp;&nbsp; S.D &nbsp;&nbsp;".$sesi['end_date']."</div></td>		
            </tr>					
        </table>";
        
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">NO.</div></td>
                <td width=\"11%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">TANGGAL</div></td>
                <td width=\"16%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">NO. REK</div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">NAMA</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">NOMINAL</div></td>
                <td width=\"17%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Saldo</div></td>
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

       

        $no                 = 1;
        $totalnominal       = 0;
        $totalsaldo         = 0;
        foreach ($acctdepositomutation as $key => $val) {
                $export .= "
                <tr>
                    <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                    <td width=\"11%\"><div style=\"text-align: left;\">".$val['deposito_account_date']."</div></td>
                    <td width=\"16%\"><div style=\"text-align: left;\">".$val['deposito_account_no']."</div></td>
                    <td width=\"25%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['deposito_account_amount'], 2)."</div></td>
                    <td width=\"17%\"><div style=\"text-align: right;\">".number_format($val['deposito_account_amount'], 2)."</div></td>
                </tr>";

                $totalnominal 	+= $val['deposito_account_amount'];
                $totalsaldo	+= $val['deposito_account_amount'];
                $no++;

        }

        $grandtotalnominal 	= $totalnominal;
        $grandtotalsaldo	= $totalsaldo;
        $export .= "
            <tr>
                <td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Jumlah </div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($grandtotalnominal, 2)."</div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($grandtotalsaldo, 2)."</div></td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Mutasi Harian Tunai Simpanan.pdf';
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

        $acctdepositomutation    = AcctDepositoAccount::select('*')
        ->join('acct_deposito', 'acct_deposito.deposito_id' ,'=', 'acct_deposito_account.deposito_id')
        ->join('core_member', 'acct_deposito_account.member_id' ,'=', 'core_member.member_id')
        ->where('acct_deposito_account.deposito_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
        ->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
        ->where('acct_deposito_account.data_state', 0);
        if(!empty($branch_id)){
            $acctdepositomutation = $acctdepositomutation->where('acct_deposito_account.branch_id', $branch_id);
        }			
        $acctdepositomutation = $acctdepositomutation->get();


        if(count($acctdepositomutation)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
            ->setLastModifiedBy($preferencecompany['company_name'])
            ->setTitle("Laporan Mutasi Harian Pembukaan Tunai Simpanan Berjangka")
            ->setSubject("")
            ->setDescription("Laporan Mutasi Harian Pembukaan Tunai Simpanan Berjangka")
            ->setKeywords("Laporan, Mutasi, Harian, Pembukaan, Tunai, Simpanan , Berjangka")
            ->setCategory("Laporan Mutasi Harian Pembukaan Tunai Simpanan Berjangka");

            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan MHTS");
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(10);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(25);


    
            $spreadsheet->getActiveSheet()->mergeCells("B1:H1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);

            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('B1', "Laporan Mutasi Harian Pembukaan Tunai Simpanan Berjangka ".date('d M Y'));	
            $sheet->setCellValue('B3', "No");
            $sheet->setCellValue('C3', "Tanggal");
            $sheet->setCellValue('D3', "No. Rek");
            $sheet->setCellValue('E3', "Nama Anggota");
            $sheet->setCellValue('F3', "Sandi");
            $sheet->setCellValue('G3', "Nominal");
            $sheet->setCellValue('H3', "Saldo");

            
            $j  = 4;
            $no = 1;
            $totalnominal = 0;
            $totalsaldo = 0;
            if(count($acctdepositomutation)==0){
                $lastno = 2;
                $lastj = 4;
               }else{
            foreach($acctdepositomutation as $key => $val){
                $sheet = $spreadsheet->getActiveSheet(0);
                $spreadsheet->getActiveSheet()->setTitle("LMHST");
                $spreadsheet->getActiveSheet()->getStyle('B'.$j.':H'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->setCellValue('B'.$j, $no);
                $sheet->setCellValue('B'.$j, $no);
                $sheet->setCellValue('C'.$j, $val['deposito_account_date']);
                $sheet->setCellValue('D'.$j, $val['deposito_account_no']);
                $sheet->setCellValue('E'.$j, $val['member_name']);
                $sheet->setCellValue('F'.$j, 0);
                $sheet->setCellValue('G'.$j, number_format($val['deposito_account_amount'],2));
                $sheet->setCellValue('H'.$j, number_format($val['deposito_account_amount'],2));

                $no++;
                $j++;
                $totalnominal   += $val['deposito_account_amount'];
                $totalsaldo 	+= $val['deposito_account_amount'];
            }

           
            $grandtotalnominal 	= $totalnominal;
            $grandtotalsaldo	= $totalsaldo;
            $m                  = $j;

            $spreadsheet->getActiveSheet()->mergeCells('B'.$m.':F'.$m);
            $spreadsheet->getActiveSheet()->getStyle('B'.$m.':H'.$m)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $spreadsheet->getActiveSheet()->getStyle('B'.$m.':H'.$m)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->setCellValue('B'.$m, 'Total');
            $spreadsheet->getActiveSheet()->setCellValue('G'.$m, number_format($grandtotalnominal,2));
            $spreadsheet->getActiveSheet()->setCellValue('H'.$m, number_format($grandtotalsaldo,2));

        }
            ob_clean();
            $filename='Laporan Mutasi Harian Pembukaan Tunai Simpanan Berjangka.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }else{
            echo "Maaf data yang di eksport tidak ada !";
        }
    }

    public function getMutationCode($mutation_id)
    {
        $mutation_code = AcctMutation::select('mutation_code')
        ->where('mutation_id', $mutation_id)
        ->first();
        return $mutation_code['mutation_code'];
    }
}
