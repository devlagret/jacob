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
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SavingsDailyTransferMutationController extends Controller
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

        return view('content.SavingsDailyTransferMutation.index', compact('corebranch'));
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

        $acctsavingstransfermutation    = AcctSavingsTransferMutation::select('acct_savings_transfer_mutation.savings_transfer_mutation_id', 'acct_savings_transfer_mutation.savings_transfer_mutation_date', 'acct_savings_transfer_mutation.savings_transfer_mutation_amount')
        ->where('acct_savings_transfer_mutation.savings_transfer_mutation_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
        ->where('acct_savings_transfer_mutation.savings_transfer_mutation_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
        ->where('acct_savings_transfer_mutation.data_state', 0);
        if(!empty($branch_id)){
            $acctsavingstransfermutation = $acctsavingstransfermutation->where('acct_savings_transfer_mutation.branch_id', $branch_id);
        }			
        $acctsavingstransfermutation = $acctsavingstransfermutation->get();

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
        
        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">NO.</div></td>
                <td width=\"11%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">TANGGAL</div></td>
                <td width=\"16%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">NO. REK</div></td>
                <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">NAMA</div></td>
                <td width=\"8%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">SANDI</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">NOMINAL</div></td>
                <td width=\"17%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Saldo</div></td>
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $no                 = 1;
        $totalnominalfrom   = 0;
        $totalsaldofrom     = 0;
        $totalnominalto     = 0;
        $totalsaldoto       = 0;
        foreach ($acctsavingstransfermutation as $key => $val) {
            $acctsavingstransfermutationfrom = AcctSavingsTransferMutationFrom::select()
			->join('acct_savings_account', 'acct_savings_transfer_mutation_from.savings_account_id' ,'=', 'acct_savings_account.savings_account_id')
			->join('core_member', 'acct_savings_transfer_mutation_from.member_id' ,'=', 'core_member.member_id')
			->where('acct_savings_transfer_mutation_from.savings_transfer_mutation_id', $val['savings_transfer_mutation_id'])
            ->get();

            foreach ($acctsavingstransfermutationfrom as $kFrom => $vFrom) {
                $mutation_code = AcctMutation::select('mutation_code')
                ->where('mutation_id', $vFrom['mutation_id'])
                ->first()
                ->mutation_code;

                $export .= "
                <tr>
                    <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                    <td width=\"11%\"><div style=\"text-align: left;\">".$val['savings_transfer_mutation_date']."</div></td>
                    <td width=\"16%\"><div style=\"text-align: left;\">".$vFrom['savings_account_no']."</div></td>
                    <td width=\"25%\"><div style=\"text-align: left;\">".$vFrom['member_name']."</div></td>
                    <td width=\"8%\"><div style=\"text-align: center;\">".$mutation_code."</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">".number_format($vFrom['savings_transfer_mutation_from_amount'], 2)."</div></td>
                    <td width=\"17%\"><div style=\"text-align: right;\">".number_format($vFrom['savings_account_last_balance'], 2)."</div></td>
                </tr>";

                $totalnominalfrom 	+= $vFrom['savings_transfer_mutation_from_amount'];
                $totalsaldofrom 	+= $vFrom['savings_account_last_balance'];
                $no++;
            }

            $acctsavingstransfermutationto  = AcctSavingsTransferMutationTo::select('acct_savings_transfer_mutation_to.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_transfer_mutation_to.member_id', 'core_member.member_name', 'acct_savings_transfer_mutation_to.savings_account_opening_balance', 'acct_savings_transfer_mutation_to.savings_transfer_mutation_to_amount', 'acct_savings_transfer_mutation_to.savings_account_last_balance', 'acct_savings_transfer_mutation_to.mutation_id')
			->join('acct_savings_account', 'acct_savings_transfer_mutation_to.savings_account_id' ,'=', 'acct_savings_account.savings_account_id')
			->join('core_member', 'acct_savings_transfer_mutation_to.member_id' ,'=', 'core_member.member_id')
			->where('acct_savings_transfer_mutation_to.savings_transfer_mutation_id', $val['savings_transfer_mutation_id'])
            ->get();

            foreach ($acctsavingstransfermutationto as $kTo => $vTo) {
                $mutation_code = AcctMutation::select('mutation_code')
                ->where('mutation_id', $vTo['mutation_id'])
                ->first()
                ->mutation_code;

                $export .= "
                <tr>
                    <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                    <td width=\"11%\"><div style=\"text-align: left;\">".$val['savings_transfer_mutation_date']."</div></td>
                    <td width=\"16%\"><div style=\"text-align: left;\">".$vTo['savings_account_no']."</div></td>
                    <td width=\"25%\"><div style=\"text-align: left;\">".$vTo['member_name']."</div></td>
                    <td width=\"8%\"><div style=\"text-align: center;\">".$mutation_code."</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">".number_format($vTo['savings_transfer_mutation_to_amount'], 2)."</div></td>
                    <td width=\"17%\"><div style=\"text-align: right;\">".number_format($vTo['savings_account_last_balance'], 2)."</div></td>
                </tr>";

                $totalnominalto += $vTo['savings_transfer_mutation_to_amount'];
                $totalsaldoto 	+= $vTo['savings_account_last_balance'];
                $no++;
            }
        }

        $grandtotalnominal 	= $totalnominalfrom + $totalnominalto;
        $grandtotalsaldo	= $totalsaldoto + $totalsaldofrom;
        $export .= "
            <tr>
                <td colspan =\"4\" style=\"border-top: 1px solid black;\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Jumlah </div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($grandtotalnominal, 2)."</div></td>
                <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($grandtotalsaldo, 2)."</div></td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Mutasi Harian Non Tunai Simpanan.pdf';
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

        $acctsavingstransfermutation    = AcctSavingsTransferMutation::select('acct_savings_transfer_mutation.savings_transfer_mutation_id', 'acct_savings_transfer_mutation.savings_transfer_mutation_date', 'acct_savings_transfer_mutation.savings_transfer_mutation_amount')
        ->where('acct_savings_transfer_mutation.savings_transfer_mutation_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
        ->where('acct_savings_transfer_mutation.savings_transfer_mutation_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
        ->where('acct_savings_transfer_mutation.data_state', 0);
        if(!empty($branch_id)){
            $acctsavingstransfermutation = $acctsavingstransfermutation->where('acct_savings_transfer_mutation.branch_id', $branch_id);
        }			
        $acctsavingstransfermutation = $acctsavingstransfermutation->get();


        if(count($acctsavingstransfermutation)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan Mutasi Harian Non Tunai Simpanan")
                                            ->setSubject("")
                                            ->setDescription("Laporan Mutasi Harian Non Tunai Simpanan")
                                            ->setKeywords("Laporan, Mutasi, Harian, Non, Tunai, Simpanan")
                                            ->setCategory("Laporan Mutasi Harian Non Tunai Simpanan");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan MHNTS");
            
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);		
            
            $spreadsheet->getActiveSheet()->mergeCells("B1:H1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue('B1',"MUTASI SIMPANAN NON TUNAI TGL : ".$sesi['start_date']." S.D ".$sesi['end_date']."");
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"Tanggal");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"No. Rek");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Sandi");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Nominal");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Saldo");
            
            $a                  = 4;
            $no                 = 0;
            $totalnominalfrom   = 0;
            $totalnominalto     = 0;
            $totalsaldoto       = 0;
            $totalsaldofrom     = 0;
            foreach($acctsavingstransfermutation as $key=>$val){
                $j = $a;

                $acctsavingstransfermutationfrom = AcctSavingsTransferMutationFrom::select('acct_savings_transfer_mutation_from.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_transfer_mutation_from.member_id', 'core_member.member_name', 'acct_savings_transfer_mutation_from.savings_account_opening_balance', 'acct_savings_transfer_mutation_from.savings_transfer_mutation_from_amount', 'acct_savings_transfer_mutation_from.savings_account_last_balance', 'acct_savings_transfer_mutation_from.mutation_id')
                ->join('acct_savings_account', 'acct_savings_transfer_mutation_from.savings_account_id' ,'=', 'acct_savings_account.savings_account_id')
                ->join('core_member', 'acct_savings_transfer_mutation_from.member_id' ,'=', 'core_member.member_id')
                ->where('acct_savings_transfer_mutation_from.savings_transfer_mutation_id', $val['savings_transfer_mutation_id'])
                ->get();

                foreach ($acctsavingstransfermutationfrom as $kForm => $vFrom) {
                    $no++;
                    $mutation_code = AcctMutation::select('mutation_code')
                    ->where('mutation_id', $vFrom['mutation_id'])
                    ->first()
                    ->mutation_code;

                    $spreadsheet->getActiveSheet()->getStyle('B'.$j.':H'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                    $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
                    $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['savings_transfer_mutation_date']);
                    $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $vFrom['savings_account_no']);
                    $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $vFrom['member_name']);
                    $spreadsheet->getActiveSheet()->setCellValue('F'.$j, $mutation_code);
                    $spreadsheet->getActiveSheet()->setCellValue('G'.$j, number_format($vFrom['savings_transfer_mutation_from_amount'],2));
                    $spreadsheet->getActiveSheet()->setCellValue('H'.$j, number_format($vFrom['savings_account_last_balance'],2));

                    $j++;
                }

                $i = $j;
                $acctsavingstransfermutationto = AcctSavingsTransferMutationTo::select('acct_savings_transfer_mutation_to.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_transfer_mutation_to.member_id', 'core_member.member_name', 'acct_savings_transfer_mutation_to.savings_account_opening_balance', 'acct_savings_transfer_mutation_to.savings_transfer_mutation_to_amount', 'acct_savings_transfer_mutation_to.savings_account_last_balance', 'acct_savings_transfer_mutation_to.mutation_id')
                ->join('acct_savings_account', 'acct_savings_transfer_mutation_to.savings_account_id', '=', 'acct_savings_account.savings_account_id')
                ->join('core_member', 'acct_savings_transfer_mutation_to.member_id', '=', 'core_member.member_id')
                ->where('acct_savings_transfer_mutation_to.savings_transfer_mutation_id', $val['savings_transfer_mutation_id'])
                ->get();

                foreach ($acctsavingstransfermutationto as $kTo => $vTo) {
                    $no++;
                    $mutation_code = AcctMutation::select('mutation_code')
                    ->where('mutation_id', $vTo['mutation_id'])
                    ->first()
                    ->mutation_code;

                    $spreadsheet->getActiveSheet()->getStyle('B'.$i.':H'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('G'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $spreadsheet->getActiveSheet()->getStyle('H'.$i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                    $spreadsheet->getActiveSheet()->setCellValue('B'.$i, $no);
                    $spreadsheet->getActiveSheet()->setCellValue('C'.$i, $val['savings_transfer_mutation_date']);
                    $spreadsheet->getActiveSheet()->setCellValue('D'.$i, $vTo['savings_account_no']);
                    $spreadsheet->getActiveSheet()->setCellValue('E'.$i, $vTo['member_name']);
                    $spreadsheet->getActiveSheet()->setCellValue('F'.$i, $mutation_code);
                    $spreadsheet->getActiveSheet()->setCellValue('G'.$i, number_format($vTo['savings_transfer_mutation_to_amount'],2));
                    $spreadsheet->getActiveSheet()->setCellValue('H'.$i, number_format($vTo['savings_account_last_balance'],2));

                    $totalnominalto += $vTo['savings_transfer_mutation_to_amount'];
                    $totalsaldoto 	+= $vTo['savings_account_last_balance'];
                    $i++;
                }
                $a = $i;
            }

            $grandtotalnominal 	= $totalnominalfrom + $totalnominalto;
            $grandtotalsaldo	= $totalsaldoto + $totalsaldofrom;
            $m                  = $a;

            $spreadsheet->getActiveSheet()->mergeCells('B'.$m.':F'.$m);
            $spreadsheet->getActiveSheet()->getStyle('B'.$m.':H'.$m)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $spreadsheet->getActiveSheet()->getStyle('B'.$m.':H'.$m)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->setCellValue('B'.$m, 'Total');
            $spreadsheet->getActiveSheet()->setCellValue('G'.$m, number_format($grandtotalnominal,2));
            $spreadsheet->getActiveSheet()->setCellValue('H'.$m, number_format($grandtotalsaldo,2));

            ob_clean();
            $filename='Laporan Mutasi Harian Non Tunai Simpanan.xls';
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
