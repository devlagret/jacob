<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAgunan;
use App\Models\AcctSavingsAccount;
use App\Models\CoreBranch;
use App\Models\PreferenceCompany;
use App\DataTables\AcctCreditsAgunanDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AcctCreditsAgunanController extends Controller
{
    public function index(AcctCreditsAgunanDataTable $dataTable)
    {
        $sessiondata = session()->get('filter_creditagunanmaster');

        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctCreditsAgunan.List.index', compact('corebranch', 'sessiondata'));
    }

    public function filter(Request $request){
        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = null;
        }

        $sessiondata = array(
            'branch_id'     => $branch_id
        );

        session()->put('filter_creditagunanmaster', $sessiondata);

        return redirect('credits-agunan');
    }

    public function filterReset(){
        session()->forget('filter_creditagunanmaster');

        return redirect('credits-agunan');
    }

    public function updateStatus($credits_agunan_id){

        $creditsagunan = AcctCreditsAgunan::findOrFail($credits_agunan_id);
        $creditsagunan->credits_agunan_status = 1;
        if($creditsagunan->save()){
            $message = array(
                'pesan' => 'Update status agunan berhasil diproses',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Update status agunan gagal diproses',
                'alert' => 'error'
            );
        }

        return redirect('credits-agunan')->with($message);
    }

    public function printReceipt($credits_agunan_id){
        $preferencecompany	= PreferenceCompany::first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);

        $agunandetail		= AcctCreditsAgunan::select('acct_credits_agunan.*', 'acct_credits_account.credits_id', 'acct_credits_account.credits_account_date', 'acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_identity_no', 'core_member_working.member_company_job_title', 'core_member.member_address', 'core_member.member_phone')
        ->join('acct_credits_account','acct_credits_agunan.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->join('core_member','acct_credits_account.member_id', '=', 'core_member.member_id')
        ->join('core_member_working','core_member_working.member_id', '=', 'core_member.member_id')
        ->where('acct_credits_agunan.credits_agunan_id', $credits_agunan_id)
        ->where('acct_credits_agunan.data_state', 0)
        ->first();

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
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"100%\"><div style=\"text-align: center; font-size:14px; font-weight:bold\">TANDA TERIMA JAMINAN</div></td>
            </tr>
        </table>
        <br>
        <br>
        <br>";

        $export .= "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"100%\"><div style=\"text-align: left; font-size:12px;\">Telah Diterima barang jaminan dari :</div></td>
            </tr>
            <tr>
                <td style=\"text-align:left;\" width=\"5%\"></td>
                <td style=\"text-align:left;\" width=\"15%\">
                    <div style=\"font-size:12px;\">Nama</div>
                </td>
                <td style=\"text-align:left;\" width=\"2%\">
                    <div style=\"font-size:12px;\">:</div>
                </td>
                <td style=\"text-align:left;\" width=\"80%\">
                    <div style=\"font-size:12px;\">".$agunandetail['member_name']."</div>
                </td>
            </tr>
            <tr>
                <td style=\"text-align:left;\" width=\"5%\"></td>
                <td style=\"text-align:left;\" width=\"15%\">
                    <div style=\"font-size:12px;\">No. KTP</div>
                </td>
                <td style=\"text-align:left;\" width=\"2%\">
                    <div style=\"font-size:12px;\">:</div>
                </td>
                <td style=\"text-align:left;\" width=\"80%\">
                    <div style=\"font-size:12px;\">".$agunandetail['member_identity_no']."</div>
                </td>
            </tr>
            <tr>
                <td style=\"text-align:left;\" width=\"5%\"></td>
                <td style=\"text-align:left;\" width=\"15%\">
                    <div style=\"font-size:12px;\">Pekerjaan</div>
                </td>
                <td style=\"text-align:left;\" width=\"2%\">
                    <div style=\"font-size:12px;\">:</div>
                </td>
                <td style=\"text-align:left;\" width=\"80%\">
                    <div style=\"font-size:12px;\">".$agunandetail['member_company_job_title']."</div>
                </td>
            </tr>
            <tr>
                <td style=\"text-align:left;\" width=\"5%\"></td>
                <td style=\"text-align:left;\" width=\"15%\">
                    <div style=\"font-size:12px;\">Alamat</div>
                </td>
                <td style=\"text-align:left;\" width=\"2%\">
                    <div style=\"font-size:12px;\">:</div>
                </td>
                <td style=\"text-align:left;\" width=\"80%\">
                    <div style=\"font-size:12px;\">".$agunandetail['member_address']."</div>
                </td>
            </tr>
            <tr>
                <td style=\"text-align:left;\" width=\"5%\"></td>
                <td style=\"text-align:left;\" width=\"15%\">
                    <div style=\"font-size:12px;\">No. Telepon</div>
                </td>
                <td style=\"text-align:left;\" width=\"2%\">
                    <div style=\"font-size:12px;\">:</div>
                </td>
                <td style=\"text-align:left;\" width=\"80%\">
                    <div style=\"font-size:12px;\">".$agunandetail['member_phone']."</div>
                </td>
            </tr>
        </table>
        <br>";

        if($agunandetail['credits_id'] == 17){
            $export .="
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td width=\"100%\"><div style=\"text-align: left; font-size:12px;\">Jaminan Berupa ATM Asli dan Buku Tabungan dengan data sebagai berikut :</div></td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"20%\">
                        <div style=\"font-size:12px;\">Nomor ATM</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_atmjamsostek_nomor']."</div>
                    </td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"20%\">
                        <div style=\"font-size:12px;\">No. Rekening Tabungan</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_atmjamsostek_keterangan']."</div>
                    </td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"20%\">
                        <div style=\"font-size:12px;\">Nama Bank</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_atmjamsostek_bank']."</div>
                    </td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"20%\">
                        <div style=\"font-size:12px;\">Atas Nama</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_atmjamsostek_nama']."</div>
                    </td>
                </tr>
            </table>
            <br>";
        }else{
            $export .="
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td width=\"100%\"><div style=\"text-align: left; font-size:12px;\">Jaminan BPKB dengan data sebagai berikut :</div></td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"15%\">
                        <div style=\"font-size:12px;\">No. BPKB</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_bpkb_nomor']."</div>
                    </td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"15%\">
                        <div style=\"font-size:12px;\">No. Polisi</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_bpkb_nopol']."</div>
                    </td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"15%\">
                        <div style=\"font-size:12px;\">Nomor Rangka</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_bpkb_no_rangka']."</div>
                    </td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"15%\">
                        <div style=\"font-size:12px;\">Nomor Mesin</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_bpkb_no_mesin']."</div>
                    </td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"15%\">
                        <div style=\"font-size:12px;\">Merk / Type</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_bpkb_type']."</div>
                    </td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"15%\">
                        <div style=\"font-size:12px;\">Tahun / Warna</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_bpkb_keterangan']."</div>
                    </td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"15%\">
                        <div style=\"font-size:12px;\">A/N Nama</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_bpkb_nama']."</div>
                    </td>
                </tr>
                <tr>
                    <td style=\"text-align:right;\" width=\"5%\">-</td>
                    <td style=\"text-align:left;\" width=\"15%\">
                        <div style=\"font-size:12px;\">Alamat</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"2%\">
                        <div style=\"font-size:12px;\">:</div>
                    </td>
                    <td style=\"text-align:left;\" width=\"80%\">
                        <div style=\"font-size:12px;\">".$agunandetail['credits_agunan_bpkb_address']."</div>
                    </td>
                </tr>";
                if($agunandetail['credits_id'] == 13){
                    $export .="
                    <tr>
                        <td style=\"text-align:right;\" width=\"5%\">-</td>
                        <td style=\"text-align:left;\" width=\"95%\">
                            <div style=\"font-size:12px;\"><b>BPKB Baru dalam Proses Pembuatan Dealer ......................, dan setelah selesai akan diberikan ke pihak KSU \"Mandiri Sejahtera\"</b></div>
                        </td>
                    </tr>
                    ";
                }
                $export .="
                </table>
                <br>";
        }
        $export .="
        <div style=\"font-size:12px;\"><b>Dan akan dikembalikan setelah pinjaman lunas.</b><div>
        <div style=\"font-size:12px;\">Karanganyar, ".$agunandetail['credits_account_date']."<div>

        <table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td style=\"text-align:center;\" width=\"50%\" height=\"100px\">
                    <div style=\"font-size:12px;\">
                        Yang Menyerahkan</div>
                </td>
                <td style=\"text-align:center;\" width=\"50%\" height=\"100px\">
                    <div style=\"font-size:12px;\">
                        Yang Menerima</div>
                </td>
                </tr>
                <tr>
                    <td style=\"text-align:center;\" width=\"50%\">
                        <div style=\"font-size:12px;\">
                            ".$agunandetail['member_name']."</div>
                    </td>
                <td style=\"text-align:center;\" width=\"50%\">
                    <div style=\"font-size:12px;\"><u>..............................</u></div>
                </td>
                </tr>
                <tr>
                    <td style=\"text-align:center;\" width=\"25%\">
                    </td>
                <td style=\"text-align:center;\" width=\"50%\" height=\"100px\">
                    <div style=\"font-size:12px;\">Mengetahui</div>
                </td>
                <td style=\"text-align:center;\" width=\"25%\">
                </td>
                </tr>
                <tr>
                    <td style=\"text-align:center;\" width=\"25%\">
                    </td>
                <td style=\"text-align:center;\" width=\"50%\">
                    <u>..............................</u><br>
                    Pimpinan Cabang
                </td>
                <td style=\"text-align:center;\" width=\"25%\">
                </td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Tanda Terima Jaminan.pdf';
        $pdf::Output($filename, 'I');
    }

    public function export(){
        $spreadsheet        = new Spreadsheet();
        $preferencecompany	= PreferenceCompany::select('company_name')->first();

		if (auth()->user()->branch_status == 1) {
            $sessiondata = session()->get('filter_creditagunanmaster');
			if (!$sessiondata) {
				$branch_id  = '';
			}else{
                $branch_id = $sessiondata['branch_id'];
            }
		} else {
			$branch_id	= auth()->user()->branch_id;
		}

        $acctcreditsagunan	= AcctCreditsAgunan::select('acct_credits_agunan.*', 'acct_credits_account.credits_account_serial', 'acct_credits_account.member_id', 'core_member.member_name')
        ->join('acct_credits_account','acct_credits_agunan.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->join('core_member','acct_credits_account.member_id', '=', 'core_member.member_id')
        ->where('acct_credits_account.data_state', 0);
        if($branch_id != ''){
            $acctcreditsagunan	= $acctcreditsagunan->where('acct_credits_account.branch_id', $branch_id);
        }
        $acctcreditsagunan	= $acctcreditsagunan->get();

        $agunanstatus = Configuration::AgunanStatus();

        if(count($acctcreditsagunan)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Master Data Agunan")
                                            ->setSubject("")
                                            ->setDescription("Master Data Agunan")
                                            ->setKeywords("Master Data Agunan")
                                            ->setCategory("Master Data Agunan");

            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Master Data Agunan");

            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('X')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('Y')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('Z')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('AA')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('AB')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('AC')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('AD')->setWidth(20);

            $spreadsheet->getActiveSheet()->mergeCells("B1:AD1");

            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:AD3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:AD3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:AD3')->getFont()->setBold(true);

            $spreadsheet->getActiveSheet()->setCellValue('B1',"Master Data Agunan");
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"No. Akad");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Sertifikat");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Luas");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Atas Nama");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Kedudukan");
            $spreadsheet->getActiveSheet()->setCellValue('I3',"Taksiran");
            $spreadsheet->getActiveSheet()->setCellValue('J3',"BPKB");
            $spreadsheet->getActiveSheet()->setCellValue('K3',"Jenis");
            $spreadsheet->getActiveSheet()->setCellValue('L3',"Atas Nama");
            $spreadsheet->getActiveSheet()->setCellValue('M3',"Alamat");
            $spreadsheet->getActiveSheet()->setCellValue('N3',"No. Polisi");
            $spreadsheet->getActiveSheet()->setCellValue('O3',"No. Rangka");
            $spreadsheet->getActiveSheet()->setCellValue('P3',"No. Mesin");
            $spreadsheet->getActiveSheet()->setCellValue('Q3',"Nama Dealer");
            $spreadsheet->getActiveSheet()->setCellValue('R3',"Alamat Dealer");
            $spreadsheet->getActiveSheet()->setCellValue('S3',"Taksiran");
            $spreadsheet->getActiveSheet()->setCellValue('T3',"Uang Muka Gross");
            $spreadsheet->getActiveSheet()->setCellValue('U3',"Nomor (ATM / Jamsostek)");
            $spreadsheet->getActiveSheet()->setCellValue('V3',"Atas Nama (ATM / Jamsostek)");
            $spreadsheet->getActiveSheet()->setCellValue('W3',"Nama Bank (ATM / Jamsostek)");
            $spreadsheet->getActiveSheet()->setCellValue('X3',"Taksiran (ATM / Jamsostek)");
            $spreadsheet->getActiveSheet()->setCellValue('Y3',"Keterangan (ATM / Jamsostek)");
            $spreadsheet->getActiveSheet()->setCellValue('Z3',"Deskripsi Bilyet Simpanan Berjangka");
            $spreadsheet->getActiveSheet()->setCellValue('AA3',"Deskripsi Elektro");
            $spreadsheet->getActiveSheet()->setCellValue('AB3',"Deskripsi Dana Keanggotaan");
            $spreadsheet->getActiveSheet()->setCellValue('AC3',"Deskripsi Tabungan");
            $spreadsheet->getActiveSheet()->setCellValue('AD3',"Status");

            $row    = 4;
            $no     = 0;
            foreach($acctcreditsagunan as $key=>$val){
                $no++;

                $spreadsheet->getActiveSheet()->getStyle('B'.$row.':AD'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('C'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('D'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('E'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('F'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('G'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('H'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('I'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('J'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('K'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('L'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('M'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('N'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('O'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('P'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('Q'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('R'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('S'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('T'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('U'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('V'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('W'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('X'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('Y'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('Z'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('AA'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('AB'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('AC'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('AD'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);


                $spreadsheet->getActiveSheet()->setCellValue('B'.$row, $no);
                $spreadsheet->getActiveSheet()->setCellValue('C'.$row, $val['credits_account_serial']);
                $spreadsheet->getActiveSheet()->setCellValue('D'.$row, $val['member_name']);
                $spreadsheet->getActiveSheet()->setCellValue('E'.$row, $val['credits_agunan_shm_no_sertifikat']);
                $spreadsheet->getActiveSheet()->setCellValue('F'.$row, $val['credits_agunan_shm_luas']);
                $spreadsheet->getActiveSheet()->setCellValue('G'.$row, $val['credits_agunan_shm_atas_nama']);
                $spreadsheet->getActiveSheet()->setCellValue('H'.$row, $val['credits_agunan_shm_kedudukan']);
                $spreadsheet->getActiveSheet()->setCellValue('I'.$row, number_format((float)$val['credits_agunan_shm_taksiran'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('J'.$row, $val['credits_agunan_bpkb_nomor']);
                $spreadsheet->getActiveSheet()->setCellValue('K'.$row, $val['credits_agunan_bpkb_type']);
                $spreadsheet->getActiveSheet()->setCellValue('L'.$row, $val['credits_agunan_bpkb_nama']);
                $spreadsheet->getActiveSheet()->setCellValue('M'.$row, $val['credits_agunan_bpkb_address']);
                $spreadsheet->getActiveSheet()->setCellValue('N'.$row, $val['credits_agunan_bpkb_nopol']);
                $spreadsheet->getActiveSheet()->setCellValue('O'.$row, $val['credits_agunan_bpkb_no_rangka']);
                $spreadsheet->getActiveSheet()->setCellValue('P'.$row, $val['credits_agunan_bpkb_no_mesin']);
                $spreadsheet->getActiveSheet()->setCellValue('Q'.$row, $val['credits_agunan_bpkb_dealer_name']);
                $spreadsheet->getActiveSheet()->setCellValue('R'.$row, $val['credits_agunan_bpkb_dealer_address']);
                $spreadsheet->getActiveSheet()->setCellValue('S'.$row, number_format((float)$val['credits_agunan_bpkb_taksiran'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('T'.$row, number_format((float)$val['credits_agunan_bpkb_gross'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('U'.$row, $val['credits_agunan_atmjamsostek_nomor']);
                $spreadsheet->getActiveSheet()->setCellValue('V'.$row, $val['credits_agunan_atmjamsostek_nama']);
                $spreadsheet->getActiveSheet()->setCellValue('W'.$row, $val['credits_agunan_atmjamsostek_bank']);
                $spreadsheet->getActiveSheet()->setCellValue('X'.$row, number_format((float)$val['credits_agunan_atmjamsostek_taksiran'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('Y'.$row, $val['credits_agunan_atmjamsostek_keterangan']);
                if($val['credits_agunan_type'] == 3){
                    $spreadsheet->getActiveSheet()->setCellValue('Z'.$row, $val['credits_agunan_other_keterangan']);
                }
                if($val['credits_agunan_type'] == 4){
                    $spreadsheet->getActiveSheet()->setCellValue('AA'.$row, $val['credits_agunan_other_keterangan']);
                }
                if($val['credits_agunan_type'] == 5){
                    $spreadsheet->getActiveSheet()->setCellValue('AB'.$row, $val['credits_agunan_other_keterangan']);
                }
                if($val['credits_agunan_type'] == 6){
                    $spreadsheet->getActiveSheet()->setCellValue('AC'.$row, $val['credits_agunan_other_keterangan']);
                }
                $spreadsheet->getActiveSheet()->setCellValue('AD'.$row, $agunanstatus[$val['credits_agunan_status']]);
                $row++;
            }

            ob_clean();
            $filename='Master Data Agunan.xls';
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
