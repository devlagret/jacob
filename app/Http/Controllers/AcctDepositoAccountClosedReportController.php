<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\CoreOffice;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AcctDepositoAccountClosedReportController extends Controller
{
    public function index()
    {
        $branch_id = auth()->user()->branch_id;
        if($branch_id == 0){
            $corebranch         = CoreBranch::where('data_state', 0)
            ->get();
        }else{
            $corebranch = CoreBranch::where('data_state', 0)
            ->where('branch_id', $branch_id)
            ->get();
        }
        $kelompok   = Configuration::KelompokLaporanSimpananBerjangka();

        return view('content.AcctDepositoAccountClosedReport.index', compact('corebranch', 'kelompok'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "start_date"    => $request->start_date,
            "end_date"	    => $request->end_date,
            "kelompok"	    => $request->kelompok,
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
        $branch_id                  = auth()->user()->branch_id;
        $branch_status              = auth()->user()->branch_status;
        $kelompok                   = Configuration::KelompokLaporanSimpananBerjangka();
        $preferencecompany	        = PreferenceCompany::select('logo_koperasi', 'company_name')->first();
        $path                       = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $period 			        = date('mY', strtotime($sesi['start_date']));
        $data_acctdepositoaccount   = array();

        if($branch_status == 1){
            if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
                $branch_id = '';
            } else {
                $branch_id = $sesi['branch_id'];
            }
        }
        
        $acctdeposito = AcctDeposito::select('deposito_id', 'deposito_name')
        ->where('data_state', 0)
        ->get();
        
        if($sesi['kelompok'] == 0){
            $acctdepositoaccount	= AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_status', 'acct_deposito_account.deposito_account_closed_date', 'acct_deposito_account.office_id')
			->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
			->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
			->where('acct_deposito_account.deposito_account_closed_date', '>=' , date('Y-m-d', strtotime($sesi['start_date'])))
			->where('acct_deposito_account.deposito_account_closed_date', '<=' , date('Y-m-d', strtotime($sesi['end_date'])))
			->where('acct_deposito_account.branch_id', $branch_id)
			->where('acct_deposito_account.data_state', 0)
			->where('acct_deposito_account.deposito_account_status', 1)
            ->get();

            foreach ($acctdepositoaccount as $key => $val) {
                $data_acctdepositoaccount[] = array (
                    'deposito_account_no'			=> $val['deposito_account_no'],
                    'member_name'					=> $val['member_name'],
                    'office_id'						=> $val['office_id'],
                    'member_address'				=> $val['member_address'],
                    'deposito_account_amount'		=> $val['deposito_account_amount'],
                    'deposito_account_period'		=> $val['deposito_account_period'],
                    'deposito_account_date'			=> $val['deposito_account_date'],
                    'deposito_account_due_date'		=> $val['deposito_account_due_date'],
                    'deposito_account_closed_date'	=> $val['deposito_account_closed_date'],
                );
            }
        } else {
            foreach ($acctdeposito as $key => $vD) {
                $acctdepositoaccount_deposito = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_status', 'acct_deposito_account.deposito_account_closed_date', 'acct_deposito_account.office_id')
                ->join('acct_deposito', 'acct_deposito_account.deposito_id' ,'=', 'acct_deposito.deposito_id')
                ->join('core_member', 'acct_deposito_account.member_id' ,'=', 'core_member.member_id')
                ->where('acct_deposito_account.deposito_account_closed_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('acct_deposito_account.deposito_account_closed_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                ->where('acct_deposito_account.deposito_id', $vD['deposito_id'])
                ->where('acct_deposito_account.branch_id', $branch_id)
                ->where('acct_deposito_account.data_state', 0)
                ->where('acct_deposito_account.deposito_account_status', 1)
                ->get();

                foreach ($acctdepositoaccount_deposito as $key => $val) {
                    $data_acctdepositoaccount[$vD['deposito_id']][] = array (
                        'deposito_account_no'			=> $val['deposito_account_no'],
                        'member_name'					=> $val['member_name'],
                        'office_id'						=> $val['office_id'],
                        'member_address'				=> $val['member_address'],
                        'deposito_account_amount'		=> $val['deposito_account_amount'],
                        'deposito_account_period'		=> $val['deposito_account_period'],
                        'deposito_account_date'			=> $val['deposito_account_date'],
                        'deposito_account_due_date'		=> $val['deposito_account_due_date'],
                        'deposito_account_closed_date'	=> $val['deposito_account_closed_date'],
                    );
                }
            }
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
                <td><div style=\"text-align: left;font-size:12;font-weight:bold\">MUTASI SIMPANAN NON TUNAI TGL : &nbsp;&nbsp; ".$sesi['start_date']."&nbsp;&nbsp; S.D &nbsp;&nbsp;".$sesi['end_date']."</div></td>		
            </tr>					
        </table>";
        
        if($sesi['kelompok'] == 0){
            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR SIMPANAN BERJANGKA DITUTUP </div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: center; font-size:10px\">Periode ".$sesi['start_date']." S.D. ".$sesi['end_date']."</div></td>
                </tr>
            </table>";
        } else {
            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: center; font-size:14px;font-weight:bold\">DAFTAR SIMPANAN BERJANGKA DITUTUP PER JENIS</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: center; font-size:10px\">Periode ".$sesi['start_date']." S.D. ".$sesi['end_date']."</div></td>
                </tr>
            </table>";
        }

        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"9%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Rek</div></td>
                <td width=\"13%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">AO</div></td>
                <td width=\"18%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Nominal</div></td>
                <td width=\"9%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">JK Waktu</div></td>
                <td width=\"9%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tanggal Mulai</div></td>
                <td width=\"9%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">JT Tempo</div></td>
                <td width=\"9%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">TGL Tutup</div></td>
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $no          = 1;
        $totalglobal = 0;
        if($sesi['kelompok'] == 0){
            foreach ($data_acctdepositoaccount as $key => $val) {
                $office_code = CoreOffice::select('office_code')
                ->where('office_id', $val['office_id'])
                ->first()
                ->office_code;

                $export .= "
                <tr>
                    <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                    <td width=\"9%\"><div style=\"text-align: left;\">".$val['deposito_account_no']."</div></td>
                    <td width=\"13%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                    <td width=\"5%\"><div style=\"text-align: left;\">".$office_code."</div></td>
                    <td width=\"18%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['deposito_account_amount'], 2)."</div></td>
                    <td width=\"9%\"><div style=\"text-align: center;\">".$val['deposito_account_period']."</div></td>
                    <td width=\"9%\"><div style=\"text-align: center;\">".$val['deposito_account_date']."</div></td>
                    <td width=\"9%\"><div style=\"text-align: center;\">".$val['deposito_account_due_date']."</div></td>
                    <td width=\"9%\"><div style=\"text-align: center;\">".$val['deposito_account_closed_date']."</div></td>
                </tr>";

                $totalglobal += $val['deposito_account_amount'];
                $no++;
            }
        } else {
            foreach ($acctdeposito as $kD => $vD) {
                if(!empty($data_acctdepositoaccount[$vD['deposito_id']])){
                    $export .= "
                    <br>
                    <tr>
                        <td colspan =\"6\" width=\"95%\" style=\"border-bottom: 1px solid black;font-weight:bold\"><div style=\"font-size:10\">".$vD['deposito_name']."</div></td>
                    </tr>
                    <br>";

                    $nov            = 1;
                    $totalperjenis  = 0;
                    foreach ($data_acctdepositoaccount[$vD['deposito_id']] as $k => $v) {
                        $office_code = CoreOffice::select('office_code')
                        ->where('office_id', $val['office_id'])
                        ->first()
                        ->office_code;

                        $export .= "
                        <tr>
                            <td width=\"5%\"><div style=\"text-align: left;\">".$nov."</div></td>
                            <td width=\"9%\"><div style=\"text-align: left;\">".$v['deposito_account_no']."</div></td>
                            <td width=\"13%\"><div style=\"text-align: left;\">".$v['member_name']."</div></td>
                            <td width=\"5%\"><div style=\"text-align: left;\">".$office_code."</div></td>
                            <td width=\"18%\"><div style=\"text-align: left;\">".$v['member_address']."</div></td>
                            <td width=\"15%\"><div style=\"text-align: right;\">".number_format($v['deposito_account_amount'], 2)."</div></td>
                            <td width=\"9%\"><div style=\"text-align: center;\">".$v['deposito_account_period']."</div></td>
                            <td width=\"9%\"><div style=\"text-align: center;\">".$v['deposito_account_date']."</div></td>
                            <td width=\"9%\"><div style=\"text-align: center;\">".$v['deposito_account_due_date']."</div></td>
                            <td width=\"9%\"><div style=\"text-align: center;\">".$val['deposito_account_closed_date']."</div></td>
                        </tr>";

                        $totalperjenis += $v['deposito_account_amount'];
                        $nov++;
                    }

                    $export .= "
                    <br>
                    <tr>
                        <td colspan =\"4\"><div style=\"font-size:10;font-style:italic;text-align:right\"></div></td>
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

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Simpanan Berjangka Ditutup.pdf';
        $pdf::Output($filename, 'I');
    }

    public function export($sesi){
        $branch_id                  = auth()->user()->branch_id;
        $branch_status              = auth()->user()->branch_status;
        $preferencecompany	        = PreferenceCompany::select('company_name')->first();
        $spreadsheet                = new Spreadsheet();
        $period 			        = date('mY', strtotime($sesi['start_date']));
        $data_acctdepositoaccount   = array();

        if($branch_status == 1){
            if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
                $branch_id = '';
            } else {
                $branch_id = $sesi['branch_id'];
            }
        }
        
        $acctdeposito = AcctDeposito::select('deposito_id', 'deposito_name')
        ->where('data_state', 0)
        ->get();
        
        if($sesi['kelompok'] == 0){
            $acctdepositoaccount	= AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_status', 'acct_deposito_account.deposito_account_closed_date', 'acct_deposito_account.office_id')
			->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
			->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
			->where('acct_deposito_account.deposito_account_closed_date', '>=' , date('Y-m-d', strtotime($sesi['start_date'])))
			->where('acct_deposito_account.deposito_account_closed_date', '<=' , date('Y-m-d', strtotime($sesi['end_date'])))
			->where('acct_deposito_account.branch_id', $branch_id)
			->where('acct_deposito_account.data_state', 0)
			->where('acct_deposito_account.deposito_account_status', 1)
            ->get();

            foreach ($acctdepositoaccount as $key => $val) {
                $data_acctdepositoaccount[] = array (
                    'deposito_account_no'			=> $val['deposito_account_no'],
                    'member_name'					=> $val['member_name'],
                    'office_id'						=> $val['office_id'],
                    'member_address'				=> $val['member_address'],
                    'deposito_account_amount'		=> $val['deposito_account_amount'],
                    'deposito_account_period'		=> $val['deposito_account_period'],
                    'deposito_account_date'			=> $val['deposito_account_date'],
                    'deposito_account_due_date'		=> $val['deposito_account_due_date'],
                    'deposito_account_closed_date'	=> $val['deposito_account_closed_date'],
                );
            }
        } else {
            foreach ($acctdeposito as $key => $vD) {
                $acctdepositoaccount_deposito = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_status', 'acct_deposito_account.deposito_account_closed_date', 'acct_deposito_account.office_id')
                ->join('acct_deposito', 'acct_deposito_account.deposito_id' ,'=', 'acct_deposito.deposito_id')
                ->join('core_member', 'acct_deposito_account.member_id' ,'=', 'core_member.member_id')
                ->where('acct_deposito_account.deposito_account_closed_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('acct_deposito_account.deposito_account_closed_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                ->where('acct_deposito_account.deposito_id', $vD['deposito_id'])
                ->where('acct_deposito_account.branch_id', $branch_id)
                ->where('acct_deposito_account.data_state', 0)
                ->where('acct_deposito_account.deposito_account_status', 1)
                ->get();

                foreach ($acctdepositoaccount_deposito as $key => $val) {
                    $data_acctdepositoaccount[$vD['deposito_id']][] = array (
                        'deposito_account_no'			=> $val['deposito_account_no'],
                        'member_name'					=> $val['member_name'],
                        'office_id'						=> $val['office_id'],
                        'member_address'				=> $val['member_address'],
                        'deposito_account_amount'		=> $val['deposito_account_amount'],
                        'deposito_account_period'		=> $val['deposito_account_period'],
                        'deposito_account_date'			=> $val['deposito_account_date'],
                        'deposito_account_due_date'		=> $val['deposito_account_due_date'],
                        'deposito_account_closed_date'	=> $val['deposito_account_closed_date'],
                    );
                }
            }
        }

        if(count($data_acctdepositoaccount)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan Simpanan Berjangka Ditutup")
                                            ->setSubject("")
                                            ->setDescription("Laporan Simpanan Berjangka Ditutup")
                                            ->setKeywords("Laporan, Simpanan, Berjangka, Ditutup")
                                            ->setCategory("Laporan Simpanan Berjangka Ditutup");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan Simp Bjk Ditutup");
            
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(40);
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

            if($sesi['kelompok'] == 0){
                $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR SIMPANAN BERJANGKA DITUTUP");
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR SIMPANAN BERJANGKA DITUTUP PER JENIS");
            }
            
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"No. Rek");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"BO");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Alamat");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Nominal");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"JK Waktu");
            $spreadsheet->getActiveSheet()->setCellValue('I3',"Tanggal Mulai");
            $spreadsheet->getActiveSheet()->setCellValue('J3',"JT Tempo");
            $spreadsheet->getActiveSheet()->setCellValue('K3',"Tanggal Tutup");
            
            $no          = 0;
            $totalglobal = 0;
            if($sesi['kelompok'] == 0){
                $j = 4;
                foreach($data_acctdepositoaccount as $key=>$val){
                    $no++;
                    $spreadsheet->setActiveSheetIndex(0);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j.':K'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
                    $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['deposito_account_no']);
                    $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
                    $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $this->AcctDepositoAccountClosedReport_model->getOfficeCode($val['office_id']));
                    $spreadsheet->getActiveSheet()->setCellValue('F'.$j, $val['member_address']);
                    $spreadsheet->getActiveSheet()->setCellValue('G'.$j, number_format($val['deposito_account_amount'],2));
                    $spreadsheet->getActiveSheet()->setCellValue('H'.$j, $val['deposito_account_period']);
                    $spreadsheet->getActiveSheet()->setCellValue('I'.$j, tgltoview($val['deposito_account_date']));
                    $spreadsheet->getActiveSheet()->setCellValue('J'.$j, tgltoview($val['deposito_account_due_date']));
                    $spreadsheet->getActiveSheet()->setCellValue('K'.$j, tgltoview($val['deposito_account_closed_date']));
        
                    $totalglobal += $val['deposito_account_amount'];
                    $j++;
                }

                $i = $j;
            } else {
                $i              = 4;
                $totalperjenis  = 0;
                foreach ($acctdeposito as $k => $v) {
                    if(!empty($data_acctdepositoaccount[$v['deposito_id']])){
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getFont()->setBold(true)->setSize(14);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i.':K'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':K'.$i);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$i, $v['deposito_name']);

                        $nov = 0;
                        $j   = $i+1;
                        foreach($data_acctdepositoaccount[$v['deposito_id']] as $key=>$val){
                            $nov++;
                            $spreadsheet->setActiveSheetIndex(0);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$j.':K'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            
                            $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $nov);
                            $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['deposito_account_no']);
                            $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
                            $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $this->AcctDepositoAccountClosedReport_model->getOfficeCode($val['office_id']));
                            $spreadsheet->getActiveSheet()->setCellValue('F'.$j, $val['member_address']);
                            $spreadsheet->getActiveSheet()->setCellValue('G'.$j, number_format($val['deposito_account_amount'],2));
                            $spreadsheet->getActiveSheet()->setCellValue('H'.$j, $val['deposito_account_period']);
                            $spreadsheet->getActiveSheet()->setCellValue('I'.$j, tgltoview($val['deposito_account_date']));
                            $spreadsheet->getActiveSheet()->setCellValue('J'.$j, tgltoview($val['deposito_account_due_date']));
                            $spreadsheet->getActiveSheet()->setCellValue('K'.$j, tgltoview($val['deposito_account_closed_date']));
                
                            $totalperjenis += $val['deposito_account_amount'];
                            $j++;
                        }

                        $m = $j;
                        
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$m.':F'.$m);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$m.':K'.$m)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        $spreadsheet->getActiveSheet()->getStyle('B'.$m.':K'.$m)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                        $spreadsheet->getActiveSheet()->setCellValue('B'.$m, 'SubTotal');
                        $spreadsheet->getActiveSheet()->setCellValue('G'.$m, number_format($totalperjenis,2));

                        $i = $m + 1;
                    }
                }
                $totalglobal += $totalperjenis;
            }
            $n = $i;
            
            $spreadsheet->getActiveSheet()->mergeCells('B'.$n.':F'.$n);
            $spreadsheet->getActiveSheet()->getStyle('B'.$n.':K'.$n)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $spreadsheet->getActiveSheet()->getStyle('B'.$n.':K'.$n)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->setCellValue('B'.$n, 'Total');
            $spreadsheet->getActiveSheet()->setCellValue('G'.$n, number_format($totalglobal,2));
            
            ob_clean();
            $filename='Laporan Simpanan Berjangka Ditutup.xls';
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
