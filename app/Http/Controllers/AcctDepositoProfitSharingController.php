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
use App\DataTables\AcctDepositoProfitSharing\AcctDepositoProfitSharingDataTable;
use App\DataTables\AcctDepositoProfitSharing\AcctSavingsAccountDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctDepositoProfitSharingController extends Controller
{
    public function index(AcctDepositoProfitSharingDataTable $dataTable)
    {
        session()->forget('data_depositoprofitsharingadd');
        $sessiondata = session()->get('filter_depositoprofitsharing');

        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctDepositoProfitSharing.List.index', compact('sessiondata', 'corebranch'));
    }

    public function filter(Request $request){
        if($request->start_date){
            $start_date = $request->start_date;
        }else{
            $start_date = date('Y-m-d');
        }

        if($request->end_date){
            $end_date = $request->end_date;
        }else{
            $end_date = date('Y-m-d');
        }

        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = auth()->user()->branch_id;
        }

        $sessiondata = array(
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'branch_id'  => $branch_id
        );

        session()->put('filter_depositoprofitsharing', $sessiondata);

        return redirect('deposito-profit-sharing');
    }

    public function filterReset(){
        session()->forget('filter_depositoprofitsharing');

        return redirect('deposito-profit-sharing');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_depositoprofitsharingadd');
        if(!$sessiondata || $sessiondata == ""){
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_depositoprofitsharingadd', $sessiondata);
    }

    public function update($deposito_profit_sharing_id)
    {
        $config                 = theme()->getOption('page', 'view');
        $sessiondata            = session()->get('data_depositoprofitsharingadd');

        $sessiondata['deposito_profit_sharing_id'] = $deposito_profit_sharing_id;
        session()->put('data_depositoprofitsharingadd', $sessiondata);
        
        $acctdepositoaccount    = AcctDepositoProfitSharing::select('acct_deposito_account.*', 'core_member.member_name', 'core_member.member_phone', 'core_member.member_address')
        ->join('acct_deposito_account', 'acct_deposito_account.deposito_account_id', 'acct_deposito_profit_sharing.deposito_account_id')
        ->join('core_member', 'core_member.member_id', 'acct_deposito_account.member_id')
        ->join('acct_deposito', 'acct_deposito.deposito_id', 'acct_deposito_account.deposito_id')
        ->where('acct_deposito_profit_sharing.deposito_profit_sharing_id', $deposito_profit_sharing_id)
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

        return view('content.AcctDepositoProfitSharing.Add.index', compact('sessiondata', 'acctdepositoaccount', 'acctsavingsaccount'));
    }

    public function modalAcctSavingsAccount(AcctSavingsAccountDataTable $dataTable)
    {
        return $dataTable->render('content.AcctDepositoProfitSharing.Add.AcctSavingsAccountModal.index');
    }

    public function selectAcctSavingsAccount($savings_account_id)
    {
        $sessiondata = session()->get('data_depositoprofitsharingadd');
        if(!$sessiondata || $sessiondata == ""){
        }
        $sessiondata['savings_account_id'] = $savings_account_id;
        session()->put('data_depositoprofitsharingadd', $sessiondata);

        return redirect('deposito-profit-sharing/update/'.$sessiondata['deposito_profit_sharing_id']);
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
				'deposito_id'						=> $request->deposito_id,
				'deposito_profit_sharing_id'		=> $request->deposito_profit_sharing_id,
				'deposito_profit_sharing_date'		=> date('Y-m-d'),
				'deposito_index_amount'				=> $request->deposito_account_interest,
				'deposito_profit_sharing_amount'	=> $request->deposito_profit_sharing_amount,
				'deposito_profit_sharing_period'	=> date('mY'),
				'savings_account_id'				=> $fields['savings_account_id'],
				'deposito_profit_sharing_status'	=> 1,
			);

			$data_savings = array (
				'savings_id'						=> $request->savings_id,
				'member_id'							=> $request->member_id_savings,
				'savings_account_opening_balance'	=> $request->savings_account_last_balance,
				'savings_account_last_balance'		=> $request->savings_account_last_balance + $data['deposito_profit_sharing_amount'],
			);

            $transaction_module_code    = "BSDEP";
			$transaction_module_id 	    = PreferenceTransactionModule::select('transaction_module_id')
            ->where('transaction_module_code', $transaction_module_code)
            ->first()
            ->transaction_module_id;
            
            $depositoprofitsharing = AcctDepositoProfitSharing::findOrFail($data['deposito_profit_sharing_id']);
            $depositoprofitsharing->deposito_profit_sharing_date    = date('Y-m-d');
            $depositoprofitsharing->deposito_index_amount           = $request->deposito_account_interest;
            $depositoprofitsharing->deposito_profit_sharing_amount  = $request->deposito_profit_sharing_amount;
            $depositoprofitsharing->deposito_profit_sharing_period  = date('mY');
            $depositoprofitsharing->savings_account_id              = $fields['savings_account_id'];
            $depositoprofitsharing->deposito_profit_sharing_status  = 1;
            $depositoprofitsharing->save();

			$depositoaccount = AcctDepositoAccount::findOrFail($request->deposito_account_id);
            $depositoaccount->deposito_account_interest_amount  = $depositoaccount['deposito_account_interest_amount']+$data['deposito_profit_sharing_amount'];
            $depositoaccount->deposito_process_last_date        = $data['deposito_profit_sharing_date'];
            $depositoaccount->save();

            $total_amount	= $data['deposito_profit_sharing_amount'];
            $tax_amount		= 0;
            if($total_amount > $preferencecompany['tax_minimum_amount']){
                $tax_amount = $total_amount * $preferencecompany['tax_percentage'] / 100;
            }
            $total_amount_min_tax	= $total_amount - $tax_amount;

            $data_transfer = array (
                'branch_id'							=> auth()->user()->branch_id,
                'savings_transfer_mutation_date'	=> date('Y-m-d'),
                'savings_transfer_mutation_amount'	=> $total_amount_min_tax,
                'operated_name'						=> 'SYS',
                'created_id'						=> auth()->user()->user_id,
            );
            AcctSavingsTransferMutation::create($data_transfer);

            $savings_transfer_mutation_id = AcctSavingsTransferMutation::select('savings_transfer_mutation_id')
            ->where('created_id', $data_transfer['created_id'])
            ->orderBy('savings_transfer_mutation_id', 'DESC')
            ->first()
            ->savings_transfer_mutation_id;

            $data_transfer_to = array (
                'savings_transfer_mutation_id'				=> $savings_transfer_mutation_id,
                'savings_account_id'						=> $data['savings_account_id'],
                'savings_id'								=> $data_savings['savings_id'],
                'member_id'									=> $data_savings['member_id'],
                'branch_id'									=> auth()->user()->branch_id,
                'mutation_id'								=> $preferencecompany['deposito_basil_id'],
                'savings_account_opening_balance'			=> $data_savings['savings_account_opening_balance'],
                'savings_transfer_mutation_to_amount'		=> $total_amount_min_tax,
                'savings_account_last_balance'				=> $data_savings['savings_account_last_balance'],
            );
            AcctSavingsTransferMutationTo::create($data_transfer_to);

            $acctdepositoprofitsharing_last 	= AcctDepositoProfitSharing::select('acct_deposito_profit_sharing.deposito_profit_sharing_id', 'acct_deposito_profit_sharing.deposito_account_id', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.member_id', 'core_member.member_name')
			->join('core_member','acct_deposito_profit_sharing.member_id', '=', 'core_member.member_id')
			->join('acct_deposito_account','acct_deposito_profit_sharing.deposito_account_id', '=', 'acct_deposito_account.deposito_account_id')
			->where('acct_deposito_profit_sharing.deposito_profit_sharing_id', $data['deposito_profit_sharing_id'])
            ->first();
        
            $journal_voucher_period = date("Ym", strtotime($data['deposito_profit_sharing_date']));
            
            $data_journal = array(
                'branch_id'						=> auth()->user()->branch_id,
                'journal_voucher_period' 		=> $journal_voucher_period,
                'journal_voucher_date'			=> date('Y-m-d'),
                'journal_voucher_title'			=> 'JASA SIMP BERJANGKA '.$acctdepositoprofitsharing_last['member_name'],
                'journal_voucher_description'	=> 'JASA SIMP BERJANGKA '.$acctdepositoprofitsharing_last['member_name'],
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $acctdepositoprofitsharing_last['deposito_profit_sharing_id'],
                'transaction_journal_no' 		=> $acctdepositoprofitsharing_last['deposito_account_no'],
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucher::create($data_journal);

            $journal_voucher_id = AcctJournalVoucher::select('journal_voucher_id')
            ->where('created_id', $data_journal['created_id'])
            ->orderBy('journal_voucher_id', 'DESC')
            ->first()
            ->journal_voucher_id;

            $account_basil_id 	= AcctDeposito::select('account_basil_id')
            ->where('deposito_id', $data['deposito_id'])
            ->first()
            ->account_basil_id;

            $account_id_default_status = AcctAccount::select('account_default_status')
            ->where('acct_account.account_id', $account_basil_id)
            ->where('acct_account.data_state', 0)
            ->first()
            ->account_default_status;

            $data_debet = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $account_basil_id,
                'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                'journal_voucher_amount'		=> ABS($total_amount),
                'journal_voucher_debit_amount'	=> ABS($total_amount),
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 0,
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucherItem::create($data_debet);

            $account_id = AcctSavings::select('account_id')
            ->where('savings_id', $data_savings['savings_id'])
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
                'journal_voucher_amount'		=> ABS($total_amount_min_tax),
                'journal_voucher_credit_amount'	=> ABS($total_amount_min_tax),
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 1,
                'created_id' 					=> auth()->user()->user_id,
                
            );
            AcctJournalVoucherItem::create($data_credit);
            
            if($tax_amount > 0){
                $account_savings_tax_id 	= $preferencecompany['account_savings_tax_id'];

                $account_id_default_status = AcctAccount::select('account_default_status')
                ->where('acct_account.account_id', $account_savings_tax_id)
                ->where('acct_account.data_state', 0)
                ->first()
                ->account_default_status;

                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_savings_tax_id,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_description'],
                    'journal_voucher_amount'		=> ABS($tax_amount),
                    'journal_voucher_credit_amount'	=> ABS($tax_amount),
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                    'created_id' 					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
            }

            DB::commit();
            $message = array(
                'pesan' => 'Bunga Simpanan Berjangka berhasil diproses',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Bunga Simpanan Berjangka gagal diproses',
                'alert' => 'error'
            );
        }
        
        return redirect('deposito-profit-sharing')->with($message);
    }
}
