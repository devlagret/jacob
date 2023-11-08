<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\AcctDepositoProfitSharing;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsTransferMutation;
use App\Models\AcctSavingsTransferMutationTo;
use App\Models\CoreBranch;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\DataTables\AcctDepositoAccountClosing\AcctDepositoAccountClosingDataTable;
use App\DataTables\AcctDepositoAccountClosing\AcctSavingsAccountDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctDepositoAccountClosingController extends Controller
{
    public function index(AcctDepositoAccountClosingDataTable $dataTable)
    {
        session()->forget('data_depositoaccountclosingadd');
        $sessiondata = session()->get('filter_depositoaccountclosing');

        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $corebranch         = CoreBranch::where('data_state', 0)
            ->get();
        }else{
            $corebranch         = CoreBranch::where('data_state', 0)
            ->where('branch_id', $branch_id)
            ->get();
        }

        $acctdeposito = AcctDeposito::select('deposito_id', 'deposito_name')
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctDepositoAccountClosing.List.index', compact('sessiondata', 'corebranch', 'acctdeposito'));
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
            $branch_id = auth()->user()->branch_id;
        }

        $sessiondata = array(
            'deposito_id'   => $deposito_id,
            'branch_id'     => $branch_id
        );

        session()->put('filter_depositoaccountclosing', $sessiondata);

        return redirect('deposito-account-closing');
    }

    public function filterReset(){
        session()->forget('filter_depositoaccountclosing');

        return redirect('deposito-account-closing');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_depositoaccountclosingadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['deposito_account_amount_adm'] = 0;
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_depositoaccountclosingadd', $sessiondata);
    }

    public function update($deposito_account_id)
    {
        $config                 = theme()->getOption('page', 'view');
        $sessiondata            = session()->get('data_depositoaccountclosingadd');

        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['deposito_account_amount_adm'] = 0;
        }
        $sessiondata['deposito_account_id'] = $deposito_account_id;
        session()->put('data_depositoaccountclosingadd', $sessiondata);

        $acctdepositoaccount    = AcctDepositoAccount::select('acct_deposito_account.*', 'core_member.member_no', 'core_member.member_name', 'core_member.member_address', 'core_member.member_phone', 'acct_deposito.deposito_name')
        ->join('core_member', 'core_member.member_id', 'acct_deposito_account.member_id')
        ->join('acct_deposito', 'acct_deposito.deposito_id', 'acct_deposito_account.deposito_id')
        ->where('acct_deposito_account.deposito_account_id', $deposito_account_id)
        ->first();

        $acctsavingsaccount     = array();
        if(isset($sessiondata['savings_account_id'])){
            $acctsavingsaccount = AcctSavingsAccount::select('acct_savings_account.*', 'acct_savings_account.savings_id', 'acct_savings_account.member_id', 'acct_savings_account.savings_account_last_balance', DB::raw('CONCAT(acct_savings_account.savings_account_no," - ",core_member.member_name) AS full_no'))
            ->join('core_member', 'core_member.member_id', '=', 'acct_savings_account.member_id')
            ->where('acct_savings_account.savings_account_id', $sessiondata['savings_account_id'])
            ->first();
        }else{
            $acctsavingsaccount = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_id', 'acct_savings_account.member_id', 'acct_savings_account.savings_account_last_balance', DB::raw('CONCAT(acct_savings_account.savings_account_no," - ",core_member.member_name) AS full_no'))
            ->join('core_member', 'core_member.member_id', '=', 'acct_savings_account.member_id')
            ->where('acct_savings_account.savings_account_id', $acctdepositoaccount['savings_account_id'])
            ->first();
        }

        $preferencecompany = PreferenceCompany::first();

        if($acctdepositoaccount['deposito_account_interest_amount'] > $preferencecompany['tax_minimum_amount']){
            $tax_total	= $acctdepositoaccount['deposito_account_interest_amount'] * $preferencecompany['tax_percentage'] / 100;
        }else{
            $tax_total 	= 0;
        }

        $interest_received_total = $acctdepositoaccount['deposito_account_interest_amount'] - $tax_total;

        return view('content.AcctDepositoAccountClosing.Add.index', compact('sessiondata', 'acctdepositoaccount', 'acctsavingsaccount', 'tax_total', 'interest_received_total'));
    }

    public function modalAcctSavingsAccount(AcctSavingsAccountDataTable $dataTable)
    {
        return $dataTable->render('content.AcctDepositoAccountClosing.Add.AcctSavingsAccountModal.index');
    }

    public function selectAcctSavingsAccount($savings_account_id)
    {
        $sessiondata = session()->get('data_depositoaccountclosingadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['deposito_account_amount_adm'] = 0;
        }
        $sessiondata['savings_account_id'] = $savings_account_id;
        session()->put('data_depositoaccountclosingadd', $sessiondata);

        return redirect('deposito-account-closing/update/'.$sessiondata['deposito_account_id']);
    }

    public function processUpdate(Request $request)
    {
        $preferencecompany = PreferenceCompany::first();

        $fields = request()->validate([
            'savings_account_id'            => ['required'],
        ]);

        DB::beginTransaction();

        try {
			$data = array (
				'deposito_account_id'		    => $request->deposito_account_id,
				'deposito_account_penalty'		=> 0,
				'deposito_account_closed_date'	=> date('Y-m-d'),
				'deposito_account_status'		=> 1,
				'savings_account_id'		    => $fields['savings_account_id'],
			);

            $data_savings = array (
				'savings_id'						=> $request->savings_id,
				'member_id'						    => $request->member_id_savings,
				'savings_account_opening_balance'	=> $request->savings_account_last_balance,
				'savings_account_last_balance'		=> $request->savings_account_last_balance + $request->deposito_account_amount,
			);	

			$total_amount				   			            = $request->deposito_account_amount;
			$amount_administration                              = $request->deposito_account_amount_adm;
			$acctdepositoaccount 		                        = AcctDepositoAccount::findOrFail($data['deposito_account_id']);
			$acctdepositoaccount->deposito_account_penalty      = 0;
			$acctdepositoaccount->deposito_account_closed_date  = date('Y-m-d');
			$acctdepositoaccount->deposito_account_status       = 1;
			$acctdepositoaccount->savings_account_id            = $fields['savings_account_id'];
            $acctdepositoaccount->save();

            $data_transfer = array (
                'branch_id'							=> auth()->user()->branch_id,
                'savings_transfer_mutation_date'	=> date('Y-m-d'),
                'savings_transfer_mutation_amount'	=> $total_amount,
                'operated_name'						=> 'SYS',
                'created_id'						=> auth()->user()->user_id,
            );
            AcctSavingsTransferMutation::create($data_transfer);

            $savings_transfer_mutation_id = AcctSavingsTransferMutation::select('savings_transfer_mutation_id')
            ->where('created_id', auth()->user()->user_id)
            ->orderBy('savings_transfer_mutation_id', 'DESC')
            ->first()
            ->savings_transfer_mutation_id;

            $data_transfer_to = array (
                'savings_transfer_mutation_id'				=> $savings_transfer_mutation_id,
                'savings_account_id'						=> $data['savings_account_id'],
                'savings_id'								=> $data_savings['savings_id'],
                'member_id'									=> $data_savings['member_id'],
                'branch_id'									=> auth()->user()->branch_id,
                'mutation_id'								=> 10,
                'savings_account_opening_balance'			=> $data_savings['savings_account_opening_balance'],
                'savings_transfer_mutation_to_amount'		=> $total_amount,
                'savings_account_last_balance'				=> $data_savings['savings_account_last_balance'],
            );
            AcctSavingsTransferMutationTo::create($data_transfer_to);

            $transaction_module_code    = "PDEP";

            $transaction_module_id 		= PreferenceTransactionModule::select('transaction_module_id')
            ->where('transaction_module_code', $transaction_module_code)
            ->first()
            ->transaction_module_id;
            
            $acctdepositoaccount_last 	= AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member.member_gender', 'core_member.member_address', 'core_member.member_phone', 'core_member.member_date_of_birth', 'core_member.member_identity_no', 'core_member.city_id', 'core_member.kecamatan_id', 'core_member.identity_id', 'core_member.member_job', 'acct_deposito_account.deposito_id', 'acct_deposito.deposito_code', 'acct_deposito.deposito_name', 'acct_deposito.deposito_interest_rate', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_serial_no', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_period', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.voided_remark', 'acct_deposito_account.savings_account_id', 'acct_deposito_account.office_id', 'acct_deposito_account.deposito_account_interest_amount', 'acct_deposito_account.validation', 'acct_deposito_account.validation_id', 'acct_deposito_account.validation_at', 'acct_deposito_account.office_id', 'acct_deposito_account.deposito_account_blockir_type', 'acct_deposito_account.deposito_account_blockir_status')
			->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
			->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
			->where('acct_deposito_account.deposito_account_id', $data['deposito_account_id'])
			->where('acct_deposito_account.data_state', 0)
            ->first();

            $journal_voucher_period = date("Ym", strtotime($data['deposito_account_closed_date']));
            
            $data_journal = array(
                'branch_id'						=> auth()->user()->branch_id,
                'journal_voucher_period' 		=> $journal_voucher_period,
                'journal_voucher_date'			=> date('Y-m-d'),
                'journal_voucher_title'			=> 'PENUTUPAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
                'journal_voucher_description'	=> 'PENUTUPAN SIMP BERJANGKA '.$acctdepositoaccount_last['member_name'],
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $acctdepositoaccount_last['deposito_account_id'],
                'transaction_journal_no' 		=> $acctdepositoaccount_last['deposito_account_no'],
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucher::create($data_journal);

            $journal_voucher_id = AcctJournalVoucher::select('journal_voucher_id')
			->where('created_id', $data_journal['created_id'])
			->orderBy('journal_voucher_id', 'DESC')
            ->first()
            ->journal_voucher_id;

            $account_id = AcctDeposito::select('account_id')
            ->where('deposito_id', $acctdepositoaccount_last['deposito_id'])
            ->first()
            ->account_id;

            $account_id_default_status = AcctAccount::select('account_default_status')
			->where('account_id', $account_id)
			->where('data_state', 0)
            ->first()
            ->account_default_status;

            $data_debet =array(
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $account_id,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($total_amount),
                'journal_voucher_debit_amount'	=> ABS($total_amount),
                'account_id_status'				=> 0,
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucherItem::create($data_debet);

            $account_id = AcctSavings::select('account_id')
            ->where('acct_savings.savings_id', $data_savings['savings_id'])
            ->first()
            ->account_id;

            $account_id_default_status = AcctAccount::select('account_default_status')
			->where('account_id', $account_id)
			->where('data_state', 0)
            ->first()
            ->account_default_status;

            $data_credit = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $account_id,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($total_amount),
                'journal_voucher_credit_amount'	=> ABS($total_amount),
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 1,
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucherItem::create($data_credit);
            
            if($amount_administration > 0){
                $data_debet = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_cash_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> $amount_administration,
                    'journal_voucher_debit_amount'	=> $amount_administration,
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                    'created_id' 					=> auth()->user()->user_id
                );
                AcctJournalVoucherItem::create($data_debet);

                $account_id_default_status = AcctAccount::select('account_default_status')
                ->where('account_id', $preferencecompany['account_mutation_adm_id'])
                ->where('data_state', 0)
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_mutation_adm_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> $amount_administration,
                    'journal_voucher_credit_amount'	=> $amount_administration,
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                    'created_id' 					=> auth()->user()->user_id
                );
                AcctJournalVoucherItem::create($data_credit);
            }

            DB::commit();
            $message = array(
                'pesan' => 'Penutupan Simpanan Berjangka berhasil diproses',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Penutupan Simpanan Berjangka gagal diproses',
                'alert' => 'error'
            );
        }
        
        return redirect('deposito-account-closing')->with($message);
    }
}
