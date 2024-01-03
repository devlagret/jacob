<?php

namespace App\Http\Controllers;

use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPayment;
use App\Models\CoreBranch;
use App\Models\CoreOffice;
use App\Models\PreferenceCompany;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AcctCreditsDailyMutationController extends Controller
{
    public function payment() {
      $corebranch = CoreBranch::where('data_state', 0);
      if(Auth::user()->branch_id!=0){
            $corebranch->where('branch_id',Auth::user()->branch_id);
      }
      $corebranch = $corebranch->get();
      return view('content.AcctCreditsDailyMutation.payment.index',compact('corebranch'));
    }
    public function account() {
      $corebranch = CoreBranch::where('data_state', 0);
      if(Auth::user()->branch_id!=0){
            $corebranch->where('branch_id',Auth::user()->branch_id);
      }
      $corebranch = $corebranch->get();
      return view('content.AcctCreditsDailyMutation.account.index',compact('corebranch'));
    }
    public function paymentViewport(Request $request) {
		$sesi = array (
				'branch_id'		=> $request->branch_id,
				'office_id'		=> $request->office_id,
				'start_date'	=> $request->start_date,
				'end_date'		=> $request->end_date,
				"view"		=> $request->view
		);

		if($sesi['view'] == 'pdf'){
			return $this->pritCreditPayment($sesi);
		} else {
			return $this->exportCreditsPayment($sesi);
		}
    }
    public function accountViewport(Request $request) {
		$sesi = array (
			'branch_id'		=> $request->branch_id,
			'start_date'	=> $request->start_date,
			'end_date'		=> $request->end_date,
			"view"			=> $request->view,
		);

		if($sesi['view'] == 'pdf'){
			return $this->printCreditsAccount($sesi);
		} else {
			return $this->exportCreditsAccount($sesi);
		}
    }
    public function getOffice(Request $request) {
      $data = '';
      $bo = CoreOffice::where('branch_id',$request->branch_id)->get();
      foreach ($bo as $key => $value) {
            $data .= "<option data-kt-flag='".$value->office_id."' value='".$value->office_id."' ".($value->office_id == old('branch_id', $request->office_id_old ?? '') ? 'selected' :'')."  >". $value->office_name."</option>";
      }
      return response($data);
    }
    protected function pritCreditPayment($sesi) {
      $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
      $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
      if(Auth::user()->branch_id!=0&&empty($sesi['branch_id'])){
            $branch_id = Auth::user()->branch_id;
      }else{$branch_id = $sesi['branch_id'];}
      $acctcreditspayment = AcctCreditsPayment::with('member','account')
      ->join('acct_credits_account','acct_credits_account.credits_account_id','acct_credits_payment.credits_account_id')
      ->where('acct_credits_account.credits_approve_status',1)
      ->where('acct_credits_account.credits_account_date',">=",$sesi['start_date'])
      ->where('acct_credits_account.credits_account_date',"<=",$sesi['end_date']);
      if(!empty($branch_id)){$acctcreditspayment->where('acct_credits_account.branch_id', $branch_id);}
      if(!empty($sesi['office_id'])){$acctcreditspayment->where('acct_credits_account.office_id', $sesi['office_id']);}
      $acctcreditspayment = $acctcreditspayment->orderBy('acct_credits_account.credits_account_serial', 'ASC')
      ->get();
      // dd($acctcreditspayment);
      $pdf = new TCPDF(['L', PDF_UNIT, 'F4', true, 'UTF-8', false]);
      $pdf::SetPrintHeader(false);
      $pdf::SetPrintFooter(false);
      $pdf::SetMargins(6, 6, 6, 6);
      $pdf::AddPage('L','F4');
      $pdf::SetFont('helvetica', '', 10);
      $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);
      if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
          require_once(dirname(__FILE__).'/lang/eng.php');
          $pdf::setLanguageArray($l);
      }
      $export = "";
      $header="<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\"><tr>
            <td><div style=\"text-align: center; font-size:14px\">DAFTAR ANGSURAN PEMBIAYAAN TGL : &nbsp; ".date('d-m-Y',strtotime($sesi['start_date']))." - ".date('d-m-Y',strtotime($sesi['end_date']))."</div></td>
            </tr></table>";
      if(!empty($sesi['office_id'])){
            $header .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\"><tr>
                              <td><div style=\"text-align: left;font-size:12;font-weight:bold\">AO:  ".CoreOffice::where('office_id',$sesi['office_id'])->pluck('office_name')."</div></td>
                        </tr></table>";
      } else {
            $header .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\"><tr>
                        <td><div style=\"text-align: left;font-size:12;font-weight:bold\">GLOBAL</div></td>
                  </tr></table>";
      }
      $pdf::writeHTML($header, true, false, false, false, '');
      $pdf::SetFont('helvetica', '', 8);
      $export .= "<br><table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
          <tr>
            <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">NO.</div></td>
            <td width=\"13%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">NO. KREDIT</div></td>
            <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">NAMA</div></td>
            <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">ALAMAT</div></td>
            <td width=\"13%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">ANGS POKOK</div></td>
            <td width=\"13%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">ANGS BUNGA</div></td>
            <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">DENDA</div></td>
            <td width=\"15%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">TOTAL</div></td>
         </tr>
      </table>
      <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
      $no = 1;
      $totalpokok = 0;
      $totalmargin = 0;
      $totaltotal = 0;
      $totaldenda = 0;
      foreach ($acctcreditspayment as $key => $val) {
              $export .= "
              <tr>
                  <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                  <td width=\"13%\"><div style=\"text-align: left;\">".$val->account->credits_account_serial."</div></td>
                  <td width=\"15%\"><div style=\"text-align: left;\">".$val->member->member_name."</div></td>
                  <td width=\"15%\"><div style=\"text-align: left;\">".$val->member->member_address."</div></td>
                  <td width=\"13%\"><div style=\"text-align: right;\">".number_format($val['credits_payment_principal'])."</div></td>
                  <td width=\"13%\"><div style=\"text-align: right;\">".number_format($val['credits_payment_interest'], 2)."</div></td>
                  <td width=\"10%\"><div style=\"text-align: right;\">".number_format($val->account->credits_account_accumulated_fines)."</div></td>
                  <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val->account->credits_account_payment_amount)."</div></td>
              </tr>";
            $totalpokok 	+= $val['credits_payment_principal'];
            $totalmargin 	+= $val['credits_payment_interest'];
            $totaltotal	      += $val->account->credits_account_payment_amount;
            $totaldenda       += $val->account->credits_account_accumulated_fines;
            $no++;
      }
      $export .= "
          <tr>
            <td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".Auth::user()->username."</div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Jumlah </div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalpokok, 2)."</div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalmargin, 2)."</div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totaldenda, 2)."</div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totaltotal, 2)."</div></td>
            <td style=\"border-top: 1px solid black\"></td>
          </tr>
      </table>";
      //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
      $pdf::writeHTML($export, true, false, false, false, '');
      $filename = 'Laporan_Mutasi_Angsuran_Pembiayaan_'.date('dmYHisu').'.pdf';
      $pdf::Output($filename, 'I');
    }
    protected function exportCreditsPayment($sesi) {
      $spreadsheet        = new Spreadsheet();
      $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
      $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
      if(Auth::user()->branch_id!=0&&empty($sesi['branch_id'])){
            $branch_id = Auth::user()->branch_id;
      }else{$branch_id = $sesi['branch_id'];}
      $acctcreditspayment = AcctCreditsPayment::with('member','account')
      ->where('credits_approve_status',1)
      ->where('credits_account_date',">=",$sesi['start_date'])
      ->where('credits_account_date',"<=",$sesi['end_date']);
      if(!empty($branch_id)){$acctcreditspayment->where('branch_id', $branch_id);}
      if(!empty($sesi['office_id'])){$acctcreditspayment->where('office_id', $sesi['office_id']);}
      $acctcreditspayment = $acctcreditspayment->orderBy('credits_account_serial', 'ASC')
      ->get();
      if($acctcreditspayment->count()){
          $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                          ->setLastModifiedBy($preferencecompany['company_name'])
                                          ->setTitle("MUTASI ANGSURAN PEMBIAYAAN")
                                          ->setSubject("")
                                          ->setDescription("MUTASI ANGSURAN PEMBIAYAAN")
                                          ->setKeywords("MUTASI, SETORAN, BERJANGKA")
                                          ->setCategory("MUTASI ANGSURAN PEMBIAYAAN");

          $sheet = $spreadsheet->getActiveSheet();
          $spreadsheet->getActiveSheet()->setTitle("MUTASI ANGSURAN PEMBIAYAAN");

          $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
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
          $spreadsheet->getActiveSheet()->getStyle('B3:I5')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
          $spreadsheet->getActiveSheet()->getStyle('B3:I5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
          $spreadsheet->getActiveSheet()->getStyle('B3:I5')->getFont()->setBold(true);

          if(!empty($sesi['office_id'])){
            $spreadsheet->getActiveSheet()->setCellValue('B4',"AO : ".CoreOffice::where('office_id',$sesi['office_id'])->pluck('office_name'));
          } else {
            $spreadsheet->getActiveSheet()->setCellValue('B4',"GLOBAL");
          }

          $spreadsheet->getActiveSheet()->setCellValue('B1',"MUTASI ANGSURAN PEMBIAYAAN");
          $spreadsheet->getActiveSheet()->setCellValue('B2',"per Tanggal : ".date('d-m-Y',strtotime($sesi['start_date']))." S.D ".date('d-m-Y',strtotime($sesi['end_date'])));
          $spreadsheet->getActiveSheet()->setCellValue('B5',"No");
          $spreadsheet->getActiveSheet()->setCellValue('C5',"No Rek");
          $spreadsheet->getActiveSheet()->setCellValue('D5',"Nama ");
          $spreadsheet->getActiveSheet()->setCellValue('E5',"Alamat");
          $spreadsheet->getActiveSheet()->setCellValue('F5',"Angsuran Pokok");
          $spreadsheet->getActiveSheet()->setCellValue('G5',"Angsuran Bunga");
          $spreadsheet->getActiveSheet()->setCellValue('H5',"Denda");
          $spreadsheet->getActiveSheet()->setCellValue('I5',"Total");

          $no = 0;
          $totalpokok = 0;
          $totalmargin = 0;
          $totaltotal = 0;
          $totaldenda = 0;
          $j=5;

          foreach($acctcreditspayment as $key=>$val){
              $no++;

              $spreadsheet->getActiveSheet()->getStyle('B'.$j.':I'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
              $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
              $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
              $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
              $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
              $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
              $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
              $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
              $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

              $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
              $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val->account->credits_account_serial);
              $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val->member->member_name);
              $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $val->member->member_address);
              $spreadsheet->getActiveSheet()->setCellValue('F'.$j, number_format($val['credits_account_amount'],2));
              $spreadsheet->getActiveSheet()->setCellValue('G'.$j, number_format($val['credits_account_principal_amount'],2));
              $spreadsheet->getActiveSheet()->setCellValue('H'.$j, number_format($val->account->credits_account_accumulated_fines,2));
              $spreadsheet->getActiveSheet()->setCellValue('I'.$j, number_format($val->account->credits_account_payment_amount,2));

              $totalpokok 	+= $val['credits_payment_principal'];
              $totalmargin 	+= $val['credits_payment_interest'];
              $totaltotal	  += $val->account->credits_account_payment_amount;
              $totaldenda   += $val->account->credits_account_accumulated_fines;
              $j++;
          }

          $i = $j;

          $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':E'.$i);
          $spreadsheet->getActiveSheet()->getStyle('B'.$i.':I'.$i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
          $spreadsheet->getActiveSheet()->getStyle('B'.$i.':I'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

          $spreadsheet->getActiveSheet()->setCellValue('B'.$i, 'Total');
          $spreadsheet->getActiveSheet()->setCellValue('F'.$i, number_format($totalpokok,2));
          $spreadsheet->getActiveSheet()->setCellValue('G'.$i, number_format($totalmargin,2));
          $spreadsheet->getActiveSheet()->setCellValue('H'.$i, number_format($totaldenda,2));
          $spreadsheet->getActiveSheet()->setCellValue('I'.$i, number_format($totaltotal,2));

          ob_clean();
          $filename='Laporan_Mutasi_Angsuran_Pembiayaan_'.date('d_m_Y_Hisu').'.xls';
          header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
          header('Content-Disposition: attachment;filename="'.$filename.'"');
          header('Cache-Control: max-age=0');

          $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
          $writer->save('php://output');
      }else{
          return redirect()->back()->withInput()->with(['pesan' => 'Maaf data yang di eksport tidak ada !','alert' => 'warning']);
      }
    }
    protected function printCreditsAccount($sesi) {
      $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
      $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
      if(Auth::user()->branch_id!=0&&empty($sesi['branch_id'])){
            $branch_id = Auth::user()->branch_id;
      }else{$branch_id = $sesi['branch_id'];}
      $acctcreditspayment = AcctCreditsAccount::with('member')
      ->where('credits_approve_status',1)
      ->where('credits_account_date',">=",$sesi['start_date'])
      ->where('credits_account_date',"<=",$sesi['end_date']);
      if(!empty($branch_id)){$acctcreditspayment->where('branch_id', $branch_id);}
      $acctcreditspayment = $acctcreditspayment->orderBy('credits_account_serial', 'ASC')
      ->get();
      // dd($acctcreditspayment);
      $pdf = new TCPDF(['L', PDF_UNIT, 'F4', true, 'UTF-8', false]);
      $pdf::SetPrintHeader(false);
      $pdf::SetPrintFooter(false);
      $pdf::SetMargins(6, 6, 6, 6);
      $pdf::AddPage('L','F4');
      $pdf::SetFont('helvetica', '', 10);
      $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);
      if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
          require_once(dirname(__FILE__).'/lang/eng.php');
          $pdf::setLanguageArray($l);
      }
      $export = "";
      $header="<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\"><tr>
            <td><div style=\"text-align: center; font-size:14px\">DAFTAR PENCAIRAN PEMBIAYAAN TGL : &nbsp; ".date('d-m-Y',strtotime($sesi['start_date']))." - ".date('d-m-Y',strtotime($sesi['end_date']))."</div></td>
            </tr></table>";
      $pdf::writeHTML($header, true, false, false, false, '');
      $pdf::SetFont('helvetica', '', 8);
      $export .= "<br><table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
          <tr>
		  <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">NO.</div></td>
		  <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">NO. KREDIT</div></td>
		  <td width=\"18%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">NAMA</div></td>
		  <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">ALAMAT</div></td>
		  <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">POKOK</div></td>
		  <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">JK WAKTU</div></td>
		  <td width=\"12%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">JT TEMPO</div></td>
         </tr>
      </table>
      <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
      $no = 1;$totalsaldo=0;
      foreach ($acctcreditspayment as $key => $val) {
              $export .= "
              <tr>
                  <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                  <td width=\"12%\"><div style=\"text-align: left;\">".$val->account->credits_account_serial."</div></td>
                  <td width=\"18%\"><div style=\"text-align: left;\">".$val->member->member_name."</div></td>
                  <td width=\"25%\"><div style=\"text-align: left;\">".$val->member->member_address."</div></td>
                  <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['credits_account_amount'],2)."</div></td>
                  <td width=\"10%\"><div style=\"text-align: right;\">".$val->credits_account_period."</div></td>
                  <td width=\"12%\"><div style=\"text-align: right;\">".date('d-m-Y',strtotime($val->credits_account_due_date))."</div></td>
              </tr>";
				$totalsaldo 	+= $val['credits_account_amount'];
            $no++;
      }
      $export .= "
          <tr>
            <td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ".Auth::user()->username."</div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Jumlah </div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalsaldo, 2)."</div></td>
            <td style=\"border-top: 1px solid black\"></td>
            <td style=\"border-top: 1px solid black\"></td>
          </tr>
      </table>";
      //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
      $pdf::writeHTML($export, true, false, false, false, '');
      $filename = 'Laporan_Mutasi_Angsuran_Pembiayaan_'.date('dmYHisu').'.pdf';
      $pdf::Output($filename, 'I');
    }
    protected function exportCreditsAccount($sesi) {
      $spreadsheet        = new Spreadsheet();
      $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
      $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
      if(Auth::user()->branch_id!=0&&empty($sesi['branch_id'])){
            $branch_id = Auth::user()->branch_id;
      }else{$branch_id = $sesi['branch_id'];}
      $acctcreditspayment = AcctCreditsPayment::with('member','account')
      ->where('credits_approve_status',1)
      ->where('credits_account_date',">=",$sesi['start_date'])
      ->where('credits_account_date',"<=",$sesi['end_date']);
      if(!empty($branch_id)){$acctcreditspayment->where('branch_id', $branch_id);}
      if(!empty($sesi['office_id'])){$acctcreditspayment->where('office_id', $sesi['office_id']);}
      $acctcreditspayment = $acctcreditspayment->orderBy('credits_account_serial', 'ASC')
      ->get();
      if($acctcreditspayment->count()){
          $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                          ->setLastModifiedBy($preferencecompany['company_name'])
                                          ->setTitle("MUTASI PENCAIRAN PEMBIAYAAN")
                                          ->setSubject("")
                                          ->setDescription("MUTASI PENCAIRAN PEMBIAYAAN")
                                          ->setKeywords("MUTASI, PENCAIRAN, PEMBIAYAAN")
                                          ->setCategory("MUTASI PENCAIRAN PEMBIAYAAN");

          $sheet = $spreadsheet->getActiveSheet();
          $spreadsheet->getActiveSheet()->setTitle("MUTASI PENCAIRAN PEMBIAYAAN");

          $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
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
          $spreadsheet->getActiveSheet()->getStyle('B3:H4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
          $spreadsheet->getActiveSheet()->getStyle('B3:H4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
          $spreadsheet->getActiveSheet()->getStyle('B3:H4')->getFont()->setBold(true);



          $spreadsheet->getActiveSheet()->setCellValue('B1',"MUTASI PENCAIRAN PEMBIAYAAN");
          $spreadsheet->getActiveSheet()->setCellValue('B2',"per Tanggal : ".date('d-m-Y',strtotime($sesi['start_date']))." S.D ".date('d-m-Y',strtotime($sesi['end_date'])));
          $spreadsheet->getActiveSheet()->setCellValue('B4',"No");
          $spreadsheet->getActiveSheet()->setCellValue('C4',"No Rek");
          $spreadsheet->getActiveSheet()->setCellValue('D4',"Nama ");
          $spreadsheet->getActiveSheet()->setCellValue('E4',"Alamat");
          $spreadsheet->getActiveSheet()->setCellValue('F4',"Jangka Waktu");
          $spreadsheet->getActiveSheet()->setCellValue('G4',"Jatuh Tempo");
          $spreadsheet->getActiveSheet()->setCellValue('H4',"Pokok");

          $no = 0;
		  $totalsaldo		= 0;
          $j=5;

          foreach($acctcreditspayment as $key=>$val){
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
              $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val->account->credits_account_serial);
              $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val->member->member_name);
              $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $val->member->member_address);
              $spreadsheet->getActiveSheet()->setCellValue('F'.$j, number_format($val['credits_account_amount'],2));
              $spreadsheet->getActiveSheet()->setCellValue('G'.$j, $val->credits_account_period);
              $spreadsheet->getActiveSheet()->setCellValue('H'.$j, date('d-m-Y',strtotime($val->credits_account_due_date)));

				$totalsaldo 	+= $val['credits_account_amount'];
              $j++;
          }

          $i = $j;

          $spreadsheet->getActiveSheet()->mergeCells('B'.$i.':G'.$i);
          $spreadsheet->getActiveSheet()->getStyle('B'.$i.':H'.$i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
          $spreadsheet->getActiveSheet()->getStyle('B'.$i.':H'.$i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

          $spreadsheet->getActiveSheet()->setCellValue('B'.$i, 'Total');
          $spreadsheet->getActiveSheet()->setCellValue('H'.$i, number_format($totalsaldo,2));

          ob_clean();
          $filename='Laporan_Pencairan_Pembiyaan'.date('d_m_Y_Hisu').'.xls';
          header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
          header('Content-Disposition: attachment;filename="'.$filename.'"');
          header('Cache-Control: max-age=0');

          $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
          $writer->save('php://output');
      }else{
          return redirect()->back()->withInput()->with(['pesan' => 'Maaf data yang di eksport tidak ada !','alert' => 'warning']);
      }
    }
}
