<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctSavingsMemberDetail;
use App\Models\CoreBranch;
use App\Models\CoreMember;
use App\Models\PreferenceCompany;
use App\DataTables\CoreMemberPrintMutation\CoreMemberDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;

class CoreMemberPrintMutationController extends Controller
{
    public function index()
    {
        $coremember              = array();
        $acctsavingsmemberdetail = array();
        $sessiondata             = session()->get('data_printmutation');
        if(isset($sessiondata['member_id'])){
            $coremember = CoreMember::select('member_id', 'member_no', 'member_name', 'member_address')
            ->where('member_id', $sessiondata['member_id'])
            ->first();
        }
        if($sessiondata){
            $acctsavingsmemberdetail = AcctSavingsMemberDetail::select('acct_savings_member_detail.savings_member_detail_id', 'acct_savings_member_detail.member_id', 'core_member.member_no', 'acct_savings_member_detail.branch_id', 'acct_savings_member_detail.mutation_id', 'acct_mutation.mutation_code', 'acct_savings_member_detail.transaction_date', 'acct_savings_member_detail.principal_savings_amount', 'acct_savings_member_detail.special_savings_amount', 'acct_savings_member_detail.mandatory_savings_amount', 'acct_savings_member_detail.last_balance', 'acct_savings_member_detail.operated_name', 'core_member.member_identity_no')
            ->join('core_member', 'acct_savings_member_detail.member_id', '=', 'core_member.member_id')
            ->join('acct_mutation', 'acct_savings_member_detail.mutation_id', '=', 'acct_mutation.mutation_id')
            ->where('acct_savings_member_detail.savings_print_status', 0);
            if(isset($sessiondata['member_id'])){
                $acctsavingsmemberdetail = $acctsavingsmemberdetail->where('acct_savings_member_detail.member_id', $sessiondata['member_id']);
            }
            if(isset($sessiondata['start_date'])){
                $acctsavingsmemberdetail = $acctsavingsmemberdetail->where('acct_savings_member_detail.transaction_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])));
            }
            if(isset($sessiondata['end_date'])){
                $acctsavingsmemberdetail = $acctsavingsmemberdetail->where('acct_savings_member_detail.transaction_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])));
            }
            $acctsavingsmemberdetail = $acctsavingsmemberdetail->get();
        }

        return view('content.CoreMemberPrintMutation.index', compact('coremember', 'acctsavingsmemberdetail', 'sessiondata'));
    }

    public function modalCoreMember(CoreMemberDataTable $dataTable)
    {
        return $dataTable->render('content.CoreMemberPrintMutation.CoreMemberModal.index');
    }

    public function selectCoreMember($member_id)
    {
        $sessiondata = session()->get('data_printmutation');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['start_date']  = date('d-m-Y');
            $sessiondata['end_date']    = date('d-m-Y');
            $sessiondata['member_id']   = '';
        }
        $sessiondata['member_id'] = $member_id;
        session()->put('data_printmutation', $sessiondata);

        return redirect('member-print-mutation');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_printmutation');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['start_date']  = date('d-m-Y');
            $sessiondata['end_date']    = date('d-m-Y');
            $sessiondata['member_id']   = '';
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_printmutation', $sessiondata);
    }

    public function reset()
    {
        session()->forget('data_printmutation');

        return redirect('member-print-mutation');
    }

    public function changeDate(Request $request)
    {
        $sessiondata = session()->get('data_printmutation');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['start_date']  = date('d-m-Y');
            $sessiondata['end_date']    = date('d-m-Y');
            $sessiondata['member_id']   = '';
        }
        $sessiondata['start_date']  = $request->start_date;
        $sessiondata['end_date']    = $request->end_date;
        session()->put('data_printmutation', $sessiondata);

        return redirect('member-print-mutation');
    }

