<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\CoreBranch;
use App\Models\PreferenceCompany;
use App\DataTables\AcctSavingsAccountCoverDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctSavingsAccountCoverController extends Controller
{
    public function index(AcctSavingsAccountCoverDataTable $dataTable)
    {
        $sessiondata = session()->get('filter_savingsaccountcover');

        $branch_id          = auth()->user()->branch_id;
        $corebranch         = CoreBranch::flt()->get()->pluck('branch_name','branch_id');

        $acctsavings = AcctSavings::select('savings_id', 'savings_name')
        ->where('savings_status', 0)
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctSavingsAccountCover.List.index', compact('corebranch', 'acctsavings', 'sessiondata'));
    }

    public function filter(Request $request){
        if($request->savings_id){
            $savings_id = $request->savings_id;
        }else{
            $savings_id = null;
        }

        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = null;
        }

        $sessiondata = array(
            'savings_id' => $savings_id,
            'branch_id'  => $branch_id
        );

        session()->put('filter_savingsaccountcover', $sessiondata);

        return redirect('savings-account-cover');
    }

    public function filterReset(){
        session()->forget('filter_savingsaccountcover');

        return redirect('savings-account-cover');
    }

    public function processPrinting($savings_account_id){
        $preferencecompany  = PreferenceCompany::first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $branch_name        = CoreBranch::select('branch_name')
        ->where('branch_id', auth()->user()->branch_id)
        ->first()
        ->branch_name;

        $acctsavingsaccount = AcctSavingsAccount::withoutGlobalScopes()
        ->select('acct_savings_account.savings_account_id', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member.member_gender', 'core_member.member_address', 'core_member.member_phone', 'core_member.member_date_of_birth', 'core_member.member_identity_no', 'core_member.city_id', 'core_member.kecamatan_id', 'core_member.identity_id','core_member.branch_id', 'core_member.member_job', 'acct_savings_account.savings_id', 'acct_savings.savings_code', 'acct_savings.savings_name', 'acct_savings_account.savings_account_no', 'acct_savings_account.savings_account_date', 'acct_savings_account.savings_account_first_deposit_amount', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.voided_remark', 'acct_savings_account.validation', 'acct_savings_account.validation_at', 'acct_savings_account.validation_id', 'acct_savings_account.office_id')
        ->join('core_member', 'acct_savings_account.member_id', '=', 'core_member.member_id')
        ->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
        ->where('acct_savings_account.savings_account_id', $savings_account_id)
        ->where('acct_savings_account.data_state', 0)
        ->first();

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

        $pdf::SetFont('helvetica', '', 9);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }
        if($acctsavingsaccount['member_character']==null){$acctsavingsaccount['member_character']=0;}
        $export = "";
		$MemberCharacter = array (9 => ' ', 2 => 'Pendiri', 0 => 'Biasa', 1 => 'Luar Biasa');
        $export .= "
        <br>
			<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
			    <tr>
			        <td width=\"3%\"></td>
			        <td width=\"12%\"><div style=\"text-align: left;\">No. Anggota</div></td>
			        <td width=\"40%\"><div style=\"text-align: left;\">: ".$acctsavingsaccount['member_no']."</div></td>
			    </tr>
			    <tr>
			        <td width=\"3%\"></td>
			        <td width=\"12%\"><div style=\"text-align: left;\">Keanggotaan</div></td>
			        <td width=\"40%\"><div style=\"text-align: left;\">: ".$MemberCharacter[$acctsavingsaccount['member_character']]."</div></td>
			    </tr>
			    <tr>
			        <td width=\"3%\"></td>
			        <td width=\"12%\"><div style=\"text-align: left;\">Nama</div></td>
			        <td width=\"40%\"><div style=\"text-align: left;\">: ".$acctsavingsaccount['member_name']."</div></td>
			    </tr>
			     <tr>
			        <td width=\"3%\"></td>
			        <td width=\"12%\"><div style=\"text-align: left;\">Alamat</div></td>
			        <td width=\"40%\"><div style=\"text-align: left;\">: ".$acctsavingsaccount['member_address']."</div></td>
			    </tr>
			     <tr>
			        <td width=\"3%\"></td>
			        <td width=\"12%\"><div style=\"text-align: left;\">No. Identitas</div></td>
			        <td width=\"40%\"><div style=\"text-align: left;\">: ".$acctsavingsaccount['member_identity_no']."</div></td>
			    </tr>				
			</table>";

        // //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Cover Buku '.$acctsavingsaccount['member_name'].'.pdf';
        $pdf::Output($filename, 'I');
    }
}
