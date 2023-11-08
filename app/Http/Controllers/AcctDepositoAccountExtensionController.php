<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\AcctDepositoAccountExtensionDataTable;
use App\Models\AcctAccount;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\AcctDepositoAccountExtra;
use App\Models\AcctDepositoProfitSharing;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\CoreBranch;
use App\Models\CoreCity;
use App\Models\CoreKecamatan;
use App\Helpers\Configuration;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use Illuminate\Support\Facades\DB;

class AcctDepositoAccountExtensionController extends Controller
{
    public function index(AcctDepositoAccountExtensionDataTable $dataTable)
    {
        session()->forget('data_depositoaccountextension');
        $acctdeposito = AcctDeposito::select('deposito_id', 'deposito_name')
        ->where('data_state',0)
        ->orderBy('deposito_number','ASC')
        ->get();
        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $corebranch         = CoreBranch::where('data_state', 0)
            ->get();
        }else{
            $corebranch         = CoreBranch::where('data_state', 0)
            ->where('branch_id', $branch_id)
            ->get();
        }
        $sessiondata = session()->get('filter_depositoaccountextension');

        return $dataTable->render('content.AcctDepositoAccountExtension.List.index', compact('acctdeposito','corebranch','sessiondata'));
    }

    public function filter(Request $request)
    {
        $data = array(
            'deposito_id'   => $request->deposito_id,
            'branch_id'     => $request->branch_id,
        );

        session()->put('filter_depositoaccountextension', $data);

        return redirect('deposito-account-extension');
    }

    public function resetFilter()
    {
        session()->forget('filter_depositoaccountextension');

        return redirect('deposito-account-extension');
    }

    public function edit($deposito_account_id)
    {
        $acctdepositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_no','acct_deposito_account.deposito_account_id','acct_deposito_account.deposito_account_serial_no','acct_deposito.deposito_name','core_member.member_name','core_member.member_no','core_member.member_gender','core_member.member_address','core_member.city_id','core_member.kecamatan_id','core_member.identity_id','core_member.member_identity_no','acct_deposito_account.deposito_account_period','acct_deposito_account.deposito_account_amount','acct_deposito_account.deposito_account_date','acct_deposito_account.deposito_account_due_date','core_member.member_id','acct_deposito.deposito_id','acct_deposito_account.deposito_account_interest_amount','acct_deposito_account.deposito_account_amount','acct_deposito_account.savings_account_id')
        ->join('core_member', 'acct_deposito_account.member_id','=','core_member.member_id')
        ->join('acct_deposito', 'acct_deposito_account.deposito_id','=','acct_deposito.deposito_id')
        ->where('acct_deposito_account.data_state', 0)
        ->where('acct_deposito_account.deposito_account_id', $deposito_account_id)
        ->first();
        $membergender = Configuration::MemberGender();
        $memberidentity = Configuration::MemberIdentity();
        $datases = session()->get('data_depositoaccountextension');

        return view('content.AcctDepositoAccountExtension.Add.index', compact('acctdepositoaccount','membergender','memberidentity','datases'));
    }

    public function processEdit(Request $request)
    {
        $amount_administration =  $request->deposito_account_amount_adm;
        $data = array(
            'deposito_account_id'					=> $request->deposito_account_id,
            'deposito_id'							=> $request->deposito_id,
            'member_id'								=> $request->member_id,
            'branch_id'								=> auth()->user()->branch_id,
            'deposito_account_extra_period'			=> $request->deposito_account_extra_period,
            'deposito_account_extra_date'			=> date('Y-m-d',strtotime($request->deposito_account_extra_date)),
            'deposito_account_extra_due_date'		=> date('Y-m-d',strtotime($request->deposito_account_extra_due_date)),
            'created_id'							=> auth()->user()->user_id,
        );

        $data_update = array (
            'deposito_account_period'		=> $request->deposito_account_period + $request->deposito_account_extra_period,
            'deposito_account_due_date'		=> date('Y-m-d',strtotime($request->deposito_account_extra_due_date)),
        );

        $data_deposito = array (
            'deposito_account_due_date'		    => date('Y-m-d',strtotime($request->deposito_account_due_date)),
            'deposito_account_interest_amount'  => $request->deposito_account_interest_amount,
            'deposito_account_amount'		    => $request->deposito_account_amount,
            'savings_account_id'			    => $request->savings_account_id,
        );

        DB::beginTransaction();

        try {

            AcctDepositoAccountExtra::create($data);
            AcctDepositoAccount::where('deposito_account_id', $data['deposito_account_id'])
            ->update([
                'deposito_account_period'   => $data_update['deposito_account_period'],
                'deposito_account_due_date' => $data_update['deposito_account_due_date'],
                'updated_id'                => auth()->user()->user_id
            ]);

            $date 	= date('d', strtotime($data_deposito['deposito_account_due_date']));
            $month 	= date('m', strtotime($data_deposito['deposito_account_due_date']));
            $year 	= date('Y', strtotime($data_deposito['deposito_account_due_date']));

            for ($i=1; $i<= $data['deposito_account_extra_period']; $i++) { 
                $depositoprofitsharing = array ();

                $month = $month + 1;

                if($month == 13){
                    $month = 01;
                    $year = $year + 1;
                }

                $deposito_profit_sharing_due_date = $year.'-'.$month.'-'.$date;

                $depositoprofitsharing = array (
                    'deposito_account_id'				=> $data['deposito_account_id'],
                    'branch_id'							=> auth()->user()->branch_id,
                    'deposito_id'						=> $data['deposito_id'],
                    'deposito_account_interest_amount'  => $data_deposito['deposito_account_interest_amount'],
                    'member_id'							=> $data['member_id'],
                    'deposito_profit_sharing_due_date'	=> $deposito_profit_sharing_due_date,
                    'deposito_daily_average_balance'	=> $data_deposito['deposito_account_amount'],
                    'deposito_account_last_balance'		=> $data_deposito['deposito_account_amount'],
                    'savings_account_id'				=> $data_deposito['savings_account_id'],
                    'created_id'                        => auth()->user()->user_id,
                );

                $depositoprofitsharing_data = AcctDepositoProfitSharing::where('deposito_account_id', $depositoprofitsharing['deposito_account_id'])
                ->where('deposito_profit_sharing_due_date', $depositoprofitsharing['deposito_profit_sharing_due_date'])
                ->get();
                
                if(empty($depositoprofitsharing_data)){
                    AcctDepositoProfitSharing::create($depositoprofitsharing);
                }
                
            }
            if($amount_administration > 0){
                $transaction_module_code = "PDEP";

                $transaction_module_id 		= PreferenceTransactionModule::where('transaction_module_code',$transaction_module_code)
                ->first()
                ->transaction_module_id;
                $acctdepositoaccount_last 	= AcctDepositoAccount::select('core_member.member_name','acct_deposito_account.deposito_account_id','acct_deposito_account.deposito_account_no','acct_deposito.deposito_id')
                ->where('acct_deposito_account.deposito_account_id',$data['deposito_account_id'])
                ->join('core_member', 'acct_deposito_account.member_id','=','core_member.member_id')
                ->join('acct_deposito', 'acct_deposito_account.deposito_id','=','acct_deposito.deposito_id')
                ->where('acct_deposito_account.data_state', 0)
                ->first();

                $journal_voucher_period = date("Ym", strtotime($data['deposito_account_extra_due_date']));
                
                $data_journal = array(
                    'branch_id'						=> auth()->user()->branch_id,
                    'journal_voucher_period' 		=> $journal_voucher_period,
                    'journal_voucher_date'			=> date('Y-m-d'),
                    'journal_voucher_title'			=> 'PERPANJANGAN SIMP BERJANGKA '.$acctdepositoaccount_last->member_name,
                    'journal_voucher_description'	=> 'PERPANJANGAN SIMP BERJANGKA '.$acctdepositoaccount_last->member_name,
                    'transaction_module_id'			=> $transaction_module_id,
                    'transaction_module_code'		=> $transaction_module_code,
                    'transaction_journal_id' 		=> $acctdepositoaccount_last->deposito_account_id,
                    'transaction_journal_no' 		=> $acctdepositoaccount_last->deposito_account_no,
                    'created_id' 					=> auth()->user()->user_id,
                );
                
                AcctJournalVoucher::create($data_journal);

                $journal_voucher_id = AcctJournalVoucher::where('created_id',$data_journal['created_id'])
                ->orderBy('journal_voucher_id','DESC')
                ->first()
                ->journal_voucher_id;

                $preferencecompany = PreferenceCompany::first();

                $account_id = AcctDeposito::where('deposito_id',$acctdepositoaccount_last->deposito_id)
                ->first()
                ->account_id;

                $account_id_default_status = AcctAccount::where('account_id',$account_id)
                ->where('data_state',0)
                ->first()
                ->account_default_status;

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

                $preferencecompany = PreferenceCompany::first();

                $account_id_default_status = AcctAccount::where('account_id',$preferencecompany->account_mutation_adm_id)
                ->where('data_state',0)
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany->account_mutation_adm_id,
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
                'pesan' => 'Perpanjangan Simpanan Berjangka berhasil',
                'alert' => 'success',
            );
            return redirect('deposito-account-extension')->with($message);
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Perpanjangan Simpanan Berjangka gagal',
                'alert' => 'error'
            );
            return redirect('deposito-account-extension')->with($message);
        }

    }   

    public function elementsAdd(Request $request)
    {
        $datases = session()->get('data_depositoaccountextension');
        if(!$datases || $datases == ''){
            $datases['deposito_account_amount_adm']      = '';
            $datases['deposito_account_extra_period']    = '';
        }
        $datases[$request->name] = $request->value;
        session()->put('data_depositoaccountextension', $datases);
    }

    public function resetElementsAdd()
    {
        session()->forget('data_depositoaccountextension');

        return redirect()->back();
    }

    public static function getCityName($city_id)
    {
        $data = CoreCity::where('city_id', $city_id)
        ->where('data_state',0)
        ->first();

        return $data->city_name;
    }

    public static function getKecamatanName($kecamatan_id)
    {
        $data = CoreKecamatan::where('kecamatan_id', $kecamatan_id)
        ->where('data_state',0)
        ->first();

        return $data->kecamatan_name;
    }
}
