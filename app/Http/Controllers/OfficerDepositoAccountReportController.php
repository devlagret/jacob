<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\CoreOffice;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OfficerDepositoAccountReportController extends Controller
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

        return view('content.OfficerDepositoAccountReport.index', compact('corebranch', 'coreoffice'));
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

        $acctdeposito   = AcctDeposito::select('deposito_id', 'deposito_name')
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

        $pdf::AddPage('P');

        $pdf::SetFont('helvetica', '', 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $export = "";
        
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
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">DAFTAR NASABAH BERJANGKA : ".$office_name."</div></td>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">Mulai Tgl. ".$sesi['start_date']." S.D ".$sesi['end_date']."</div></td>			       
                </tr>						
            </table>";

            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td width=\"3%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Rek</div></td>
                    <td width=\"18%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                    <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                    <td width=\"17%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nominal</div></td>
                    <td width=\"7%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">JK Waktu</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">TGL Mulai</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">JT Tempo</div></td>
                </tr>				
            </table>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

            $grandtotalsaldo = 0;
            foreach ($acctdeposito as $kD => $vD) {
                $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.office_id')
                ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
                ->where('acct_deposito_account.deposito_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))		
                ->where('acct_deposito_account.deposito_id', $vD['deposito_id'])	
                ->where('acct_deposito_account.data_state', 0);
                if(!empty($branch_id)){
                    $acctdepositoaccount = $acctdepositoaccount->where('acct_deposito_account.branch_id', $branch_id);
                }	
                if(!empty($sesi['office_id'])){
                    $acctdepositoaccount = $acctdepositoaccount->where('acct_deposito_account.office_id', $sesi['office_id']);
                }
                $acctdepositoaccount = $acctdepositoaccount->orderBy('acct_deposito_account.deposito_account_no', 'ASC')
                ->get();

                if(!empty($acctdepositoaccount)){
                    $export .= "
                    <br>
                    <tr>
                        <td colspan =\"6\" style=\"border-bottom: 1px solid black;\"><div style=\"font-size:10\">".$vD['deposito_name']."</div></td>
                    </tr>";

                    $no         = 1;
                    $totalsaldo = 0;
                    foreach ($acctdepositoaccount as $key => $val) {				
                        $export .= "
                        <tr>
                            <td width=\"3%\"><div style=\"text-align: left;\">".$no."</div></td>
                            <td width=\"10%\"><div style=\"text-align: left;\">".$val['deposito_account_no']."</div></td>
                            <td width=\"18%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                            <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                            <td width=\"17%\"><div style=\"text-align: right;\">".number_format($val['deposito_account_amount'], 2)."</div></td>
                            <td width=\"7%\"><div style=\"text-align: center;\">".$val['deposito_account_period']."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".$val['deposito_account_date']."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".$val['deposito_account_due_date']."</div></td>
                        </tr>";

                        $totalsaldo += $val['deposito_account_amount'];
                        $no++;
                    }

                    $export .= "	
                    <tr>
                        <td colspan =\"3\" style=\"border-top: 1px solid black;\"></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Subtotal </div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalsaldo, 2)."</div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                    </tr>";

                    $grandtotalsaldo += $totalsaldo;
                }
            }

            $export .= "	
                <br>
                <tr>
                    <td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:9;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Total </div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalsaldo, 2)."</div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                </tr>						
            </table>";
        } else {
            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">DAFTAR NASABAH BERJANGKA</div></td>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">Mulai Tgl. ".$sesi['start_date']." S.D ".$sesi['end_date']."</div></td>			       
                </tr>						
            </table>";

            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td width=\"3%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Rek</div></td>
                    <td width=\"18%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">BO</div></td>
                    <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                    <td width=\"17%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nominal</div></td>
                    <td width=\"7%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">JK Waktu</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">TGL Mulai</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">JT Tempo</div></td>
                </tr>				
            </table>";

            $no              = 1;
            $grandtotalsaldo = 0;
            $export .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
            foreach ($acctdeposito as $kD => $vD) {
                $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.office_id')
                ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
                ->where('acct_deposito_account.deposito_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))	
                ->where('acct_deposito_account.deposito_id', $vD['deposito_id'])	
                ->where('acct_deposito_account.data_state', 0);
                if(!empty($branch_id)){
                    $acctdepositoaccount = $acctdepositoaccount->where('acct_deposito_account.branch_id', $branch_id);
                }	
                if(!empty($sesi['office_id'])){
                    $acctdepositoaccount = $acctdepositoaccount->where('acct_deposito_account.office_id', $sesi['office_id']);
                }
                $acctdepositoaccount = $acctdepositoaccount->orderBy('acct_deposito_account.deposito_account_no', 'ASC')
                ->get();

                if(!empty($acctdepositoaccount)){
                    $export .= "
                    <br>
                    <tr>
                        <td colspan =\"6\" style=\"border-bottom: 1px solid black;\"><div style=\"font-size:10\">".$vD['deposito_name']."</div></td>
                    </tr>";

                    $no             = 1;
                    $totalsaldo     = 0;	
                    $office_code    = '';
                    if($sesi['office_id']){
                        $office_code = CoreOffice::select('office_code')
                        ->where('office_id', $sesi['office_id'])
                        ->first()
                        ->office_code;
                    }

                    foreach ($acctdepositoaccount as $key => $val) {	
                        $export .= "
                        <tr>
                            <td width=\"3%\"><div style=\"text-align: left;\">".$no."</div></td>
                            <td width=\"10%\"><div style=\"text-align: left;\">".$val['deposito_account_no']."</div></td>
                            <td width=\"18%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                            <td width=\"5%\"><div style=\"text-align: left;\">".$office_code."</div></td>
                            <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                            <td width=\"17%\"><div style=\"text-align: right;\">".number_format($val['deposito_account_amount'], 2)."</div></td>
                            <td width=\"7%\"><div style=\"text-align: center;\">".$val['deposito_account_period']."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".$val['deposito_account_date']."</div></td>
                            <td width=\"10%\"><div style=\"text-align: right;\">".$val['deposito_account_due_date']."</div></td>
                        </tr>";

                        $totalsaldo += $val['deposito_account_amount'];
                        $no++;
                    }

                    $export .= "	
                    <tr>
                        <td colspan =\"4\" style=\"border-top: 1px solid black;\"></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Subtotal </div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalsaldo, 2)."</div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                    </tr>";

                    $grandtotalsaldo += $totalsaldo;
                }
            }

            $export .= "
                <br>	
                <tr>
                    <td colspan =\"4\" style=\"border-top: 1px solid black;\"><div style=\"font-size:9;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Total </div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalsaldo, 2)."</div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                </tr>						
            </table>";
        }

        // //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
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

        $acctdeposito   = AcctDeposito::select('deposito_id', 'deposito_name')
        ->where('data_state', 0)
        ->get();

        if(count($acctdeposito)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan BO Simp Bjk")
                                            ->setSubject("")
                                            ->setDescription("Laporan BO Simp Bjk")
                                            ->setKeywords("Laporan, BO, Simp, Bjk")
                                            ->setCategory("Laporan BO Simp Bjk");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan BO Simp Bjk");

            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);		
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);		

            $spreadsheet->getActiveSheet()->mergeCells("B1:I1");
            $spreadsheet->getActiveSheet()->mergeCells("B2:I2");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B2')->getFont()->setSize(11);
            $spreadsheet->getActiveSheet()->getStyle('B3:I3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:I3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:I3')->getFont()->setBold(true);
            if($sesi['office_id'] == 0){
                $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR SIMPANAN BERJANGKA");
            } else {
                $office_name = CoreOffice::select('office_name')
                ->where('office_id', $sesi['office_id'])
                ->first()
                ->office_name;

                $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR SIMPANAN BERJANGKA ".$office_name);
            }
            $spreadsheet->getActiveSheet()->setCellValue('B2',"Periode : ".$sesi['start_date']." S.D ".$sesi['end_date']);
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"No. Rek");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Alamat");				
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Nominal");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Jangka Waktu");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Tanggal Mulai");
            $spreadsheet->getActiveSheet()->setCellValue('I3',"Jatuh Tempo");

            $no             = 0;
            $totalnominal   = 0;
            if(empty($sesi['office_id'])){
                $j = 4;
                foreach ($acctdeposito as $k => $v) {
                    $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.office_id')
                    ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
                    ->where('acct_deposito_account.deposito_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                    ->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))	
                    ->where('acct_deposito_account.deposito_id', $v['deposito_id'])	
                    ->where('acct_deposito_account.data_state', 0);
                    if(!empty($branch_id)){
                        $acctdepositoaccount = $acctdepositoaccount->where('acct_deposito_account.branch_id', $branch_id);
                    }	
                    if(!empty($sesi['office_id'])){
                        $acctdepositoaccount = $acctdepositoaccount->where('acct_deposito_account.office_id', $sesi['office_id']);
                    }
                    $acctdepositoaccount = $acctdepositoaccount->orderBy('acct_deposito_account.deposito_account_no', 'ASC')
                    ->get();

                    foreach($acctdepositoaccount as $key=>$val){
                        $no++;
                        $spreadsheet->setActiveSheetIndex(0);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$j.':I'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                        $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['deposito_account_no']);
                        $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
                        $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $val['member_address']);
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$j, number_format($val['deposito_account_amount'],2));
                        $spreadsheet->getActiveSheet()->setCellValue('G'.$j,$val['deposito_account_period']);
                        $spreadsheet->getActiveSheet()->setCellValue('H'.$j,$val['deposito_account_date']);
                        $spreadsheet->getActiveSheet()->setCellValue('I'.$j, $val['deposito_account_due_date']);

                        $totalnominal += $val['deposito_account_amount'];
                        $j++;
                    }
                }
            } else {
                $i = 4;
                foreach ($acctdeposito as $k => $v) {
                    $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.office_id')
                    ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
                    ->where('acct_deposito_account.deposito_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                    ->where('acct_deposito_account.deposito_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))	
                    ->where('acct_deposito_account.deposito_id', $v['deposito_id'])	
                    ->where('acct_deposito_account.data_state', 0);
                    if(!empty($branch_id)){
                        $acctdepositoaccount = $acctdepositoaccount->where('acct_deposito_account.branch_id', $branch_id);
                    }	
                    if(!empty($sesi['office_id'])){
                        $acctdepositoaccount = $acctdepositoaccount->where('acct_deposito_account.office_id', $sesi['office_id']);
                    }
                    $acctdepositoaccount = $acctdepositoaccount->orderBy('acct_deposito_account.deposito_account_no', 'ASC')
                    ->get();

                    if(!empty($acctdepositoaccount)){
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getFont()->setBold(true)->setSize(14);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i.':I'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':I'.$i);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$i, $v['deposito_name']);

                        $nov             = 0;
                        $subtotalnominal = 0;
                        $j               = $i+1;
                        foreach($acctdepositoaccount as $key=>$val){
                            $nov++;
                            
                            $spreadsheet->setActiveSheetIndex(0);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$j.':I'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $nov);
                            $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['deposito_account_no']);
                            $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
                            $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $val['member_address']);
                            $spreadsheet->getActiveSheet()->setCellValue('F'.$j, number_format($val['deposito_account_amount'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('G'.$j, $val['deposito_account_period']);
                            $spreadsheet->getActiveSheet()->setCellValue('H'.$j, $val['deposito_account_date']);
                            $spreadsheet->getActiveSheet()->setCellValue('I'.$j, $val['deposito_account_due_date']);

                            $subtotalnominal += $val['deposito_account_amount'];
                            $j++;
                        }

                        $m = $j;

                        $spreadsheet->getActiveSheet()->getStyle('B'.$m.':I'.$m)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        $spreadsheet->getActiveSheet()->getStyle('B'.$m.':I'.$m)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$m.':E'.$m);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$m, 'SubTotal');
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$m, number_format($subtotalnominal,2));

                        $i              = $m + 1;
                        $totalnominal   += $subtotalnominal;
                    }
                }

                $n = $i;

                $spreadsheet->getActiveSheet()->mergeCells('B'.$n.':E'.$n);
                $spreadsheet->getActiveSheet()->getStyle('B'.$n.':I'.$n)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B'.$n.':I'.$n)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                $spreadsheet->getActiveSheet()->setCellValue('B'.$n, 'Total');
                $spreadsheet->getActiveSheet()->setCellValue('F'.$n, number_format($totalnominal,2));
            }

            ob_clean();
            $filename='Laporan BO Simp Bjk.xls';
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
