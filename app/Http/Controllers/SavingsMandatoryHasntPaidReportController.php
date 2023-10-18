<?php

    namespace App\Http\Controllers;

    use App\Models\AcctSavingsMemberDetail;
    use App\Models\CoreBranch;
    use App\Models\CoreMember;
    use App\Models\PreferenceCompany;
    use Carbon\Carbon;
    use Elibyy\TCPDF\Facades\TCPDF;
    use Illuminate\Http\Request;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;

    class SavingsMandatoryHasntPaidReportController extends Controller
    {
        public function index()
        {
            $corebranch = CoreBranch::where('data_state', 0)->get();

            return view('content.SavingsMandatoryHasntPaidReport.index', compact('corebranch'));
        }

        public function viewport(Request $request)
        {
            $sesi = array (
                "start_date"    => $request->start_date,
                "end_date"    => $request->end_date,
                "branch_id"	    => $request->branch_id,
                "view"		    => $request->view,
            );
            if($sesi['view'] == 'pdf'){
               return $this->processPrinting($sesi);
            }else{
                return $this->export($sesi);
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

            $coremember = CoreMember::where('data_state',0)
            ->where('member_register_date','>=', Carbon::parse($sesi['start_date'])->format('Y-m-d'))
            ->where('member_register_date','<=', Carbon::parse($sesi['end_date'])->format('Y-m-d'));
            if(!empty($branch_id)){
                $coremember = $coremember->where('branch_id', $branch_id);
            }
            $coremember = $coremember->get();
            $pdf = new TCPDF(['L', PDF_UNIT, 'A4', true, 'UTF-8', false]);

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

            $head = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                            <tr>
                                <td><div style=\"text-align: center; font-size:14px\">DAFTAR TUNGGAKAN SIMPANAN WAJIB</div></td>
                            </tr>
                        </table>
            ";
            $pdf::writeHTML($head, true, false, false, false, '');
            $export = "
            <br><table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\"><tr>
                    <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:9;\">No.</div></td>
                    <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:9;\">No Anggota</div></td>
                    <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:9;\">Nama</div></td>
                    <td width=\"30%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:9;\">Terakhir Setor</div></td>
                    <td width=\"25%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:9;\">Tunggakan</div></td>
                </tr></table>";

            $no                 = 1;
            foreach ($coremember as $key => $val) {
                $savingdetail	= AcctSavingsMemberDetail::where('mutation_id',1)
                ->where('member_id', $val->member_id)
                ->orderByDesc('transaction_date')
                ->first();
                if($savingdetail){
                $start  	=  Carbon::parse(Carbon::now()->format('Y-m-d'));
                $end 		=  Carbon::parse($savingdetail->transaction_date);
                $interval 		= $start->diff($end);
                $Keterlambatan 	= $interval->m;
                if(($Keterlambatan >= 1)){
                    $export .= "
                    <tr>
                    <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                    <td width=\"25%\"><div style=\"text-align: left;\">".$val['member_no']."</div></td>
                    <td width=\"25%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                    <td width=\"25%\"><div style=\"text-align: left;\">".date('d-m-Y',strtotime($savingdetail->transaction_date))."</div></td>
                    <td width=\"20%\"><div style=\"text-align: center;\">".$Keterlambatan."</div></td>
                    </tr>";
                    $no++;
                }
            }
            }

            //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
            $pdf::writeHTML($export, true, false, false, false, '');

            $filename = 'Laporan Tunggakan Simpanan Wajib - '.Carbon::now()->format('d-m-Y-Hisu').'.pdf';
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

            $coremember = CoreMember::where('data_state',0)
            ->where('member_register_date','>=', Carbon::parse($sesi['start_date'])->format('Y-m-d'))
            ->where('member_register_date','<=', Carbon::parse($sesi['end_date'])->format('Y-m-d'));
            if(!empty($branch_id)){
                $coremember = $coremember->where('branch_id', $branch_id);
            }
            $coremember = $coremember->get();


            if($coremember->count()){
                $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                                ->setLastModifiedBy($preferencecompany['company_name'])
                                                ->setTitle("DAFTAR TUNGGAKAN SIMPANAN WAJIB")
                                                ->setSubject("")
                                                ->setDescription("DAFTAR TUNGGAKAN SIMPANAN WAJIB")
                                                ->setKeywords("DAFTAR, TUNGGAKAN, SIMPANAN, WAJIB")
                                                ->setCategory("DAFTAR TUNGGAKAN SIMPANAN WAJIB");

                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);;

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
                $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
                $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);

                $spreadsheet->getActiveSheet()->mergeCells("B1:F1");
                $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
                $spreadsheet->getActiveSheet()->getStyle('B3:f3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B3:f3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B3:f3')->getFont()->setBold(true);

                $spreadsheet->getActiveSheet()->setCellValue('B1',"DAFTAR TUNGGAKAN SIMPANAN WAJIB");
                $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
                $spreadsheet->getActiveSheet()->setCellValue('C3',"No. Anggota");
                $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Anggota");
                $spreadsheet->getActiveSheet()->setCellValue('E3',"Terakhir Angsur");
                $spreadsheet->getActiveSheet()->setCellValue('F3',"Tunggakan");

                $no                 = 0;
                $j                  = 4;

                foreach($coremember as $key=>$val){
                    $savingdetail	= AcctSavingsMemberDetail::where('mutation_id',1)
                    ->where('member_id', $val->member_id)
                    ->orderByDesc('transaction_date')
                    ->first();
                    if($savingdetail){

                        $start  	=  Carbon::parse(Carbon::now()->format('Y-m-d'));
                        $end 		=  Carbon::parse($savingdetail->transaction_date);
                        $interval 		= $start->diff($end);
                        $Keterlambatan 	= $interval->m;

                        $no++;

                        $spreadsheet->getActiveSheet()->getStyle('B'.$j.':F'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
                        $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['member_no']."p");
                        $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
                        $spreadsheet->getActiveSheet()->setCellValue('E'.$j, date('d-m-Y',strtotime($savingdetail->transaction_date)));
                        $spreadsheet->getActiveSheet()->setCellValue('F'.$j, $Keterlambatan);

                        $j++;
                    }
                }

                ob_clean();
                $filename='DAFTAR TUNGGAKAN SIMPANAN WAJIB - '.Carbon::now()->format('d-m-Y-Hisu').'.xls';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$filename.'"');
                header('Cache-Control: max-age=0');

                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
                $writer->save('php://output');
            }else{
                return redirect()->back()->with(['pesan' => 'Maaf data yang di eksport tidak ada !','alert' => 'warning']);
            }
        }
    }
