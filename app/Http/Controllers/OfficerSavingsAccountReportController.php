<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\CoreOffice;
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

class OfficerSavingsAccountReportController extends Controller
{
    public function index()
    {
        $corebranch = CoreBranch::where('data_state', 0)->get();
        $coreoffice = CoreOffice::where('data_state', 0)->get();

        return view('content.OfficerSavingsAccountReport.index', compact('corebranch', 'coreoffice'));
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

        $acctsavings        = AcctSavings::select('savings_id', 'savings_name')
        ->where('data_state', 0)
        ->where('savings_status', 0)
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
        
        if(!empty($sesi['office_id'])){
            $office = CoreOffice::select('office_name')
            ->where('office_id', $sesi['office_id'])
            ->first();
            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td colspan=\"2\"><div style=\"text-align: left;font-size:10; font-weight:bold\">".$preferencecompany['company_name']."</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">DAFTAR NASABAH SIMPANAN : ".$office['office_name']."</div></td>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">Mulai Tgl. ".$sesi['start_date']." S.D ".$sesi['end_date']."</div></td>			       
                </tr>						
            </table>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                    <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Rek</div></td>
                    <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                    <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                    <td width=\"17%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bagi Hasil</div></td>
                    <td width=\"17%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Saldo</div></td>
                    
                </tr>				
            </table>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

            $grandtotalbasil = 0;
            $grandtotalsaldo = 0;
            foreach ($acctsavings as $kSavings => $vSavings) {
                $acctsavingsaccount = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.office_id', 'acct_savings_account.branch_id')
                ->join('core_member', 'acct_savings_account.member_id', '=' ,'core_member.member_id')
                ->join('acct_savings', 'acct_savings_account.savings_id', '=' ,'acct_savings.savings_id')
                ->where('acct_savings_account.savings_account_date' , '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('acct_savings_account.savings_account_date' , '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                ->where('acct_savings_account.savings_id', $vSavings['savings_id'])
                ->where('acct_savings.savings_status', 0)
                ->where('acct_savings_account.data_state', 0);
                if(!empty($sesi['office_id'])){
                    $acctsavingsaccount = $acctsavingsaccount->where('acct_savings_account.office_id', $sesi['office_id']);
                }
                if(!empty($branch_id)){
                    $acctsavingsaccount = $acctsavingsaccount->where('acct_savings_account.branch_id', $branch_id);
                }
                $acctsavingsaccount     = $acctsavingsaccount->orderBy('acct_savings_account.savings_account_no', 'ASC')
                ->get();

                if(!empty($acctsavingsaccount)){
                    $export .= "
                        <br>
                        <tr>
                            <td colspan =\"6\" style=\"border-bottom: 1px solid black;\"><div style=\"font-size:10\">".$vSavings['savings_name']."</div></td>
                        </tr>
                    ";

                    $no         = 1;
                    $totalbasil = 0;
                    $totalsaldo = 0;
                    foreach ($acctsavingsaccount as $key => $val) {
                        $savings_profit_sharing = AcctSavingsProfitSharing::where('savings_account_id', $val['savings_account_id'])
                        ->where('branch_id', $sesi['branch_id'])
                        ->where('savings_profit_sharing_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                        ->where('savings_profit_sharing_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                        ->sum('savings_profit_sharing_amount');
                        
                        $export .= "
                            <tr>
                                <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                                <td width=\"12%\"><div style=\"text-align: left;\">".$val['savings_account_no']."</div></td>
                                <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                                <td width=\"25%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                                <td width=\"17%\"><div style=\"text-align: right;\">".number_format($savings_profit_sharing, 2)."</div></td>
                                <td width=\"17%\"><div style=\"text-align: right;\">".number_format($val['savings_account_last_balance'], 2)."</div></td>
                            </tr>
                        ";
                        $no++;

                        $totalbasil += $savings_profit_sharing;
                        $totalsaldo += $val['savings_account_last_balance'];
                    }

                    $export .= "	
                    <tr>
                        <td colspan =\"3\" style=\"border-top: 1px solid black;\"></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Subtotal </div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalbasil, 2)."</div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalsaldo, 2)."</div></td>
                    </tr>";

                    $grandtotalbasil += $totalbasil;
                    $grandtotalsaldo += $totalsaldo;
                }
            }

            $export .= "	
            <br>
                <tr>
                    <td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:9;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Total </div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalbasil, 2)."</div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalsaldo, 2)."</div></td>
                </tr>						
            </table>";
        } else {
            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td colspan=\"2\"><div style=\"text-align: left;font-size:10; font-weight:bold\">".$preferencecompany['company_name']."</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">DAFTAR NASABAH SIMPANAN</div></td>
                    <td><div style=\"text-align: left;font-size:10; font-weight:bold\">Mulai Tgl. ".$sesi['start_date']." S.D ".$sesi['end_date']."</div></td>
                </tr>						
            </table>";

            $export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                    <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Rek</div></td>
                    <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                    <td width=\"8%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">BO</div></td>
                    <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                    <td width=\"17%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Bagi Hasil</div></td>
                    <td width=\"17%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Saldo</div></td>
                </tr>				
            </table>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

            $no              = 1;
            $grandtotalbasil = 0;
            $grandtotalsaldo = 0;
            foreach ($acctsavings as $kSavings => $vSavings) {
                $acctsavingsaccount = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.office_id', 'acct_savings_account.branch_id')
                ->join('core_member', 'acct_savings_account.member_id', '=' ,'core_member.member_id')
                ->join('acct_savings', 'acct_savings_account.savings_id', '=' ,'acct_savings.savings_id')
                ->where('acct_savings_account.savings_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('acct_savings_account.savings_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))			
                ->where('acct_savings_account.savings_id', $vSavings['savings_id'])
                ->where('acct_savings.savings_status', 0)
                ->where('acct_savings_account.data_state', 0);
                if(!empty($sesi['office_id'])){
                    $acctsavingsaccount = $acctsavingsaccount->where('acct_savings_account.office_id', $sesi['office_id']);
                }
                if(!empty($branch_id)){
                    $acctsavingsaccount = $acctsavingsaccount->where('acct_savings_account.branch_id', $branch_id);
                }
                $acctsavingsaccount = $acctsavingsaccount->orderBy('acct_savings_account.savings_account_no', 'ASC')
                ->get();

                if(!empty($acctsavingsaccount)){
                    $export .= "
                    <br>
                    <tr>
                        <td colspan =\"6\" style=\"border-bottom: 1px solid black;\"><div style=\"font-size:10\">".$vSavings['savings_name']."</div></td>
                    </tr>";

                    $no         = 1;
                    $totalbasil = 0;
                    $totalsaldo = 0;
                    foreach ($acctsavingsaccount as $key => $val) {
                        $savings_profit_sharing = AcctSavingsProfitSharing::where('savings_account_id', $val['savings_account_id'])
                        ->where('branch_id', $branch_id)
                        ->where('savings_profit_sharing_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                        ->where('savings_profit_sharing_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                        ->sum('savings_profit_sharing_amount');

                        $office_code = CoreOffice::select('office_code')
                        ->where('office_id', $val['office_id'])
                        ->first()
                        ->office_code;
                        
                        $export .= "
                            <tr>
                                <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                                <td width=\"12%\"><div style=\"text-align: left;\">".$val['savings_account_no']."</div></td>
                                <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                                <td width=\"8%\"><div style=\"text-align: left;\">".$office_code."</div></td>
                                <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                                <td width=\"17%\"><div style=\"text-align: right;\">".number_format($savings_profit_sharing, 2)."</div></td>
                                <td width=\"17%\"><div style=\"text-align: right;\">".number_format($val['savings_account_last_balance'], 2)."</div></td>
                            </tr>
                        ";
                        $no++;

                        $totalbasil += $savings_profit_sharing;
                        $totalsaldo += $val['savings_account_last_balance'];
                    }

                    $export .= "	
                    <tr>
                        <td colspan =\"4\" style=\"border-top: 1px solid black;\"></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Subtotal </div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalbasil, 2)."</div></td>
                        <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalsaldo, 2)."</div></td>
                    </tr>";

                    $grandtotalbasil += $totalbasil;
                    $grandtotalsaldo += $totalsaldo;
                }
            }

            $export .= "	
                <tr>
                    <td colspan =\"4\" style=\"border-top: 1px solid black;\"><div style=\"font-size:9;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;font-weight:bold;text-align:center\">Total </div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalbasil, 2)."</div></td>
                    <td style=\"border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($grandtotalsaldo, 2)."</div></td>
                </tr>						
            </table>";
        }

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan BO Tabungan.pdf';
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

