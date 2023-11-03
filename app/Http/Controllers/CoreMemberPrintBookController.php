<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\CoreCity;
use App\Models\CoreKecamatan;
use App\Models\CoreKelurahan;
use App\Models\CoreProvince;
use App\Models\CoreMember;
use App\Models\CoreMemberWorking;
use App\Models\PreferenceCompany;
use App\DataTables\CoreMemberPrintBookDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Str;

class CoreMemberPrintBookController extends Controller
{
    public function index(CoreMemberPrintBookDataTable $dataTable)
    {
        return $dataTable->render('content.CoreMemberPrintBook.List.index');
    }

    public function processPrinting($member_id)
    {
        $membercharacter            = Configuration::MemberCharacter();
        $preferencecompany	        = PreferenceCompany::select('logo_koperasi')->first();
        $path                       = public_path('storage/'.$preferencecompany['logo_koperasi']);

        $coremember = CoreMember::with('branch')->find($member_id);


        $pdf = new TCPDF(['P', PDF_UNIT, 'A4', true, 'UTF-8', false]);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(5, 4, 1);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);
        $pdf::AddPage('P','A4');

        $pdf::SetFont('helvetica', '', 9);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }
        // <br></br>
        // <div style=\"text-align:center;\">
        //     <br/>
        //     <br/>
        //     <br/>
        // </div>
        $export = "
       ";

        $export .= "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"3%\"></td>
                <td width=\"11.6%\"><div style=\"text-align: left;\">No. Anggota</div></td>
                <td width=\"40%\"><div style=\"text-align: left;\">: ".$coremember['member_no']."</div></td>
            </tr>
            <tr>
                <td width=\"3%\"></td>
                <td width=\"11.6%\"><div style=\"text-align: left;\">Keanggotaan</div></td>
                <td width=\"40%\"><div style=\"text-align: left;\">: ".$membercharacter[$coremember['member_character']]."</div></td>
            </tr>
            <tr>
                <td width=\"3%\"></td>
                <td width=\"11.6%\"><div style=\"text-align: left;\">Nama</div></td>
                <td width=\"40%\"><div style=\"text-align: left;\">: ".$coremember['member_name']."</div></td>
            </tr>
            <tr>
                <td width=\"3%\"></td>
                <td width=\"11.6%\"><div style=\"text-align: left;\">Alamat</div></td>
                <td width=\"40%\"><div style=\"text-align: left;\">: ".$coremember['member_address']."</div></td>
            </tr>
            <tr>
                <td width=\"3%\"></td>
                <td width=\"11.6%\"><div style=\"text-align: left;\">No. Identitas</div></td>
                <td width=\"40%\"><div style=\"text-align: left;\">: ".$coremember['member_identity_no']."</div></td>
            </tr>				
        </table>";
        $pdf::setCellHeightRatio(1.25);
        // //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');
        $pdf::setCellHeightRatio(1);

        $filename = 'Cover Buku '.$coremember['member_name'].'.pdf';
        $pdf::setTitle(Str::title($filename));
        $pdf::Output($filename, 'I');

    }
}
