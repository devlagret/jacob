<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\AcctDepositoProfitSharing;
use App\Models\AcctSavingsAccount;
use App\Models\CoreBranch;
use App\Models\CoreCity;
use App\Models\CoreKecamatan;
use App\Models\CoreMember;
use App\Models\CoreOffice;
use App\Models\PreferenceCompany;
use App\Models\PreferenceInventory;
use App\Models\PreferenceTransactionModule;
use App\DataTables\AcctDepositoAccount\AcctDepositoAccountDataTable;
use App\DataTables\AcctDepositoAccount\AcctSavingsAccountDataTable;
use App\DataTables\AcctDepositoAccount\CoreMemberDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctDepositoAccountController extends Controller
{
    public function index(AcctDepositoAccountDataTable $dataTable)
    {
        session()->forget('data_depositoaccountadd');
        $sessiondata = session()->get('filter_depositoaccount');

        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state', 0)
        ->get();

        $acctdeposito = AcctDeposito::select('deposito_id', 'deposito_name')
        ->where('data_state', 0)
        ->get();
        
        return $dataTable->render('content.AcctDepositoAccount.List.index', compact('corebranch', 'acctdeposito', 'sessiondata'));
    }

    public function filter(Request $request){
        if($request->deposito_id){
            $deposito_id = $request->deposito_id;
        }else{
            $deposito_id = null;
        }

        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = null;
        }

        $sessiondata = array(
            'deposito_id' => $deposito_id,
            'branch_id'  => $branch_id
        );

        session()->put('filter_depositoaccount', $sessiondata);

        return redirect('deposito-account');
    }

    public function filterReset(){
        session()->forget('filter_depositoaccount');

        return redirect('deposito-account');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_depositoaccountadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['deposito_id']                             = null;
            $sessiondata['deposito_account_extra_type']             = null;
            $sessiondata['deposito_period']                         = null;
            $sessiondata['office_id']                               = null;
            $sessiondata['deposito_account_due_date']               = null;
            $sessiondata['deposito_account_amount']                 = 0;
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_depositoaccountadd', $sessiondata);
    }

    public function add()
    {
        $config                 = theme()->getOption('page', 'view');
        $sessiondata            = session()->get('data_depositoaccountadd');
        $membergender           = array_filter(Configuration::MemberGender());
        $memberidentity         = array_filter(Configuration::MemberIdentity());
        $familyrelationship     = array_filter(Configuration::FamilyRelationship());
        $depositoextratype      = array_filter(Configuration::DepositoExtraType());
        
        $acctdeposito            = AcctDeposito::select('deposito_id', 'deposito_name')
	    ->join('acct_account', 'acct_account.account_id','=','acct_deposito.account_id')
        ->where('acct_deposito.data_state', 0)
        ->get();
        
        $coreoffice             = CoreOffice::select('office_id', 'office_name')
        ->where('data_state', 0)
        ->get();

        $coremember             = array();
        if(isset($sessiondata['member_id'])){
            $coremember = CoreMember::select('core_member.member_id', 'core_member.member_no', 'core_member.member_name', 'core_member.member_address', 'core_member.member_date_of_birth', 'core_member.member_gender', 'core_member.member_phone', 'core_member.member_mother', 'core_member.member_identity_no', 'core_member.member_heir', 'core_member.member_heir_address', 'core_member.member_heir_relationship', 'core_city.city_name', 'core_kecamatan.kecamatan_name')
            ->join('core_city', 'core_city.city_id', '=', 'core_member.city_id')
            ->join('core_kecamatan', 'core_kecamatan.kecamatan_id', '=', 'core_member.kecamatan_id')
            ->where('member_id', $sessiondata['member_id'])
            ->first();
        }

        $savingsaccount         = array();
        if(isset($sessiondata['savings_account_id'])){
            $savingsaccount = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no')
            ->where('savings_account_id', $sessiondata['savings_account_id'])
            ->first();
        }

        return view('content.AcctDepositoAccount.Add.index', compact('sessiondata', 'membergender', 'memberidentity', 'familyrelationship', 'depositoextratype', 'acctdeposito', 'coreoffice', 'coremember', 'savingsaccount'));
    }

    public function processAdd(Request $request)
    {
        $preferencecompany = PreferenceCompany::first();

        $fields = request()->validate([
            'member_id'                     => ['required'],
            'savings_account_id'            => ['required'],
            'deposito_id'                   => ['required'],
            'office_id'                     => ['required'],
            'deposito_period'               => ['required'],
            'deposito_account_date'         => ['required'],
            'deposito_account_due_date'     => ['required'],
            'deposito_account_interest'     => ['required'],
            'deposito_account_amount'       => ['required'],
        ]);

        DB::beginTransaction();

        try {
            $data  = array(
                'member_id'                             => $fields['member_id'],
                'savings_account_id'                    => $fields['savings_account_id'],
                'deposito_id'                           => $fields['deposito_id'],
                'office_id'                             => $fields['office_id'],
                'deposito_account_period'               => $fields['deposito_period'],
                'deposito_account_date'                 => date('Y-m-d', strtotime($fields['deposito_account_date'])),
                'deposito_account_due_date'             => date('Y-m-d', strtotime($fields['deposito_account_due_date'])),
                'deposito_account_interest'             => $fields['deposito_account_interest'],
                'deposito_account_amount'               => $fields['deposito_account_amount'],
                'deposito_member_heir'                  => $request->deposito_member_heir,
                'deposito_member_heir_address'          => $request->deposito_member_heir_address,
                'deposito_member_heir_relationship'     => $request->deposito_member_heir_relationship,
                'branch_id'                             => auth()->user()->branch_id,
                'created_id'                            => auth()->user()->user_id,
            );
            AcctDepositoAccount::create($data);

			$transaction_module_code 	= "DEP";
			$transaction_module_id 		= PreferenceTransactionModule::select('transaction_module_id')
            ->where('transaction_module_code', $transaction_module_code)
            ->first()
            ->transaction_module_id;
            
            $acctdepositoaccount_last = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.deposito_account_no', 'core_member.member_name')
            ->join('core_member', 'core_member.member_id', 'acct_deposito_account.member_id')
			->where('acct_deposito_account.created_id', $data['created_id'])
            ->whereDate('acct_deposito_account.created_at', date('Y-m-d'))
			->orderBy('acct_deposito_account.created_at','DESC')
            ->first();

            $date 	= date('d', strtotime($data['deposito_account_date']));
            $month 	= date('m', strtotime($data['deposito_account_date']));
            $year 	= date('Y', strtotime($data['deposito_account_date']));

            for($i = 1; $i <= $data['deposito_account_period']; $i++) { 
                $depositoprofitsharing = array ();

                $month = $month + 1;
                if($month == 13){
                    $month = 01;
                    $year = $year + 1;
                }
                $deposito_profit_sharing_due_date = $year.'-'.$month.'-'.$date;

                $depositoprofitsharing = array (
                    'deposito_account_id'				=> $acctdepositoaccount_last['deposito_account_id'],
                    'branch_id'							=> auth()->user()->branch_id,
                    'deposito_id'						=> $data['deposito_id'],
                    'deposito_account_nisbah'			=> $data['deposito_account_interest'],
                    'member_id'							=> $data['member_id'],
                    'deposito_profit_sharing_due_date'	=> $deposito_profit_sharing_due_date,
                    'deposito_daily_average_balance'	=> $data['deposito_account_amount'],
                    'deposito_account_last_balance'		=> $data['deposito_account_amount'],
                    'savings_account_id'				=> $data['savings_account_id'],
                );
                AcctDepositoProfitSharing::create($depositoprofitsharing);
            }
            
            $journal_voucher_period = date("Ym", strtotime($data['deposito_account_date']));
            $data_journal = array(
                'branch_id'						=> auth()->user()->branch_id,
                'journal_voucher_period' 		=> $journal_voucher_period,
                'journal_voucher_date'			=> date('Y-m-d'),
                'journal_voucher_title'			=> 'SETORAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
                'journal_voucher_description'	=> 'SETORAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $acctdepositoaccount_last['deposito_account_id'],
                'transaction_journal_no' 		=> $acctdepositoaccount_last['deposito_account_no'],
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

            $data_debet = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $preferencecompany['account_cash_id'],
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($data['deposito_account_amount']),
                'journal_voucher_debit_amount'	=> ABS($data['deposito_account_amount']),
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 0,
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucherItem::create($data_debet);

            $account_id = AcctDeposito::select('account_id')
            ->where('deposito_id', $data['deposito_id'])
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
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($data['deposito_account_amount']),
                'journal_voucher_credit_amount'	=> ABS($data['deposito_account_amount']),
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 1,
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucherItem::create($data_credit);

            DB::commit();
            $message = array(
                'pesan' => 'Simpanan Berjangka berhasil ditambah',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Simpanan Berjangka gagal ditambah',
                'alert' => 'error'
            );
        }
        
        return redirect('deposito-account')->with($message);
    }

    public function modalCoreMember(CoreMemberDataTable $dataTable)
    {
        return $dataTable->render('content.AcctDepositoAccount.Add.CoreMemberModal.index');
    }

    public function selectCoreMember($member_id)
    {
        $sessiondata = session()->get('data_depositoaccountadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['deposito_id']                  = null;
            $sessiondata['deposito_account_extra_type']  = null;
            $sessiondata['deposito_period']              = null;
            $sessiondata['office_id']                    = null;
            $sessiondata['deposito_account_due_date']    = null;
            $sessiondata['deposito_account_amount']      = 0;
        }
        $sessiondata['member_id'] = $member_id;
        session()->put('data_depositoaccountadd', $sessiondata);

        return redirect('deposito-account/add');
    }

    public function modalAcctSavingsAccount(AcctSavingsAccountDataTable $dataTable)
    {
        return $dataTable->render('content.AcctDepositoAccount.Add.AcctSavingsAccountModal.index');
    }

    public function selectAcctSavingsAccount($savings_account_id)
    {
        $sessiondata = session()->get('data_depositoaccountadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['deposito_id']                  = null;
            $sessiondata['deposito_account_extra_type']  = null;
            $sessiondata['deposito_period']              = null;
            $sessiondata['office_id']                    = null;
            $sessiondata['deposito_account_due_date']    = null;
            $sessiondata['deposito_account_amount']      = 0;
        }
        $sessiondata['savings_account_id'] = $savings_account_id;
        session()->put('data_depositoaccountadd', $sessiondata);

        return redirect('deposito-account/add');
    }

    public function printNote($deposito_account_id){
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $branch_city        = CoreBranch::select('branch_city')
        ->where('branch_id', auth()->user()->branch_id)
        ->first()
        ->branch_city;

        $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member.member_gender', 'core_member.member_address', 'core_member.member_phone', 'core_member.member_date_of_birth', 'core_member.member_identity_no', 'core_member.city_id', 'core_member.kecamatan_id', 'core_member.identity_id', 'core_member.member_job', 'acct_deposito_account.deposito_id', 'acct_deposito.deposito_code', 'acct_deposito.deposito_name', 'acct_deposito.deposito_interest_rate', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_serial_no', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.voided_remark', 'acct_deposito_account.savings_account_id', 'acct_deposito_account.office_id', 'acct_deposito_account.deposito_account_interest', 'acct_deposito_account.validation', 'acct_deposito_account.validation_id', 'acct_deposito_account.validation_at', 'acct_deposito_account.office_id', 'acct_deposito_account.deposito_account_blockir_type', 'acct_deposito_account.deposito_account_blockir_status')
        ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
        ->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
        ->where('acct_deposito_account.deposito_account_id', $deposito_account_id)
        ->where('acct_deposito_account.data_state', 0)
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

        $pdf::SetFont('helvetica', '', 13);

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
                <td width=\"75%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN SIMPANAN BERJANGKA</div></td>
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
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctdepositoaccount['member_name']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">No. Rekening</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctdepositoaccount['deposito_account_no']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctdepositoaccount['member_address']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".Configuration::numtotxt($acctdepositoaccount['deposito_account_amount'])."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: SETORAN AWAL TABUNGAN</div></td>
            </tr>
                <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctdepositoaccount['deposito_account_amount'], 2)."</div></td>
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

        $filename = 'Kwitansi Simpanan Berjangka.pdf';
        $pdf::Output($filename, 'I');
    }

    public function validation($deposito_account_id){
        $depositoaccount                 = AcctDepositoAccount::findOrFail($deposito_account_id);
        $depositoaccount->validation     = 1;        
        $depositoaccount->validation_id  = auth()->user()->user_id;        
        $depositoaccount->validation_at  = date('Y-m-d');     
        if($depositoaccount->save()){
            $message = array(
                'pesan' => 'Simpanan Berjangka berhasil divalidasi',
                'alert' => 'success'
            );

            return redirect('deposito-account/print-validation/'.$deposito_account_id);
        }else{
            $message = array(
                'pesan' => 'Simpanan Berjangka gagal divalidasi',
                'alert' => 'error'
            );
            return redirect('deposito-account')->with($message);
        }

    }

    public function printValidation($deposito_account_id){
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $branch_city        = CoreBranch::select('branch_city')
        ->where('branch_id', auth()->user()->branch_id)
        ->first()
        ->branch_city;

        $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member.member_gender', 'core_member.member_address', 'core_member.member_phone', 'core_member.member_date_of_birth', 'core_member.member_identity_no', 'core_member.city_id', 'core_member.kecamatan_id', 'core_member.identity_id', 'core_member.member_job', 'acct_deposito_account.deposito_id', 'acct_deposito.deposito_code', 'acct_deposito.deposito_name', 'acct_deposito.deposito_interest_rate', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_serial_no', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.voided_remark', 'acct_deposito_account.savings_account_id', 'acct_deposito_account.office_id', 'acct_deposito_account.deposito_account_interest', 'acct_deposito_account.validation', 'acct_deposito_account.validation_id', 'acct_deposito_account.validation_at', 'acct_deposito_account.office_id', 'acct_deposito_account.deposito_account_blockir_type', 'acct_deposito_account.deposito_account_blockir_status')
        ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
        ->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
        ->where('acct_deposito_account.deposito_account_id', $deposito_account_id)
        ->where('acct_deposito_account.data_state', 0)
        ->first();

        $validation_name = User::select('username')
        ->where('user_id', $acctdepositoaccount['validation_id'])
        ->first()
        ->username;

        $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 16, 6, 6);

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
                <td width=\"55%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['deposito_account_no']."</div></td>
                <td width=\"40%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['member_name']."</div></td>
                <td width=\"5%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['office_id']."</div></td>
            </tr>
            <tr>
                <td width=\"52%\"><div style=\"text-align: right; font-size:14px\">".$acctdepositoaccount['validation_at']."</div></td>
                <td width=\"18%\"><div style=\"text-align: right; font-size:14px\">".$validation_name."</div></td>
                <td width=\"30%\"><div style=\"text-align: right; font-size:14px\"> IDR &nbsp; ".number_format($acctdepositoaccount['deposito_account_amount'], 2)."</div></td>
            </tr>
        </table>";


        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Validasi Simpanan Berjangka.pdf';
        $pdf::Output($filename, 'I');
    }
    public function printCertificate() {
        
    }
    public function printCertificateFront($deposito_account_id){
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $branch_city        = CoreBranch::select('branch_city')
        ->where('branch_id', auth()->user()->branch_id)
        ->first()
        ->branch_city;

        $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member.member_gender', 'core_member.member_address', 'core_member.member_phone', 'core_member.member_date_of_birth', 'core_member.member_identity_no', 'core_member.city_id', 'core_member.kecamatan_id', 'core_member.identity_id', 'core_member.member_job', 'acct_deposito_account.deposito_id', 'acct_deposito.deposito_code', 'acct_deposito.deposito_name', 'acct_deposito.deposito_interest_rate', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_serial_no', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.voided_remark', 'acct_deposito_account.savings_account_id', 'acct_deposito_account.office_id', 'acct_deposito_account.deposito_account_interest', 'acct_deposito_account.validation', 'acct_deposito_account.validation_id', 'acct_deposito_account.validation_at', 'acct_deposito_account.office_id', 'acct_deposito_account.deposito_account_blockir_type', 'acct_deposito_account.deposito_account_blockir_status')
        ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
        ->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
        ->where('acct_deposito_account.deposito_account_id', $deposito_account_id)
        ->where('acct_deposito_account.data_state', 0)
        ->first();

        $validation_name = User::select('username')
        ->where('user_id', $acctdepositoaccount['validation_id'])
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
        <table cellspacing=\"6\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"100%\" colspan=\"4\" height=\"135px\"></td>
            </tr>
            <tr>
                <td width=\"20%\"></td>
                <td width=\"45%\"><div style=\"text-align: left; font-size:12px\">".$acctdepositoaccount['member_name']."</div></td>
                <td width=\"10%\"></td>
                <td width=\"25%\"><div style=\"text-align: right; font-size:12px\">".number_format($acctdepositoaccount['deposito_account_amount'], 2)."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"></td>
                <td width=\"45%\"><div style=\"text-align: left; font-size:12px\">".$acctdepositoaccount['member_address']."</div></td>
                <td width=\"10%\"></td>
                <td width=\"25%\" rowspan =\"2\"><div style=\"text-align: left; font-size:11px\">".Configuration::numtotxt($acctdepositoaccount['deposito_account_amount'])."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"></td>
                <td width=\"45%\"><div style=\"text-align: left; font-size:12px\">".$acctdepositoaccount['deposito_account_no']."</div></td>
                <td width=\"10%\"></td>
            </tr>
        </table>
        <br><br><br><br><br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"30%\"><div style=\"text-align: center; font-size:12px\">".date('d-m-Y', strtotime($acctdepositoaccount['deposito_account_date']))."</div></td>
                <td width=\"25%\"><div style=\"text-align: center; font-size:12px\">".date('d-m-Y', strtotime($acctdepositoaccount['deposito_account_due_date']))."</div></td>
                <td width=\"30%\"><div style=\"text-align: center; font-size:12px\">".$acctdepositoaccount['deposito_account_period']."</div></td>
                <td width=\"25%\"><div style=\"text-align: center; font-size:12px\">".$acctdepositoaccount['deposito_interest_rate']."</div></td>
            </tr>
        </table>
        <br><br><br><br><br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"28%\"><div style=\"text-align: center; font-size:12px\">".$acctdepositoaccount['deposito_account_serial_no']."</div></td>
            </tr>
        </table>";


        // //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Validasi Simpanan Berjangka.pdf';
        $pdf::Output($filename, 'I');
    }

    public function printCertificateBack($deposito_account_id){
        $preferencecompany	= PreferenceCompany::select('logo_koperasi', 'company_name')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $branch_city        = CoreBranch::select('branch_city')
        ->where('branch_id', auth()->user()->branch_id)
        ->first()
        ->branch_city;

        $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member.member_gender', 'core_member.member_address', 'core_member.member_phone', 'core_member.member_date_of_birth', 'core_member.member_identity_no', 'core_member.city_id', 'core_member.kecamatan_id', 'core_member.identity_id', 'core_member.member_job', 'acct_deposito_account.deposito_id', 'acct_deposito.deposito_code', 'acct_deposito.deposito_name', 'acct_deposito.deposito_interest_rate', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_serial_no', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.voided_remark', 'acct_deposito_account.savings_account_id', 'acct_deposito_account.office_id', 'acct_deposito_account.deposito_account_interest', 'acct_deposito_account.validation', 'acct_deposito_account.validation_id', 'acct_deposito_account.validation_at', 'acct_deposito_account.office_id', 'acct_deposito_account.deposito_account_blockir_type', 'acct_deposito_account.deposito_account_blockir_status')
        ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
        ->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
        ->where('acct_deposito_account.deposito_account_id', $deposito_account_id)
        ->where('acct_deposito_account.data_state', 0)
        ->first();

        $validation_name = User::select('username')
        ->where('user_id', $acctdepositoaccount['validation_id'])
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
        <table cellspacing=\"6\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"100%\" colspan=\"4\" height=\"50px\"></td>
            </tr>
            <tr>
                <td width=\"10%\"></td>
                <td width=\"20%\"><div style=\"text-align: left; font-size:12px\">".$acctdepositoaccount['member_no']."</div></td>
                <td width=\"10%\"></td>
                <td width=\"45%\"><div style=\"text-align: left; font-size:12px\">".$acctdepositoaccount['member_name']."</div></td>
            </tr>
            <tr>
                <td width=\"100%\" colspan=\"4\"></td>
            </tr>
            <tr>
                <td width=\"10%\"></td>
                <td width=\"55%\"></td>
                <td width=\"10%\"></td>
                <td width=\"25%\" rowspan =\"2\"><div style=\"text-align: left; font-size:11px\">".number_format($acctdepositoaccount['deposito_account_amount'], 2)."</div></td>
            </tr>
        </table>";

        // //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Validasi Simpanan Berjangka.pdf';
        $pdf::Output($filename, 'I');
    }

    public function getDepositoDetail(Request $request){
        $deposito = AcctDeposito::select('deposito_id', 'deposito_period', 'deposito_interest_rate')
        ->where('deposito_id', $request->deposito_id)
        ->first();

        $deposito_account_date                 = date('d-m-Y');
        $deposito_account_due_date             = date('d-m-Y', strtotime("+".$deposito['deposito_period']." months", strtotime($deposito_account_date)));
        $deposito['deposito_account_due_date'] = $deposito_account_due_date;

        return $deposito;
    }
}
