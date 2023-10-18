<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NominativeDepositoReportController extends Controller
{
    public function index()
    {
        $corebranch = CoreBranch::where('data_state', 0)->get();
        $kelompok   = Configuration::KelompokLaporanSimpananBerjangka();

        return view('content.NominativeDeposito.index', compact('corebranch', 'kelompok'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "start_date"	=> $request->start_date,
            "kelompok"	    => $request->kelompok,
            "branch_id"		=> $request->branch_id, 
            "view"			=> $request->view,
        );

        if($sesi['view'] == 'pdf'){
            $this->processPrinting($sesi);
        } else {
            $this->export($sesi);
        }
    }

    public function processPrinting($sesi){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('logo_koperasi')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        if($branch_status == 1){
            if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
                $branch_id = '';
            } else {
                $branch_id = $sesi['branch_id'];
            }
        }
        
        $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_status', 'acct_deposito.deposito_interest_rate')
        ->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
        ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
        ->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['start_date'])))
        ->where('acct_deposito_account.deposito_account_status', 0)
        ->where('acct_deposito_account.data_state', 0);
        if(!empty($branch_id)){
            $acctdepositoaccount = $acctdepositoaccount->where('acct_deposito_account.branch_id', $branch_id);
        }
        $acctdepositoaccount = $acctdepositoaccount->orderBy('acct_deposito_account.deposito_account_id', 'ASC')
        ->orderBy('acct_deposito_account.deposito_id', 'ASC')
        ->orderBy('acct_deposito_account.member_id', 'ASC')
        ->orderBy('core_member.member_name', 'ASC')
        ->orderBy('core_member.member_address', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_date', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_due_date', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_amount', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_no', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_period', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_status', 'ASC')
        ->get();

        $acctdeposito = AcctDeposito::select('deposito_id', 'deposito_name')
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

        $export = "";
        
        if($sesi['kelompok'] == 0){
            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR NOMINATIF SIMPANAN BERJANGKA</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: center; font-size:10px\">Periode ".$sesi['start_date']."</div></td>
                </tr>
            </table>";
        } else {
            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR NOMINATIF SIMPANAN BERJANGKA PER JENIS</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: center; font-size:10px\">Periode ".$sesi['start_date']."</div></td>
                </tr>
            </table>";
        }

        $export .= "
        <br>
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Rek</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">Bunga</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Nominal</div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">JK Waktu</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tanggal Mulai</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">JT Tempo</div></td>
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $no             = 1;
        $totalglobal    = 0;

        if($sesi['kelompok'] == 0){
            foreach ($acctdepositoaccount as $key => $val) {
                $depositointerestrate = $val['deposito_interest_rate']/12;
                $export .= "
                    <tr>
                        <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                        <td width=\"10%\"><div style=\"text-align: left;\">".$val['deposito_account_no']."</div></td>
                        <td width=\"15%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                        <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                        <td width=\"5%\"><div style=\"text-align: left;\">".$depositointerestrate."%</div></td>
                        <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['deposito_account_amount'], 2)."</div></td>
                        <td width=\"10%\"><div style=\"text-align: center;\">".$val['deposito_account_period']."</div></td>
                        <td width=\"10%\"><div style=\"text-align: center;\">".$val['deposito_account_date']."</div></td>
                        <td width=\"10%\"><div style=\"text-align: center;\">".$val['deposito_account_due_date']."</div></td>
                    </tr>
                ";
                $totalglobal += $val['deposito_account_amount'];
                $no++;
            }
        } else {
            foreach ($acctdeposito as $kSavings => $vSavings) {			
                $acctdepositoaccount_deposito = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_status', 'acct_deposito.deposito_interest_rate')
                ->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
                ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
                ->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('acct_deposito_account.deposito_id', $vSavings['deposito_id'])
                ->where('acct_deposito_account.deposito_account_status', 0)
                ->where('acct_deposito_account.data_state', 0)
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
                    $export .= "
                        <br>
                        <tr>
                            <td colspan =\"6\" width=\"95%\" style=\"border-bottom: 1px solid black;font-weight:bold\"><div style=\"font-size:10\">".$vSavings['deposito_name']."</div></td>
                        </tr>
                        <br>
                    ";

                    $nov            = 1;
                    $totalperjenis  = 0;

                    foreach ($acctdepositoaccount_deposito as $k => $v) {
                        $depositointerestrate1 			= $v['deposito_interest_rate']/12;
                        $depositointerestrate_total 	= round($depositointerestrate1,2);

                        $export .= "
                            <tr>
                                <td width=\"5%\"><div style=\"text-align: left;\">".$nov."</div></td>
                                <td width=\"10%\"><div style=\"text-align: left;\">".$v['deposito_account_no']."</div></td>
                                <td width=\"15%\"><div style=\"text-align: left;\">".$v['member_name']."</div></td>
                                <td width=\"20%\"><div style=\"text-align: left;\">".$v['member_address']."</div></td>
                                <td width=\"5%\"><div style=\"text-align: left;\">".$depositointerestrate_total."%</div></td>
                                <td width=\"15%\"><div style=\"text-align: right;\">".number_format($v['deposito_account_amount'], 2)."</div></td>
                                <td width=\"10%\"><div style=\"text-align: center;\">".$v['deposito_account_period']."</div></td>
                                <td width=\"10%\"><div style=\"text-align: center;\">".$v['deposito_account_date']."</div></td>
                                <td width=\"10%\"><div style=\"text-align: center;\">".$v['deposito_account_due_date']."</div></td>
                            </tr>

                        ";

                        $totalperjenis += $v['deposito_account_amount'];
                        $nov++;
                    }

                    $export .= "
                    <br>
                    <tr>
                        <td colspan =\"3\"><div style=\"font-size:10;font-style:italic;text-align:right\"></div></td>
                        <td><div style=\"font-size:10;font-weight:bold;text-align:center\">Subtotal</div></td>
                        <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalperjenis, 2)."</div></td>
                    </tr>
                    <br>";

                    $totalglobal += $totalperjenis;
                }
            }
        }

        $export .= "
            <tr>
                <td colspan =\"4\"><div style=\"font-size:10;font-style:italic;text-align:left\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                <td><div style=\"font-size:10;font-weight:bold;text-align:center\">Total</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalglobal, 2)."</div></td>
            </tr>
        </table>";


        // //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Nominatif Simpanan Berjangka.pdf';
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
        
        $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_status', 'acct_deposito.deposito_interest_rate')
        ->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
        ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
        ->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['start_date'])))
        ->where('acct_deposito_account.deposito_account_status', 0)
        ->where('acct_deposito_account.data_state', 0);
        if(!empty($branch_id)){
            $acctdepositoaccount = $acctdepositoaccount->where('acct_deposito_account.branch_id', $branch_id);
        }
        $acctdepositoaccount = $acctdepositoaccount->orderBy('acct_deposito_account.deposito_account_id', 'ASC')
        ->orderBy('acct_deposito_account.deposito_id', 'ASC')
        ->orderBy('acct_deposito_account.member_id', 'ASC')
        ->orderBy('core_member.member_name', 'ASC')
        ->orderBy('core_member.member_address', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_date', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_due_date', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_amount', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_no', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_period', 'ASC')
        ->orderBy('acct_deposito_account.deposito_account_status', 'ASC')
        ->get();

        $acctdeposito = AcctDeposito::select('deposito_id', 'deposito_name')
        ->where('data_state', 0)
        ->get();

        if(count($acctdepositoaccount)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan Nominatif Simp Bjk")
                                            ->setSubject("")
                                            ->setDescription("Laporan Nominatif Simp Bjk")
                                            ->setKeywords("Laporan, Nominatif, Simp, Bjk")
                                            ->setCategory("Laporan Nominatif Simp Bjk");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan Nominatif Simp Bjk");

            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);		
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);		

            $spreadsheet->getActiveSheet()->mergeCells("B1:I1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:I3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:I3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:I3')->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR NOMINATIF SIMPANAN BERJANGKA");
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"No. Rek");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Alamat");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Nominal");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Jangka Waktu");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Tanggal Mulai");
            $spreadsheet->getActiveSheet()->setCellValue('I3',"Tanggal Jatuh Tempo");

            $row                = 4;
            $no                 = 0;
            $totalbasil         = 0;
            $totalsaldo         = 0;
            $subtotalnominal    = 0;
            $grandtotal         = 0;

            if($sesi['kelompok'] == 0){
                foreach($acctdepositoaccount as $key=>$val){
                    $no++;
                    $spreadsheet->setActiveSheetIndex(0);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$row.':I'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('C'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('F'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('G'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('H'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('I'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    
                    $spreadsheet->getActiveSheet()->setCellValue('B'.$row, $no);
                    $spreadsheet->getActiveSheet()->setCellValue('C'.$row, $val['deposito_account_no']);
                    $spreadsheet->getActiveSheet()->setCellValue('D'.$row, $val['member_name']);
                    $spreadsheet->getActiveSheet()->setCellValue('E'.$row, $val['member_address']);
                    $spreadsheet->getActiveSheet()->setCellValue('F'.$row,number_format($val['deposito_account_amount'],2));
                    $spreadsheet->getActiveSheet()->setCellValue('G'.$row, $val['deposito_account_period']);
                    $spreadsheet->getActiveSheet()->setCellValue('H'.$row, $val['deposito_account_date']);
                    $spreadsheet->getActiveSheet()->setCellValue('I'.$row, $val['deposito_account_due_date']);
                            
                    $grandtotal += $val['deposito_account_amount'];	
                    $row++;
                }
            } else {
                $i = 4;
                foreach ($acctdeposito as $k => $v) {
                    $acctdepositoaccount_deposito = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_status', 'acct_deposito.deposito_interest_rate')
                    ->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
                    ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
                    ->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['start_date'])))
                    ->where('acct_deposito_account.deposito_id', $v['deposito_id'])
                    ->where('acct_deposito_account.deposito_account_status', 0)
                    ->where('acct_deposito_account.data_state', 0)
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
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getFont()->setBold(true)->setSize(14);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i.':I'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':I'.$i);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$i, $v['deposito_name']);

                        $nov                = 0;
                        $row                = $i+1;
                        $subtotalnominal    = 0;

                        foreach($acctdepositoaccount_deposito as $key=>$val){
                            $nov++;
                            $spreadsheet->setActiveSheetIndex(0);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$row.':I'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('C'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('D'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('E'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('F'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('G'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('H'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('I'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            
                            $spreadsheet->getActiveSheet()->setCellValue('B'.$row, $nov);
                            $spreadsheet->getActiveSheet()->setCellValue('C'.$row, $val['deposito_account_no']);
                            $spreadsheet->getActiveSheet()->setCellValue('D'.$row, $val['member_name']);
                            $spreadsheet->getActiveSheet()->setCellValue('E'.$row, $val['member_address']);
                            $spreadsheet->getActiveSheet()->setCellValue('F'.$row, number_format($val['deposito_account_amount'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('G'.$row, $val['deposito_account_period']);
                            $spreadsheet->getActiveSheet()->setCellValue('H'.$row, $val['deposito_account_date']);
                            $spreadsheet->getActiveSheet()->setCellValue('I'.$row, $val['deposito_account_due_date']);

                            $subtotalnominal += $val['deposito_account_amount'];
                            $row++;
                        }
                    }
                    $m = $row;
                    $i = $m+1;
                }
                $grandtotal += $subtotalnominal;
            }

            $spreadsheet->getActiveSheet()->getStyle('B'.$row.':I'.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $spreadsheet->getActiveSheet()->getStyle('F'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('B'.$row.':I'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->mergeCells('B'.$row.':E'.$row);
            $spreadsheet->getActiveSheet()->setCellValue('B'.$row, 'Total');
            $spreadsheet->getActiveSheet()->setCellValue('F'.$row, number_format($grandtotal,2));

            ob_clean();
            $filename='Laporan Nominatif Simpanan Berjangka.xls';
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