    public function processPrinting(Request $request){
        
        $preferencecompany	= PreferenceCompany::select('logo_koperasi')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $sessiondata        = session()->get('data_printmutation');
        $data               = array();
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['start_date']  = date('d-m-Y');
            $sessiondata['end_date']    = date('d-m-Y');
            $sessiondata['member_id']   = '';
        }
        
        
        $mutasicoremember = AcctSavingsMemberDetail::select('acct_savings_member_detail.savings_member_detail_id', 'acct_savings_member_detail.member_id', 'core_member.member_no', 'acct_savings_member_detail.branch_id', 'acct_savings_member_detail.mutation_id', 'acct_mutation.mutation_code', 'acct_savings_member_detail.transaction_date', 'acct_savings_member_detail.principal_savings_amount', 'acct_savings_member_detail.special_savings_amount', 'acct_savings_member_detail.mandatory_savings_amount', 'acct_savings_member_detail.last_balance', 'acct_savings_member_detail.operated_name', 'core_member.member_identity_no')
        ->join('core_member', 'acct_savings_member_detail.member_id', '=', 'core_member.member_id')
        ->join('acct_mutation', 'acct_savings_member_detail.mutation_id', '=', 'acct_mutation.mutation_id')
        ->where('acct_savings_member_detail.transaction_date', '>=', date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_savings_member_detail.transaction_date', '<=', date('Y-m-d', strtotime($sessiondata['end_date'])))
        ->where('acct_savings_member_detail.member_id', $sessiondata['member_id'])
        ->where('acct_savings_member_detail.savings_print_status', 0)
        ->get();

        $member_last_number 	= CoreMember::select('member_last_number')
        ->where('member_id', $sessiondata['member_id'])
        ->first()
        ->member_last_number;

        if(empty($member_last_number) || $member_last_number == 0){
            $no = 1;
        } else {
            $no = $member_last_number + 1;
        }

        foreach ($mutasicoremember as $key => $val) {
            if($no == 31){
                $no = 1;
            } else {
                $no = $no;
            }

            $data[] = array (
                'no'							=> $no,
                'savings_member_detail_id'		=> $val['savings_member_detail_id'],
                'member_id'						=> $val['member_id'],
                'transaction_date'				=> $val['transaction_date'],
                'transaction_code'				=> $val['mutation_code'],
                'principal_savings_amount'		=> $val['principal_savings_amount'],
                'special_savings_amount'		=> $val['special_savings_amount'],
                'mandatory_savings_amount'		=> $val['mandatory_savings_amount'],
                'last_balance'					=> $val['last_balance'],
                'operated_name'					=> $val['operated_name'],	
            );
            $no++;
        }

        if($request->view == 'print'){
            foreach ($data as $k => $v) {
                $savingsmemberdetail = AcctSavingsMemberDetail::where('savings_member_detail_id', $v['savings_member_detail_id'])
                ->first();
                $savingsmemberdetail->savings_print_status = 1;
                $savingsmemberdetail->save();

                $coremember = CoreMember::where('member_id', $v['member_id'])
                ->first();
                $coremember->member_last_number = $v['no'];
                $coremember->save();
            }
        }
        
        $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 6, 6, 6);

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

        $export = "
        ";
        
        $export .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
        if($member_last_number > 0){
            for ($i=1; $i <= $member_last_number ; $i++) { 
                if($i == 15){
                    $export .= "
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                    ";
                } else {
                    $export .= "
                    <tr>
                        <td></td>
                    </tr>";
                }
            }
        }
        foreach ($data as $key => $val) { 
            if($val['no'] == 1){
                $export .= "
                <tr>
                    <td width=\"4%\"><div style=\"text-align: center;\">No</div></td>
                    <td width=\"10%\"><div style=\"text-align: center;\">Tanggal</div></td>
                    <td width=\"9%\"><div style=\"text-align: center;\">Sandi</div></td>
                    <td width=\"12%\"><div style=\"text-align: center;\">S.Pokok</div></td>
                    <td width=\"13%\"><div style=\"text-align: center;\">S.Khusus</div></td>
                    <td width=\"12%\"><div style=\"text-align: center;\">S Wajib</div></td>
                    <td width=\"12%\"><div style=\"text-align: center;\">Saldo</div></td>
                    <td width=\"5%\"><div style=\"text-align: center;\">Opt</div></td>
                </tr>";
            }

            $export .= "
            <tr>
                <td width=\"3%\"><div style=\"text-align: left;\">".$val['no'].".</div></td>
                <td width=\"10%\"><div style=\"text-align: center;\">".date('d-m-y',strtotime(($val['transaction_date'])))."</div></td>
                <td width=\"9%\"><div style=\"text-align: center;\">".$val['transaction_code']."</div></td>
                <td width=\"12%\"><div style=\"text-align: right;\">".number_format($val['principal_savings_amount'])." &nbsp;</div></td>
                <td width=\"13%\"><div style=\"text-align: right;\">".number_format($val['special_savings_amount'])." &nbsp;</div></td>
                <td width=\"13%\"><div style=\"text-align: right;\">".number_format($val['mandatory_savings_amount'])." &nbsp;</div></td>
                <td width=\"12%\"><div style=\"text-align: right;\">".number_format($val['last_balance'])." &nbsp;</div></td>
                <td width=\"5%\"><div style=\"text-align: center;\">".substr($val['operated_name'],0,3)."</div></td>
            </tr>";

            if($val['no'] == 15){
                $export .= "
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>";
            }

            if($val['no'] == 30){
                $export .= "
                <tr>
                    <td></td>
                </tr>";
            }
        }
        $export .= "</table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Mutasi Anggota.pdf';
        $pdf::Output($filename, 'I');
    }
}
