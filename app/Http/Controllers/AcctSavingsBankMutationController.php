<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctBankAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsBankMutation;
use App\Models\CoreBranch;
use App\Models\CoreCity;
use App\Models\CoreKecamatan;
use App\Models\CoreMember;
use App\Models\AcctMutation;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\DataTables\AcctSavingsBankMutation\AcctSavingsBankMutationDataTable;
use App\DataTables\AcctSavingsBankMutation\AcctSavingsAccountDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctSavingsBankMutationController extends Controller
{
    public function index(AcctSavingsBankMutationDataTable $dataTable)
    {
        session()->forget('data_savingsbankmutationadd');
        $sessiondata = session()->get('filter_savingsbankmutation');

        return $dataTable->render('content.AcctSavingsBankMutation.List.index', compact('sessiondata'));
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

        $sessiondata = array(
            'start_date' => $start_date,
            'end_date'   => $end_date
        );

        session()->put('filter_savingsbankmutation', $sessiondata);

        return redirect('savings-bank-mutation');
    }

    public function filterReset(){
        session()->forget('filter_savingsbankmutation');

        return redirect('savings-bank-mutation');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_savingsbankmutationadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['bank_account_id']                     = null;
            $sessiondata['mutation_id']                         = null;
            $sessiondata['savings_bank_mutation_amount']        = 0;
            $sessiondata['savings_bank_mutation_amount_adm']    = 0;
            $sessiondata['savings_bank_mutation_last_balance']  = 0;
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_savingsbankmutationadd', $sessiondata);
    }

    public function add()
    {
        $config                 = theme()->getOption('page', 'view');
        $sessiondata            = session()->get('data_savingsbankmutationadd');
        $membergender           = array_filter(Configuration::MemberGender());
        $memberidentity         = array_filter(Configuration::MemberIdentity());
        $familyrelationship     = array_filter(Configuration::FamilyRelationship());
        
        $acctmutation           = AcctMutation::select('mutation_id', 'mutation_name')
        ->where('mutation_module', 'TABB')
        ->where('data_state', 0)
        ->get();
        
        $acctbankaccount        = AcctBankAccount::select('bank_account_id', 'bank_account_name')
        ->where('data_state', 0)
        ->get();

        $acctsavingsaccount     = array();
        if(isset($sessiondata['savings_account_id'])){
            $acctsavingsaccount = AcctSavingsAccount::select('core_member.member_id', 'core_member.member_name', 'core_member.member_address', 'core_member.member_mother', 'core_member.member_identity_no', 'core_city.city_name', 'core_kecamatan.kecamatan_name','acct_savings_account.savings_account_pickup_date','acct_savings_account.unblock_state', 'acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.savings_id', 'acct_savings_account.savings_account_last_balance', 'acct_savings.savings_name')
            ->join('acct_savings', 'acct_savings.savings_id', '=', 'acct_savings_account.savings_id')
            ->join('core_member', 'core_member.member_id', '=', 'acct_savings_account.member_id')
            ->join('core_city', 'core_city.city_id', '=', 'core_member.city_id')
            ->join('core_kecamatan', 'core_kecamatan.kecamatan_id', '=', 'core_member.kecamatan_id')
            ->where('acct_savings_account.savings_account_id', $sessiondata['savings_account_id'])
            ->first();
        }

        return view('content.AcctSavingsBankMutation.Add.index', compact('sessiondata', 'membergender', 'memberidentity', 'familyrelationship', 'acctmutation', 'acctbankaccount', 'acctsavingsaccount'));
    }

    public function modalAcctSavingsAccount(AcctSavingsAccountDataTable $dataTable)
    {
        return $dataTable->render('content.AcctSavingsBankMutation.Add.AcctSavingsAccountModal.index');
    }

    public function selectAcctSavingsAccount($savings_account_id)
    {
        $sessiondata = session()->get('data_savingsbankmutationadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['bank_account_id']                     = null;
            $sessiondata['mutation_id']                         = null;
            $sessiondata['savings_bank_mutation_amount']        = 0;
            $sessiondata['savings_bank_mutation_amount_adm']    = 0;
            $sessiondata['savings_bank_mutation_last_balance']  = 0;
        }
        $sessiondata['savings_account_id'] = $savings_account_id;
        session()->put('data_savingsbankmutationadd', $sessiondata);

        return redirect('savings-bank-mutation/add');
    }

    public function processAdd(Request $request)
    {
        $preferencecompany = PreferenceCompany::first();

        $fields = request()->validate([
            'savings_account_id'            => ['required'],
            'bank_account_id'               => ['required'],
            'mutation_id'                   => ['required'],
            'savings_bank_mutation_amount'  => ['required'],
            'savings_bank_mutation_date'    => ['required'],
        ]);

        DB::beginTransaction();

        try {
            $data  = array(
                'savings_account_id'                    => $fields['savings_account_id'],
                'bank_account_id'                       => $fields['bank_account_id'],
                'mutation_id'                           => $fields['mutation_id'],
                'member_id'                             => $request->member_id,
                'savings_id'                            => $request->savings_id,
                'savings_bank_mutation_date'            => date('Y-m-d', strtotime($fields['savings_bank_mutation_date'])),
                'savings_bank_mutation_opening_balance' => $request->savings_bank_mutation_opening_balance,
                'savings_bank_mutation_amount'          => $fields['savings_bank_mutation_amount'],
                'savings_bank_mutation_amount_adm'      => $request->savings_bank_mutation_amount_adm,
                'savings_bank_mutation_last_balance'    => $request->savings_bank_mutation_last_balance,
                'savings_bank_mutation_remark'          => $request->savings_bank_mutation_remark,
                'branch_id'                             => auth()->user()->branch_id,
                'operated_name'                         => auth()->user()->username,
                'created_id'                            => auth()->user()->user_id,
            );
            AcctSavingsBankMutation::create($data);

			$transaction_module_code 		= "TTAB";
			$transaction_module_id 			= PreferenceTransactionModule::select('transaction_module_id')
            ->where('transaction_module_code', $transaction_module_code)
            ->first()
            ->transaction_module_id;
			
			$journal_voucher_period 		= date("Ym", strtotime($data['savings_bank_mutation_date']));

            $acctsavingsbank_last 			= AcctSavingsBankMutation::select('acct_savings_bank_mutation.savings_bank_mutation_id', 'acct_savings_bank_mutation.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_bank_mutation.member_id', 'core_member.member_name')
			->join('acct_savings_account','acct_savings_bank_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
			->join('core_member','acct_savings_bank_mutation.member_id', '=', 'core_member.member_id')
			->where('acct_savings_bank_mutation.created_id', $data['created_id'])
			->orderBy('acct_savings_bank_mutation.savings_bank_mutation_id','DESC')
            ->first();

            $data_journal = array(
                'branch_id'							=> auth()->user()->branch_id,
                'journal_voucher_period' 			=> $journal_voucher_period,
                'journal_voucher_date'				=> date('Y-m-d'),
                'journal_voucher_title'				=> 'MUTASI BANK '.$acctsavingsbank_last['member_name'],
                'journal_voucher_description'		=> 'MUTASI BANK '.$acctsavingsbank_last['member_name'],
                'transaction_module_id'				=> $transaction_module_id,
                'transaction_module_code'			=> $transaction_module_code,
                'transaction_journal_id' 			=> $acctsavingsbank_last['savings_bank_mutation_id'],
                'transaction_journal_no' 			=> $acctsavingsbank_last['savings_account_no'],
                'created_id' 						=> $data['created_id'],
            );
            AcctJournalVoucher::create($data_journal);

            $journal_voucher_id 					= AcctJournalVoucher::select('journal_voucher_id')
            ->where('acct_journal_voucher.created_id', $data['created_id'])
            ->orderBy('acct_journal_voucher.journal_voucher_id', 'DESC')
            ->first()
            ->journal_voucher_id;

            if($data['mutation_id'] == 7){
                $account_bank_id					= AcctBankAccount::select('account_id')
                ->where('bank_account_id', $data['bank_account_id'])
                ->first()
                ->account_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $account_bank_id)
                ->first()
                ->account_default_status;

                $data_debet = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_bank_id,
                    'journal_voucher_description'	=> 'SETORAN VIA BANK '.$acctsavingsbank_last['member_name'],
                    'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
                    'journal_voucher_debit_amount'	=> $data['savings_bank_mutation_amount'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                    'created_id'					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_debet);

                $account_id 						= AcctSavings::select('account_id')
                ->where('savings_id', $data['savings_id'])
                ->first()
                ->account_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $account_id)
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_id,
                    'journal_voucher_description'	=> 'SETORAN VIA BANK '.$acctsavingsbank_last['member_name'],
                    'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
                    'journal_voucher_credit_amount'	=> $data['savings_bank_mutation_amount'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                    'created_id'					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);

                if($data['savings_bank_mutation_amount_adm'] > 0){
                    $data_debet = array (
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $preferencecompany['account_cash_id'],
                        'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                        'journal_voucher_amount'		=> $data['savings_bank_mutation_amount_adm'],
                        'journal_voucher_debit_amount'	=> $data['savings_bank_mutation_amount_adm'],
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 0,
                        'created_id'					=> auth()->user()->user_id,
                    );
                    AcctJournalVoucherItem::create($data_debet);
                }

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $preferencecompany['account_mutation_adm_id'])
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_mutation_adm_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> $data['savings_bank_mutation_amount_adm'],
                    'journal_voucher_credit_amount'	=> $data['savings_bank_mutation_amount_adm'],
                    'account_id_status'				=> 1,
                    'created_id'					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
            } else if($data['mutation_id'] == 8){
                $account_id 						= AcctSavings::select('account_id')
                ->where('savings_id', $data['savings_id'])
                ->first()
                ->account_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $account_id)
                ->first()
                ->account_default_status;

                $data_debet =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_id,
                    'journal_voucher_description'	=> 'PENARIKAN VIA BANK '.$acctsavingsbank_last['member_name'],
                    'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
                    'journal_voucher_debit_amount'	=> $data['savings_bank_mutation_amount'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                    'created_id'					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_debet);

                $account_bank_id					= AcctBankAccount::select('account_id')
                ->where('bank_account_id', $data['bank_account_id'])
                ->first()
                ->account_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $account_bank_id)
                ->first()
                ->account_default_status;

                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_bank_id,
                    'journal_voucher_description'	=> 'PENARIKAN VIA BANK '.$acctsavingsbank_last['member_name'],
                    'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
                    'journal_voucher_credit_amount'	=> $data['savings_bank_mutation_amount'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                    'created_id'					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
                
                if($data['savings_bank_mutation_amount_adm'] > 0){
                    $data_debet = array (
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $preferencecompany['account_cash_id'],
                        'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                        'journal_voucher_amount'		=> $data['savings_bank_mutation_amount_adm'],
                        'journal_voucher_debit_amount'	=> $data['savings_bank_mutation_amount_adm'],
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 0,
                        'created_id'					=> auth()->user()->user_id,
                    );
                    AcctJournalVoucherItem::create($data_debet);
                }

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $preferencecompany['account_mutation_adm_id'])
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_mutation_adm_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> $data['savings_bank_mutation_amount_adm'],
                    'journal_voucher_credit_amount'	=> $data['savings_bank_mutation_amount_adm'],
                    'account_id_status'				=> 1,
                    'created_id'					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
            } else if($data['mutation_id'] == 3){
                $account_bank_id					= AcctBankAccount::select('account_id')
                ->where('bank_account_id', $data['bank_account_id'])
                ->first()
                ->account_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $account_bank_id)
                ->first()
                ->account_default_status;

                $data_debet = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_bank_id,
                    'journal_voucher_description'	=> 'KOREKSI KREDIT '.$acctsavingsbank_last['member_name'],
                    'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
                    'journal_voucher_debit_amount'	=> $data['savings_bank_mutation_amount'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                    'created_id'					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_debet);

                $account_id 						= AcctSavings::select('account_id')
                ->where('savings_id', $data['savings_id'])
                ->first()
                ->account_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $account_id)
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_id,
                    'journal_voucher_description'	=> 'KOREKSI KREDIT '.$acctsavingsbank_last['member_name'],
                    'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
                    'journal_voucher_credit_amount'	=> $data['savings_bank_mutation_amount'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                    'created_id'					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
            } else if($data['mutation_id'] == 4){
                $account_id 						= AcctSavings::select('account_id')
                ->where('savings_id', $data['savings_id'])
                ->first()
                ->account_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $account_id)
                ->first()
                ->account_default_status;

                $data_debet =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_id,
                    'journal_voucher_description'	=> 'KOREKSI DEBET '.$acctsavingsbank_last['member_name'],
                    'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
                    'journal_voucher_debit_amount'	=> $data['savings_bank_mutation_amount'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                    'created_id'					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_debet);

                $account_bank_id					= AcctBankAccount::select('account_id')
                ->where('bank_account_id', $data['bank_account_id'])
                ->first()
                ->account_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $account_bank_id)
                ->first()
                ->account_default_status;

                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_bank_id,
                    'journal_voucher_description'	=> 'KOREKSI DEBET '.$acctsavingsbank_last['member_name'],
                    'journal_voucher_amount'		=> $data['savings_bank_mutation_amount'],
                    'journal_voucher_credit_amount'	=> $data['savings_bank_mutation_amount'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                    'created_id'					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
            }


            DB::commit();
            $message = array(
                'pesan' => 'Tabungan berhasil ditambah',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Tabungan gagal ditambah',
                'alert' => 'error'
            );
        }
        
        return redirect('savings-bank-mutation')->with($message);
    }
}