        $acctsavings        = AcctSavings::select('savings_id', 'savings_name')
        ->where('data_state', 0)
        ->where('savings_status', 0)
        ->get();

        if(count($acctsavings)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan BO Tabungan")
                                            ->setSubject("")
                                            ->setDescription("Laporan BO Tabungan")
                                            ->setKeywords("Laporan, BO, Tabungan")
                                            ->setCategory("Laporan BO Tabungan");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan BO Tabungan");

            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);		

            $spreadsheet->getActiveSheet()->mergeCells("B1:G1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->mergeCells("B2:G2");
            $spreadsheet->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B2')->getFont()->setSize(11);
            $spreadsheet->getActiveSheet()->getStyle('B3:G3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:G3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:G3')->getFont()->setBold(true);
            if($sesi['office_id'] == 0){
                $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR SIMPANAN");
            } else {
                $office_name = CoreOffice::select('office_name')
                ->where('office_id', $sesi['office_id'])
                ->first()
                ->office_name;

                $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR SIMPANAN ".$office_name);
            }
            $spreadsheet->getActiveSheet()->setCellValue('B2',"Periode : ".$sesi['start_date']." S.D ".$sesi['end_date']);
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"No. Rek");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Alamat");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Basil");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Saldo");

            $no         = 0;
            $totalbasil = 0;
            $totalsaldo = 0;
            $i          = 4;
            if(empty($sesi['office_id'])){
                foreach ($acctsavings as $k => $v) {
                    $acctsavingsaccount = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.office_id', 'acct_savings_account.branch_id')
                    ->join('core_member', 'acct_savings_account.member_id', '=' ,'core_member.member_id')
                    ->join('acct_savings', 'acct_savings_account.savings_id', '=' ,'acct_savings.savings_id')
                    ->where('acct_savings_account.savings_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                    ->where('acct_savings_account.savings_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))			
                    ->where('acct_savings_account.data_state', 0)
                    ->where('acct_savings.savings_status', 0)
                    ->where('acct_savings_account.savings_id', $v['savings_id']);
                    if(!empty($sesi['office_id'])){
                        $acctsavingsaccount = $acctsavingsaccount->where('acct_savings_account.office_id', $sesi['office_id']);
                    }
                    if(!empty($branch_id)){
                        $acctsavingsaccount = $acctsavingsaccount->where('acct_savings_account.branch_id', $branch_id);
                    }
                    $acctsavingsaccount = $acctsavingsaccount->orderBy('acct_savings_account.savings_account_no', 'ASC')
                    ->get();

                    foreach($acctsavingsaccount as $key=>$val){
                        $savings_profit_sharing = AcctSavingsProfitSharing::where('savings_account_id', $val['savings_account_id'])
                        ->where('branch_id', $sesi['branch_id'])
                        ->where('savings_profit_sharing_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                        ->where('savings_profit_sharing_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                        ->sum('savings_profit_sharing_amount');

                        $no++;
                        $spreadsheet->setActiveSheetIndex(0);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i.':G'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $spreadsheet->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $spreadsheet->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $spreadsheet->getActiveSheet()->getStyle('E'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $spreadsheet->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        $spreadsheet->getActiveSheet()->getStyle('G'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                        $spreadsheet->getActiveSheet()->setCellValue('B'.$i, $no);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$i, $val['savings_account_no']);
                        $spreadsheet->getActiveSheet()->setCellValue('D'.$i, $val['member_name']);
                        $spreadsheet->getActiveSheet()->setCellValue('E'.$i, $val['member_address']);
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$i, number_format($savings_profit_sharing,2));
                        $spreadsheet->getActiveSheet()->setCellValue('G'.$i, number_format($val['savings_account_last_balance'],2));

                        $totalbasil += $savings_profit_sharing;
                        $totalsaldo += $val['savings_account_last_balance'];
                        $i++;
                        $no++;
                    }
                }
            } else {
                $totalbasil = 0;
                $totalsaldo = 0;
                foreach ($acctsavings as $k => $v) {
                    $acctsavingsaccount = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.office_id', 'acct_savings_account.branch_id')
                    ->join('core_member', 'acct_savings_account.member_id', '=' ,'core_member.member_id')
                    ->join('acct_savings', 'acct_savings_account.savings_id', '=' ,'acct_savings.savings_id')
                    ->where('acct_savings_account.savings_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                    ->where('acct_savings_account.savings_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))			
                    ->where('acct_savings_account.data_state', 0)
                    ->where('acct_savings.savings_status', 0)
                    ->where('acct_savings_account.savings_id', $v['savings_id']);
                    if(!empty($sesi['office_id'])){
                        $acctsavingsaccount = $acctsavingsaccount->where('acct_savings_account.office_id', $sesi['office_id']);
                    }
                    if(!empty($branch_id)){
                        $acctsavingsaccount = $acctsavingsaccount->where('acct_savings_account.branch_id', $branch_id);
                    }
                    $acctsavingsaccount = $acctsavingsaccount->orderBy('acct_savings_account.savings_account_no', 'ASC')
                    ->get();

                    if(!empty($acctsavingsaccount)){
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getFont()->setBold(true)->setSize(14);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$i.':G'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':G'.$i);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$i, $v['savings_name']);
                        
                        $nov            = 0;
                        $i              = $i+1;
                        $subtotalbasil  = 0;
                        $subtotalsaldo  = 0;

                        foreach($acctsavingsaccount as $key=>$val){
                            $savings_profit_sharing = AcctSavingsProfitSharing::where('savings_account_id', $val['savings_account_id'])
                            ->where('branch_id', $sesi['branch_id'])
                            ->where('savings_profit_sharing_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                            ->where('savings_profit_sharing_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                            ->sum('savings_profit_sharing_amount');

                            $nov++;
                            $spreadsheet->setActiveSheetIndex(0);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$i.':G'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('E'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('G'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            
                            $spreadsheet->getActiveSheet()->setCellValue('B'.$i, $nov);
                            $spreadsheet->getActiveSheet()->setCellValue('C'.$i, $val['savings_account_no']);
                            $spreadsheet->getActiveSheet()->setCellValue('D'.$i, $val['member_name']);
                            $spreadsheet->getActiveSheet()->setCellValue('E'.$i, $val['member_address']);
                            $spreadsheet->getActiveSheet()->setCellValue('F'.$i, $savings_profit_sharing);
                            $spreadsheet->getActiveSheet()->setCellValue('G'.$i, number_format($val['savings_account_last_balance'],2));
                    
                            $subtotalbasil += $savings_profit_sharing;
                            $subtotalsaldo += $val['savings_account_last_balance'];
                            $i++;
                        }							

                        $m = $i;

                        $spreadsheet->getActiveSheet()->getStyle('B'.$m.':G'.$m)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        $spreadsheet->getActiveSheet()->getStyle('B'.$m.':G'.$m)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$m.':E'.$m);
                        $spreadsheet->getActiveSheet()->setCellValue('B'.$m, 'SubTotal');
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$m, number_format($subtotalbasil,2));
                        $spreadsheet->getActiveSheet()->setCellValue('G'.$m, number_format($subtotalsaldo,2));
                    }
                    $i = $m+1;
                }

                $totalbasil += $subtotalbasil;
                $totalsaldo += $subtotalsaldo;
            }

            $n = $i;

            $spreadsheet->getActiveSheet()->getStyle('B'.$n.':G'.$n)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $spreadsheet->getActiveSheet()->getStyle('B'.$n.':G'.$n)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->mergeCells('B'.$n.':E'.$n);

            $spreadsheet->getActiveSheet()->setCellValue('B'.$n, 'Total');
            $spreadsheet->getActiveSheet()->setCellValue('F'.$n, number_format($totalbasil,2));
            $spreadsheet->getActiveSheet()->setCellValue('G'.$n, number_format($totalsaldo,2));

            ob_clean();
            $filename='Laporan BO Tabungan.xls';
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
