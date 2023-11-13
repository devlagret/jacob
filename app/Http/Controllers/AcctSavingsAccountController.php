<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\CoreBranch;
use App\Models\CoreCity;
use App\Models\CoreKecamatan;
use App\Models\CoreMember;
use App\Models\CoreOffice;
use App\Models\PreferenceCompany;
use App\Models\PreferenceInventory;
use App\Models\PreferenceTransactionModule;
use App\DataTables\AcctSavingsAccount\AcctSavingsAccountDataTable;
use App\DataTables\AcctSavingsAccount\CoreMemberDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctSavingsAccountController extends Controller
{
    public function index(AcctSavingsAccountDataTable $dataTable)
    {
        session()->forget('data_savingsaccountadd');
        $sessiondata = session()->get('filter_savingsaccount');
        // dump($sessiondata);
        $corebranch = CoreBranch::select('branch_id', 'branch_name');
        if(Auth::user()->branch_id!==0){
            $corebranch->where('branch_id',Auth::user()->branch_id);
        }
        $corebranch = $corebranch->get();

        $acctsavings = AcctSavings::select('savings_id', 'savings_name')
        ->where('savings_status', 0)
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctSavingsAccount.List.index', compact('corebranch', 'acctsavings', 'sessiondata'));
    }

    public function filter(Request $request){
        $sessiondata = array(
            'savings_id' => $request->savings_id,
            'branch_id'  => $request->branch_id
        );

        session()->put('filter_savingsaccount', $sessiondata);

        return redirect('savings-account');
    }

    public function filterReset(){
        session()->forget('filter_savingsaccount');

        return redirect('savings-account');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_savingsaccountadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['savings_id']                              = null;
            $sessiondata['savings_interest_rate']                   = 0;
            $sessiondata['office_id']                               = null;
            $sessiondata['savings_account_first_deposit_amount']    = 0;
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_savingsaccountadd', $sessiondata);
    }

    public function add()
    {
        $config                 = theme()->getOption('page', 'view');
        $sessiondata            = session()->get('data_savingsaccountadd');
        $membergender           = array_filter(Configuration::MemberGender());
        $memberidentity         = array_filter(Configuration::MemberIdentity());
        $familyrelationship     = array_filter(Configuration::FamilyRelationship());

        $acctsavings            = AcctSavings::select('savings_id', 'savings_name')
        ->where('savings_status', 0)
        ->where('data_state', 0)
        ->get();

        $coreoffice             = CoreOffice::select('office_id', 'office_name')
        ->where('data_state', 0)
        ->get();

        $coremember             = array();
        if(isset($sessiondata['member_id'])){
            $coremember = CoreMember::with('kecamatan','city')
            ->find($sessiondata['member_id']);
        }

        return view('content.AcctSavingsAccount.Add.index', compact('sessiondata', 'membergender', 'memberidentity', 'familyrelationship', 'acctsavings', 'coreoffice', 'coremember'));
    }

    public function modalCoreMember(CoreMemberDataTable $dataTable)
    {
        return $dataTable->render('content.AcctSavingsAccount.Add.CoreMemberModal.index');
    }

    public function selectCoreMember($member_id)
    {
        $sessiondata = session()->get('data_savingsaccountadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['savings_id']                              = null;
            $sessiondata['savings_interest_rate']                   = 0;
            $sessiondata['office_id']                               = null;
            $sessiondata['savings_account_first_deposit_amount']    = 0;
        }
        $sessiondata['member_id'] = $member_id;
        session()->put('data_savingsaccountadd', $sessiondata);

        return redirect('savings-account/add');
    }

    public function processAdd(Request $request)
    {


        $preferencecompany = PreferenceCompany::first();

        $fields = request()->validate([
            'member_id'                             =>['required'],
            'savings_id'                            =>['required'],
            'savings_account_date'                  =>['required'],
            'office_id'                             =>['required'],
            'savings_account_first_deposit_amount'  =>['required'],
        ]);

		$pickup_date = date('Y-m-d', strtotime($request->savings_account_pickup_date));
        $period = $request->saving_account_period;
		if(!collect(['25','26','27'])->contains($fields['savings_id'])){
			$pickup_date = null;
            $period = null;
		}
        DB::beginTransaction();

        try {
            $data  = array(
                'member_id'                             => $fields['member_id'],
                'savings_id'                            => $fields['savings_id'],
                'office_id'                             => $fields['office_id'],
                'savings_account_first_deposit_amount'  => $fields['savings_account_first_deposit_amount'],
                'savings_account_last_balance'          => $fields['savings_account_first_deposit_amount'],
                'savings_account_date'                  => date('Y-m-d', strtotime($fields['savings_account_date'])),
                'savings_account_adm_amount'		    => $preferencecompany['savings_account_administration'],
                'savings_member_heir'                   => $request->savings_member_heir,
                'savings_member_heir_address'           => $request->savings_member_heir_address,
                'savings_member_heir_relationship'      => $request->savings_member_heir_relationship,
                'branch_id'                             => auth()->user()->branch_id,
                'operated_name'                         => auth()->user()->username,
                'created_id'                            => auth()->user()->user_id,
            );
            // $minSaving = AcctSavings::find($fields['savings_id'])->minimum_first_deposit_amount;
            // if($fields['savings_account_first_deposit_amount']<$minSaving&&$minSaving!=0){
            //     $message = array(
            //         'pesan' => 'Setoran awal minimal Rp'.number_format($minSaving,2),
            //         'alert' => 'error'
            //     );
            //     $sessiondata = session()->get('data_savingsaccountadd');
            //     if(!$sessiondata || $sessiondata == ""){
            //         $sessiondata['savings_id']                              = null;
            //         $sessiondata['savings_interest_rate']                   = 0;
            //         $sessiondata['office_id']                               = null;
            //         $sessiondata['savings_account_first_deposit_amount']    = 0;
            //     }
            //     $sessiondata['member_id'] = $fields['member_id'];
            //     session()->put('data_savingsaccountadd', $sessiondata);
            //     return redirect('savings-account/add')->with($message);
            // }
            AcctSavingsAccount::create($data);
            $transaction_module_code 	= "TAB";
			$transaction_module_id 		= PreferenceTransactionModule::select('transaction_module_id')
            ->where('transaction_module_code',$transaction_module_code)
            ->first()
            ->transaction_module_id;

            $acctsavingsaccount_last 	= AcctSavingsAccount::withoutGlobalScopes()
            ->select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name')
			->join('core_member','acct_savings_account.member_id', '=', 'core_member.member_id')
			->where('acct_savings_account.created_id', $data['created_id'])
			->whereDate('acct_savings_account.created_at', date('Y-m-d'))
			->orderBy('acct_savings_account.created_at','DESC')
            ->first();

            $journal_voucher_period = date("Ym", strtotime($data['savings_account_date']));

            $data_journal = array(
                'branch_id'						=> auth()->user()->branch_id,
                'journal_voucher_period' 		=> $journal_voucher_period,
                'journal_voucher_date'			=> date('Y-m-d'),
                'journal_voucher_title'			=> 'SETORAN TABUNGAN '.$acctsavingsaccount_last['member_name'],
                'journal_voucher_description'	=> 'SETORAN TABUNGAN '.$acctsavingsaccount_last['member_name'],
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $acctsavingsaccount_last['savings_account_id'],
                'transaction_journal_no' 		=> $acctsavingsaccount_last['savings_account_no'],
                'created_id' 					=> $data['created_id'],
                'created_at' 					=> date('Y-m-d'),
            );
            AcctJournalVoucher::create($data_journal);

            $journal_voucher_id = AcctJournalVoucher::select('journal_voucher_id')
			->where('acct_journal_voucher.created_id', $data['created_id'])
			->orderBy('acct_journal_voucher.journal_voucher_id', 'DESC')
            ->first()
            ->journal_voucher_id;

            $account_id_default_status = AcctAccount::select('account_default_status')
			->where('acct_account.account_id', $preferencecompany['account_cash_id'])
			->where('acct_account.data_state', 0)
            ->first()
            ->account_default_status;

            if($data['savings_account_first_deposit_amount'] > 0){
                $data_debet = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_cash_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> $data['savings_account_first_deposit_amount'],
                    'journal_voucher_debit_amount'	=> $data['savings_account_first_deposit_amount'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                    'created_id' 					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_debet);
            }

            $account_id = AcctSavings::select('account_id')
			->where('savings_id', $data['savings_id'])
			->where('data_state', 0)
            ->first()
            ->account_id;

            $account_id_default_status = AcctAccount::select('account_default_status')
			->where('acct_account.account_id', $account_id)
			->where('acct_account.data_state', 0)
            ->first()
            ->account_default_status;

            $data_credit =array(
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $account_id,
                'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                'journal_voucher_amount'		=> $data['savings_account_first_deposit_amount']-$preferencecompany['savings_account_administration'],
                'journal_voucher_credit_amount'	=> $data['savings_account_first_deposit_amount']-$preferencecompany['savings_account_administration'],
                'account_id_status'				=> 1,
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucherItem::create($data_credit);

            if($data['savings_account_adm_amount'] > 0){
                $preferenceinventory = PreferenceInventory::first();

                $account_id_default_status = AcctAccount::select('account_default_status')
                ->where('acct_account.account_id', $preferenceinventory['inventory_adm_id'])
                ->where('acct_account.data_state', 0)
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferenceinventory['inventory_adm_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> $preferencecompany['savings_account_administration'],
                    'journal_voucher_credit_amount'	=> $preferencecompany['savings_account_administration'],
                    'account_id_status'				=> 1,
                    'created_id' 					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
            }

            DB::commit();
            $message = array(
                'pesan' => 'Tabungan berhasil ditambah',
                'alert' => 'success'
            );
            return redirect('savings-account/print-note/'.$acctsavingsaccount_last->savings_account_id);
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Tabungan gagal ditambah',
                'alert' => 'error'
            );
            return redirect('savings-account')->with($message);
        }
    }

    public function printNote($savings_account_id)
    {
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $branch_city        = CoreBranch::select('branch_city')
        ->where('branch_id', auth()->user()->branch_id)
        ->first()
        ->branch_city;

        $acctsavingsaccount = AcctSavingsAccount::with('savingdata','member')
        ->where('savings_account_id', $savings_account_id)
        ->first();

        $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);
        $pdf::SetTitle('Kwitansi Tabungan');
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
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"25%\"></td>
                <td width=\"75%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN AWAL TABUNGAN</div></td>
            </tr>
            <tr>
                <td width=\"25%\"></td>
                <td width=\"75%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
            </tr>
        </table>
        <br>
        <br>
        <br>

        Telah diterima uang dari :
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingsaccount->member->member_name."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">No. Rekening</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingsaccount['savings_account_no']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingsaccount->member->member_address."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".Configuration::numtotxt($acctsavingsaccount['savings_account_first_deposit_amount'])."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: SETORAN AWAL TABUNGAN</div></td>
            </tr>
                <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctsavingsaccount['savings_account_first_deposit_amount'], 2)."</div></td>
            </tr>
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">".$branch_city.", ".date('d-m-Y')."</div></td>
            </tr>
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\">Penyetor</div></td>
                <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
            </tr>
        </table>";


        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');
        $filename = 'Kwitansi Tabungan.pdf';
        $pdf::Output($filename, 'I');
    }

    public function validation($savings_account_id){
        $savingsaccount                 = AcctSavingsAccount::findOrFail($savings_account_id);
        $savingsaccount->validation     = 1;
        $savingsaccount->validation_id  = auth()->user()->user_id;
        $savingsaccount->validation_at  = date('Y-m-d');
        if($savingsaccount->save()){
            $message = array(
                'pesan' => 'Tabungan berhasil divalidasi',
                'alert' => 'success'
            );

            return redirect('savings-account/print-validation/'.$savings_account_id);
        }else{
            $message = array(
                'pesan' => 'Tabungan gagal divalidasi',
                'alert' => 'error'
            );
            return redirect('savings-account')->with($message);
        }

    }

    public function printValidation($savings_account_id){
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $branch_city        = CoreBranch::select('branch_city')
        ->where('branch_id', auth()->user()->branch_id)
        ->first()
        ->branch_city;

        $acctsavingsaccount = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member.member_gender', 'core_member.member_address', 'core_member.member_phone', 'core_member.member_date_of_birth', 'core_member.member_identity_no', 'core_member.city_id', 'core_member.kecamatan_id', 'core_member.identity_id','core_member.branch_id', 'core_member.member_job', 'acct_savings_account.savings_id', 'acct_savings.savings_code', 'acct_savings.savings_name', 'acct_savings_account.savings_account_no', 'acct_savings_account.savings_account_date', 'acct_savings_account.savings_account_first_deposit_amount', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.voided_remark', 'acct_savings_account.validation', 'acct_savings_account.validation_at', 'acct_savings_account.validation_id', 'acct_savings_account.office_id')
        ->join('core_member', 'acct_savings_account.member_id', '=', 'core_member.member_id')
        ->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
        ->where('acct_savings_account.data_state', 0)
        ->where('acct_savings_account.savings_account_id', $savings_account_id)
        ->first();

        $validation_name = User::select('username')
        ->where('user_id', $acctsavingsaccount['validation_id'])
        ->first()
        ->username;

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

        $export .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"55%\"><div style=\"text-align: right; font-size:14px\">".$acctsavingsaccount['savings_account_no']."</div></td>
                <td width=\"40%\"><div style=\"text-align: right; font-size:14px\">".$acctsavingsaccount['member_name']."</div></td>
                <td width=\"5%\"><div style=\"text-align: right; font-size:14px\">".$acctsavingsaccount['office_id']."</div></td>
            </tr>
            <tr>
                <td width=\"52%\"><div style=\"text-align: right; font-size:14px\">".$acctsavingsaccount['validation_at']."</div></td>
                <td width=\"18%\"><div style=\"text-align: right; font-size:14px\">".$validation_name."</div></td>
                <td width=\"30%\"><div style=\"text-align: right; font-size:14px\"> IDR &nbsp; ".number_format($acctsavingsaccount['savings_account_first_deposit_amount'], 2)."</div></td>
            </tr>
        </table>";


        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Validasi Tabungan.pdf';
        $pdf::Output($filename, 'I');
    }

    public function getSavingsInterestRate(Request $request){
        $savings_interest_rate = AcctSavings::select('savings_interest_rate')
        ->where('savings_id', $request->savings_id)
        ->first()
        ->savings_interest_rate;

        return $savings_interest_rate;
    }
    public function unblock($savings_account_id) {
        try{
        DB::beginTransaction();
        $AcctSavings=AcctSavingsAccount::find($savings_account_id);
        $AcctSavings->unblock_state = 1;
        $AcctSavings->save();
        DB::commit();
        $message = array(
            'pesan' => 'Tabungan berhasil diunblokir',
            'alert' => 'success'
        );
        return redirect('savings-account')->with($message);
        }catch(\Exception $e){
        DB::rollBack();
        $message = array(
            'pesan' => 'Tabungan gagal diunblokir',
            'alert' => 'error'
        );
        return redirect('savings-account')->with($message);
        }
        }
}
