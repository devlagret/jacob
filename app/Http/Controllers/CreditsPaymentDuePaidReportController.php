<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPayment;
use App\Models\PreferenceCompany;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DateTime;

class CreditsPaymentDuePaidReportController extends Controller
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

        return view('content.CreditsPaymentDuePaidReport.index', compact('corebranch'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "start_date"    => $request->start_date,
            "branch_id"	    => $request->branch_id,
            "view"		    => $request->view,
        );

        if($sesi['view'] == 'pdf'){
         return   $this->processPrinting($sesi);
        }else{
         return   $this->export($sesi);
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

        $acctcreditsaccount	= AcctCreditsAccount::select('acct_credits_account.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_principal_amount', 'acct_credits_account.credits_account_interest_amount', 'acct_credits_account.credits_account_last_balance', 'acct_credits_account.credits_account_payment_date', 'acct_credits_account.credits_account_last_payment_date', 'acct_credits_account.credits_account_payment_amount', 'acct_credits_account.credits_account_accumulated_fines', 'acct_credits_account.credits_account_period', 'acct_credits_account.credits_account_payment_to', 'acct_credits_account.credits_account_status')
        ->withoutGlobalScopes()
        ->join('core_member', 'acct_credits_account.member_id', '=' ,'core_member.member_id')
        ->where('acct_credits_account.credits_account_due_date', date('Y-m-d', strtotime($sesi['start_date'])))
        ->where('acct_credits_account.credits_account_status', 0)
        ->where('acct_credits_account.credits_approve_status', 1)
        ->where('acct_credits_account.data_state', 0);
        if(!empty($branch_id)){
            $acctcreditsaccount	= $acctcreditsaccount->where('acct_credits_account.branch_id', $branch_id);
        }
        $acctcreditsaccount	= $acctcreditsaccount->orderBy('acct_credits_account.credits_account_serial', 'ASC')
        ->get();

        $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(0, 6, 6, 6);

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
                <td><div style=\"text-align: center; font-size:14px\">DAFTAR PINJAMAN JATUH TEMPO TGL ".$sesi['start_date']."</div></td>
            </tr>
        </table>";

        $export .= "
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"3%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:9;\">No.</div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">No. Akad</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Nama</div></td>
                <td width=\"18%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Alamat</div></td>
                <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Plafon</div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Angs Pokok</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Angs Margin</div></td>
                <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">SLD Pokok (outstanding)</div></td>
                <td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Tenor</div></td>
                <td width=\"8%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:9;\">Tgl Terakhir Angsur</div></td>
            </tr>
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $no                 = 1;
        $totalplafon        = 0;
        $totalangspokok     = 0;
        $totalangsmargin    = 0;
        $totalangs          = 0;
        $totalsisa          = 0;
        $totaldenda         = 0;
        foreach ($acctcreditsaccount as $key => $val) {
            $acctcredits_fine   = AcctCreditsAccount::select('acct_credits.credits_fine')
            ->withoutGlobalScopes()
			->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
			->where('acct_credits_account.credits_account_id', $val['credits_account_id'])
            ->first();

            $day 		        = date('d-m-Y');
            $start_ 	        = new DateTime($day);
            $end_ 		        = new DateTime($val['credits_account_payment_date']);
            $status 	        = $val['credits_account_status'];

            if($end_ >= $start_){
                $Keterlambatan 	= '0';
            }else{
                $interval 		= $start_->diff($end_);
                $Keterlambatan 	= $interval->days;
            }

            $credits_payment_fine_amount 		= (($val['credits_account_payment_amount'] * $acctcredits_fine['credits_fine']) / 100 ) * $Keterlambatan;
            $credits_account_accumulated_fines 	= $val['credits_account_accumulated_fines'] + $credits_payment_fine_amount;
            $credits_account_payment_to         = ($val['credits_account_payment_to'] + 1);

            $export .= "
            <tr>
                <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                <td width=\"10%\"><div style=\"text-align: left;\">".$val['credits_account_serial']."</div></td>
                <td width=\"15%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                <td width=\"10%\"><div style=\"text-align: left;\">".number_format($val['credits_account_amount'], 2)."</div></td>
                <td width=\"10%\"><div style=\"text-align: left;\">".number_format($val['credits_account_principal_amount'], 2)."</div></td>
                <td width=\"10%\"><div style=\"text-align: left;\">".number_format($val['credits_account_interest_amount'], 2)."</div></td>
                <td width=\"9%\"><div style=\"text-align: left;\">".number_format($val['credits_account_last_balance'], 2)."</div></td>
                <td width=\"4%\"><div style=\"text-align: left;\">".$credits_account_payment_to." / ".$val['credits_account_period']."</div></td>
                <td width=\"8%\"><div style=\"text-align: center;\">".date('d-m-Y',strtotime($val['credits_account_last_payment_date']))."</div></td>
            </tr>";

            $totalplafon	 	+= $val['credits_account_amount'];
            $totalangspokok 	+= $val['credits_account_principal_amount'];
            $totalangsmargin 	+= $val['credits_account_interest_amount'];
            $totalangs 			+= $val['credits_account_payment_amount'];
            $totalsisa 			+= $val['credits_account_last_balance'];
            $totaldenda			+= $val['credits_account_accumulated_fines'];
            $no++;
        }

        $export .= "
            <tr>
                <td colspan =\"3\"><div style=\"font-size:8;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".auth()->user()->username."</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:8;font-weight:bold;text-align:center\">Total </div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:8;text-align:left\">".number_format($totalplafon, 2)."</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:8;text-align:left\">".number_format($totalangspokok, 2)."</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:8;text-align:left\">".number_format($totalangsmargin, 2)."</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:8;text-align:left\">".number_format($totalsisa, 2)."</div></td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Anggota Jatuh Tempo Angsur.pdf';
        $pdf::Output($filename, 'I');
    }

    public function export($sesi){
        // dd($sesi);
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

        $acctcreditsaccount	= AcctCreditsAccount::select('acct_credits_account.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_principal_amount', 'acct_credits_account.credits_account_interest_amount', 'acct_credits_account.credits_account_last_balance', 'acct_credits_account.credits_account_payment_date', 'acct_credits_account.credits_account_last_payment_date', 'acct_credits_account.credits_account_payment_amount', 'acct_credits_account.credits_account_accumulated_fines', 'acct_credits_account.credits_account_period', 'acct_credits_account.credits_account_payment_to', 'acct_credits_account.credits_account_status')
        ->withoutGlobalScopes()
        ->join('core_member', 'acct_credits_account.member_id', '=' ,'core_member.member_id')
        ->where('acct_credits_account.credits_account_due_date', date('Y-m-d', strtotime($sesi['start_date'])))
        ->where('acct_credits_account.credits_account_status', 0)
        ->where('acct_credits_account.credits_approve_status', 1)
        ->where('acct_credits_account.data_state', 0);
        if(!empty($branch_id)){
            $acctcreditsaccount	= $acctcreditsaccount->where('acct_credits_account.branch_id', $branch_id);
        }
        $acctcreditsaccount	= $acctcreditsaccount->orderBy('acct_credits_account.credits_account_serial', 'ASC')
        ->get();
    // dd($acctcreditsaccount);
        if($acctcreditsaccount->count()){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("DAFTAR PINJAMAN JATUH TEMPO")
                                            ->setSubject("")
                                            ->setDescription("DAFTAR PINJAMAN JATUH TEMPO")
                                            ->setKeywords("DAFTAR, PINJAMAN, JATUH TEMPO")
                                            ->setCategory("DAFTAR PINJAMAN JATUH TEMPO");
            $spreadsheet->setActiveSheetIndex(0);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
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
            $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(40);

            $spreadsheet->getActiveSheet()->mergeCells("B1:K1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:K3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:K3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:K3')->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR PINJAMAN JATUH TEMPO S.D ".$sesi['start_date']);
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"No. Kredit");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Alamat");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Plafon");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Angs Pokok");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Angs Bunga");
            $spreadsheet->getActiveSheet()->setCellValue('I3',"Total Angsuran");
            $spreadsheet->getActiveSheet()->setCellValue('J3',"Saldo Pokok (Outstanding)");
            $spreadsheet->getActiveSheet()->setCellValue('K3',"Tanggal Terakhir Angsuran");

            $no                 = 0;
            $totalplafon        = 0;
            $totalangspokok     = 0;
            $totalangsmargin    = 0;
            $totalangs          = 0;
            $totalsisa          = 0;
            $totaldenda         = 0;
            $j                  = 4;

            foreach($acctcreditsaccount as $key=>$val){
                $acctcredits_fine   = AcctCreditsAccount::select('acct_credits.credits_fine')
                ->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
                ->where('acct_credits_account.credits_account_id', $val['credits_account_id'])
                ->first();

                $day 		        = date('d-m-Y');
                $start_ 	        = new DateTime($day);
                $end_ 		        = new DateTime($val['credits_account_payment_date']);

                if($end_ >= $start_){
                    $Keterlambatan 	= '0';
                }else{
                    $interval 		= $start_->diff($end_);
                    $Keterlambatan 	= $interval->days;
                }

                $credits_payment_fine_amount 		= (($val['credits_account_payment_amount'] * $acctcredits_fine['credits_fine']) / 100 ) * $Keterlambatan;
                $credits_account_accumulated_fines 	= $val['credits_account_accumulated_fines'] + $credits_payment_fine_amount;

                $no++;
                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getActiveSheet()->getStyle('B'.$j.':K'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
                $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['credits_account_serial']);
                $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
                $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $val['member_address']);
                $spreadsheet->getActiveSheet()->setCellValue('F'.$j, number_format($val['credits_account_amount'],2));
                $spreadsheet->getActiveSheet()->setCellValue('G'.$j, number_format($val['credits_account_principal_amount'],2));
                $spreadsheet->getActiveSheet()->setCellValue('H'.$j, number_format($val['credits_account_interest_amount'],2));
                $spreadsheet->getActiveSheet()->setCellValue('I'.$j, number_format($val['credits_account_payment_amount'],2));
                $spreadsheet->getActiveSheet()->setCellValue('J'.$j, number_format($val['credits_account_last_balance'],2));
                $spreadsheet->getActiveSheet()->setCellValue('K'.$j, date('d-m-Y',strtotime($val['credits_account_last_payment_date'])));

                $totalplafon        += $val['credits_account_amount'];
                $totalangspokok     += $val['credits_account_principal_amount'];
                $totalangsmargin    += $val['credits_account_interest_amount'];
                $totalangs          += $val['credits_account_payment_amount'];
                $totalsisa          += $val['credits_account_last_balance'];
                $totaldenda         += $val['credits_account_accumulated_fines'];
                $j++;
            }

            $i = $j;

            $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':E'.$i);
            $spreadsheet->getActiveSheet()->getStyle('B'.$i.':K'.$i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $spreadsheet->getActiveSheet()->getStyle('B'.$i.':K'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->setCellValue('B'.$i, 'Total');
            $spreadsheet->getActiveSheet()->setCellValue('F'.$i, number_format($totalplafon,2));
            $spreadsheet->getActiveSheet()->setCellValue('G'.$i, number_format($totalangspokok,2));
            $spreadsheet->getActiveSheet()->setCellValue('H'.$i, number_format($totalangsmargin,2));
            $spreadsheet->getActiveSheet()->setCellValue('I'.$i, number_format($totalangs,2));
            $spreadsheet->getActiveSheet()->setCellValue('J'.$i, number_format($totalsisa,2));
            $spreadsheet->getActiveSheet()->setCellValue('K'.$i, number_format($totaldenda,2));

            ob_clean();
            $filename='DAFTAR PINJAMAN JATUH TEMPO - '.Carbon::now()->format('Y-m-d-Hisu').'.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }else{
            echo redirect()->back()->with(['pesan' => 'Maaf data yang di eksport tidak ada !','alert' => 'warning']);
        }
    }
}
