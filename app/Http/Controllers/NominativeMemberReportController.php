<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\CoreMember;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NominativeMemberReportController extends Controller
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
       
        $membercharacter    = Configuration::MemberCharacter();

        return view('content.NominativeMember.index', compact('corebranch', 'membercharacter'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "branch_id"			=> $request->branch_id,
            "member_character"	=> $request->member_character,
            "view"				=> $request->view,
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
        
        $coremember = CoreMember::select('member_id', 'member_name', 'member_no', 'member_address', 'member_principal_savings_last_balance', 'member_special_savings_last_balance', 'member_mandatory_savings_last_balance')
        ->where('data_state', 0);
        if($sesi['member_character']){
            $coremember = $coremember->where('member_character', $sesi['member_character']);
        }
        if($branch_id != ''){
            $coremember = $coremember->where('branch_id', $branch_id);
        }
        $coremember = $coremember->orderBy('member_no', 'ASC')
        ->get();

        if($sesi['member_character'] == 9){
            $membercharacter = 'ANGGOTA BIASA DAN ANGGOTA LUAR BIASA';
        } else if($sesi['member_character'] == 0){
            $membercharacter = 'ANGGOTA BIASA';
        } else if($sesi['member_character'] == 1){
            $membercharacter = 'ANGGOTA LUAR BIASA';
        } else if($sesi['member_character'] == 2){
            $membercharacter = 'PENDIRI';
        }

        $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(7.5, 7.5, 7, 7);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $simp_pokok = 0;
        $simp_khs   = 0;
        $simp_wjb   = 0;

        $export = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td><div style=\"text-align: left;font-size:12; font-weight:bold\">DAFTAR REGISTER : ".$membercharacter."</div></td>			       
            </tr>						
        </table>
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Anggota</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Simp Pokok</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Simp WJB</div></td>
                <td width=\"15%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Simp KHS</div></td>
            </tr>				
        </table>";

        $no = 1;

        $export .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        foreach ($coremember as $key => $val) {
            $export .= "
                <tr>
                    <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                    <td width=\"12%\"><div style=\"text-align: left;\">".$val['member_no']."</div></td>
                    <td width=\"15%\"><div style=\"text-align: left;\">".$val['member_name']."</div></td>
                    <td width=\"20%\"><div style=\"text-align: left;\">".$val['member_address']."</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['member_principal_savings_last_balance'], 2)."</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['member_mandatory_savings_last_balance'], 2)."</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['member_special_savings_last_balance'], 2)."</div></td>
                </tr>
            ";

            $simp_pokok += $val['member_principal_savings_last_balance'];

            $simp_khs 	+= $val['member_special_savings_last_balance'];

            $simp_wjb 	+= $val['member_mandatory_savings_last_balance'];

            $no++;
        }

        $export .= "
        <tr>
            <td colspan =\"3\" style=\"border-top: 1px solid black;\"><div style=\"font-size:10;text-align:left;font-style:italic\"></div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Jumlah </div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($simp_pokok, 2)."</div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($simp_wjb, 2)."</div></td>
            <td style=\"border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($simp_khs, 2)."</div></td>
        </tr>							
        </table>";

        // //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Nominatif Anggota.pdf';
        $pdf::Output($filename, 'I');
    }

    public function export($sesi){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $spreadsheet        = new Spreadsheet();
        $preferencecompany	= PreferenceCompany::select('company_name')->first();

        if($branch_status == 1){
            if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
                $branch_id = '';
            } else {
                $branch_id = $sesi['branch_id'];
            }
        }
        
        $coremember = CoreMember::select('member_id', 'member_name', 'member_no', 'member_address', 'member_principal_savings_last_balance', 'member_special_savings_last_balance', 'member_mandatory_savings_last_balance')
        ->where('data_state', 0);
        if($sesi['member_character']){
            $coremember = $coremember->where('member_character', $sesi['member_character']);
        }
        if($branch_id != ''){
            $coremember = $coremember->where('branch_id', $branch_id);
        }
        $coremember = $coremember->orderBy('member_no', 'ASC')
        ->get();

        if(count($coremember)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan Nominatif Anggota")
                                            ->setSubject("")
                                            ->setDescription("Laporan Nominatif Anggota")
                                            ->setKeywords("Laporan Nominatif Anggota")
                                            ->setCategory("Laporan Nominatif Anggota");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan Nominatif Anggota");

            //Cell Management
            $spreadsheet->getActiveSheet()->mergeCells("B2:H2");
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);

            //Style Management
            $spreadsheet->getActiveSheet()->getStyle('B2:H4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B2')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B4:H4')->getFont()->setBold(true);
            
            //Cell Value
            $sheet->setCellValue('B2',"DAFTAR REGISTER ANGGOTA BIASA DAN ANGGOTA");	
            $sheet->setCellValue('B4',"No");
            $sheet->setCellValue('C4',"No. Anggota");
            $sheet->setCellValue('D4',"Nama Anggota");
            $sheet->setCellValue('E4',"Alamat");
            $sheet->setCellValue('F4',"Simpanan Pokok");
            $sheet->setCellValue('G4',"Simpanan Wajib");
            $sheet->setCellValue('H4',"Simpanan Khusus");

            $row = 4;
			$no  = 0;
				
			foreach($coremember as $key=>$val){
                $no++;
                $row++;
                
                 $sheet->setCellValue('B'.$row, $no);
                 $sheet->setCellValue('C'.$row,$val['member_no']);
                 $sheet->setCellValue('D'.$row, $val['member_name']);
                 $sheet->setCellValue('E'.$row, $val['member_address']);
                 $sheet->setCellValue('F'.$row, number_format($val['member_principal_savings_last_balance'],2));
                 $sheet->setCellValue('G'.$row, number_format($val['member_special_savings_last_balance'],2));
                 $sheet->setCellValue('H'.$row, number_format($val['member_mandatory_savings_last_balance'],2));
			}
            
            $spreadsheet->getActiveSheet()->getStyle('B4:B'.($row))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('F4:H'.($row))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('B4:H'.($row))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
            ob_clean();
            $filename='Laporan Nominatif Anggota.xls';
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
