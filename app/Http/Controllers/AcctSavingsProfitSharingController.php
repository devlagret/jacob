<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsAccountDetail;
use App\Models\AcctSavingsAccountDetailTemp;
use App\Models\AcctSavingsAccountTemp;
use App\Models\AcctSavingsProfitSharing;
use App\Models\AcctSavingsProfitSharingTemp;
use App\Models\AcctSavingsProfitSharingLog;
use App\Models\AcctSavingsTransferMutation;
use App\Models\AcctSavingsTransferMutationTo;
use App\Models\CoreBranch;
use App\Models\CoreMember;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\SavingsProfitSharingLog;
use App\Models\SystemPeriodLog;
use App\DataTables\AcctSavingsProfitSharingDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Carbon\Carbon;

class AcctSavingsProfitSharingController extends Controller
{
    public function index()
    {
        $month          = Configuration::month();
        $year_period 	= date('Y');
        
        for($i = ($year_period - 2); $i < ($year_period + 2); $i++){
            $year[$i] 	= $i;
        }

        $period 	    = SystemPeriodLog::select('*')
        ->orderBy('period_log_id', 'DESC')
        ->first();
        // dd($period);
        $today = Carbon::today()->format('mY');
        // dd($today);
        if($period == null){
            $last_month     = substr($today,0,2);
            $next_month     = $last_month + 1;
        }else{
            $last_month     = substr($period['period'],0,2);
            $next_month     = $last_month + 1;
        }

        if($last_month == 12){
            $next_month = 1;
        } else {
            $next_month = $last_month + 1;
        }

        if($next_month < 10){
            $month_period = '0'.$next_month;
        } else {
            $month_period = $next_month;
        }

        return view('content.AcctSavingsProfitSharing.index', compact('month', 'year', 'year_period', 'month_period'));
    }

    public function listData(AcctSavingsProfitSharingDataTable $dataTable)
    {
        $month          = Configuration::month();

        $acctsavingsprofitsharingtemp   = AcctSavingsProfitSharingTemp::select('acct_savings_profit_sharing_temp.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_profit_sharing_temp.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_profit_sharing_temp.savings_profit_sharing_temp_amount', 'acct_savings_profit_sharing_temp.savings_account_last_balance', 'acct_savings_profit_sharing_temp.savings_profit_sharing_temp_period', 'acct_savings_profit_sharing_temp.savings_interest_temp_amount', 'acct_savings_profit_sharing_temp.savings_tax_temp_amount')
        ->join('acct_savings_account','acct_savings_profit_sharing_temp.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('core_member', 'acct_savings_profit_sharing_temp.member_id', '=', 'core_member.member_id')
        ->where('acct_savings_profit_sharing_temp.branch_id', auth()->user()->branch_id)
        ->get();

        return $dataTable->render('content.AcctSavingsProfitSharing.List.index', compact('month', 'acctsavingsprofitsharingtemp'));
    }

    public function processAdd(Request $request){
        $preferencecompany  = PreferenceCompany::first();

        $fields = request()->validate([
            'month_period'              => ['required'],
            'year_period'               => ['required'],
            'savings_account_minimum'   => ['required'],
        ]); 
        
        $data = array (
            'month_period' 	=> $fields['month_period'],
            'year_period'	=> $fields['year_period'],
            'saldo_minimal'	=> $fields['savings_account_minimum'],
        );

        $savings_profit_sharing_period 	= $data['month_period'].$data['year_period'];
        $last_date 	                    = date('t', strtotime($data['month_period']));
        $date 		                    = $data['year_period'].'-'.$data['month_period'].'-'.$last_date;

        AcctSavingsAccountTemp::truncate();
        AcctSavingsAccountDetailTemp::truncate();
        AcctSavingsProfitSharingTemp::truncate();

        DB::beginTransaction();

        try {
            $acctsavingsaccountforsrh   = AcctSavingsAccount::withoutGlobalScopes()->select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_savings_account.savings_id', 'acct_savings.savings_name', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.savings_account_daily_average_balance', 'acct_savings_account.branch_id')
			->join('core_member', 'acct_savings_account.member_id', '=', 'core_member.member_id')
			->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
            // ->where('acct_savings_account.branch_id', auth()->user()->branch_id)
			->where('acct_savings_account.data_state', 0)
			->where('acct_savings.savings_status', 0)
            ->get();

            foreach ($acctsavingsaccountforsrh as $key => $val) {
                $yesterday_transaction_date = AcctSavingsAccountDetail::select('today_transaction_date')
                ->where('savings_account_id', $val['savings_account_id'])
                ->orderBy('today_transaction_date', 'DESC')
                ->first()
                ->today_transaction_date;

                $last_balance_SRH           = AcctSavingsAccountDetail::select('last_balance')
                ->where('savings_account_id', $val['savings_account_id'])
                ->orderBy('today_transaction_date', 'DESC')
                ->first()
                ->last_balance;

                if(empty($last_balance_SRH)){
                    $last_balance_SRH = 0;
                }

                $last_date 	    = date('t', strtotime($data['month_period']));
                $date 		    = $data['year_period'].'-'.$data['month_period'].'-'.$last_date;
                $date1 		    = date_create($date);
                $date2 		    = date_create($yesterday_transaction_date);
                $interval       = $date1->diff($date2);
                $range_date     = $interval->days;

                if($range_date == 0){
                    $range_date = 1;
                }

                $daily_average_balance = ($last_balance_SRH * $range_date) / $last_date;

                $data_savings_account_detail_temp = array(
                    'savings_account_id'				=> $val['savings_account_id'],
                    'branch_id'							=> $val['branch_id'],
                    'savings_id'						=> $val['savings_id'],
                    'member_id'							=> $val['member_id'],
                    'today_transaction_date'			=> date('Y-m-d', strtotime($date)),
                    'yesterday_transaction_date'		=> $yesterday_transaction_date,
                    'transaction_code'					=> 'Penutupan Akhir Bulan',
                    'opening_balance'					=> $last_balance_SRH,
                    'last_balance'						=> $last_balance_SRH,
                    'daily_average_balance'				=> $daily_average_balance,
                    'operated_name'						=> 'SYSTEM',
                );
                AcctSavingsAccountDetailTemp::create($data_savings_account_detail_temp);
                
                $daily_average_balance_total = AcctSavingsAccountDetail::where('savings_account_id', $val['savings_account_id'])
                ->whereMonth('today_transaction_date', $data['month_period'])
                ->whereYear('today_transaction_date', $data['year_period'])
                ->orderBy('today_transaction_date', 'ASC')
                ->sum('daily_average_balance');
                
                $data_savings_account_temp = array (
                    'savings_account_id'					=> $val['savings_account_id'],
                    'branch_id'								=> $val['branch_id'],
                    'savings_id'							=> $val['savings_id'],
                    'savings_account_daily_average_balance' => $daily_average_balance_total + $daily_average_balance,
                );
                AcctSavingsAccountTemp::create($data_savings_account_temp);
            }

            $acctsavingsaccount = AcctSavingsAccount::withoutGlobalScopes()->select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.savings_id', 'acct_savings.savings_name', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.member_id', 'core_member.member_name', 'core_member.member_address', 'core_member.city_id', 'core_city.city_name', 'core_member.kecamatan_id', 'core_kecamatan.kecamatan_name', 'core_member.identity_id', 'core_member.member_identity_no', 'acct_savings_account.savings_account_daily_average_balance', 'acct_savings_account.branch_id')
			->join('core_member', 'acct_savings_account.member_id', '=', 'core_member.member_id')
			->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
			->join('core_city', 'core_member.city_id', '=', 'core_city.city_id')
			->join('core_kecamatan', 'core_member.kecamatan_id', '=', 'core_kecamatan.kecamatan_id')
			->join('acct_savings_account_temp', 'acct_savings_account.savings_account_id', '=', 'acct_savings_account_temp.savings_account_id')
			->where('acct_savings_account.data_state', 0)
			->where('acct_savings_account.savings_account_last_balance', '>=', $data['saldo_minimal'])
			// ->where('acct_savings_account.branch_id', auth()->user()->branch_id)
            ->get();

            foreach ($acctsavingsaccount as $k => $v) {
                $profitsharing  = AcctSavings::select('savings_interest_rate')
                ->where('savings_id', $v['savings_id'])
                ->first()
                ->savings_interest_rate;

                $savings_account_daily_average_balance 	= $v['savings_account_last_balance'];
                $savings_interest_temp_amount 			= ($savings_account_daily_average_balance * ($profitsharing / 12)) / 100;
                $savings_account_last_balance 			= $v['savings_account_last_balance'] + $savings_interest_temp_amount;

                if($savings_interest_temp_amount > $preferencecompany['tax_minimum_amount']){
                    $savings_tax_temp_amount = $savings_interest_temp_amount * $preferencecompany['tax_percentage'] / 100;
                }else{
                    $savings_tax_temp_amount = 0;
                }

                $savings_account_last_balance -= $savings_tax_temp_amount;

                $data_savings_profit_sharing_temp = array(
                    'savings_account_id'						=> $v['savings_account_id'],
                    'branch_id'									=> $v['branch_id'],
                    'savings_id'								=> $v['savings_id'],
                    'member_id'									=> $v['member_id'],
                    'savings_profit_sharing_temp_date'			=> date('Y-m-d', strtotime($date)),
                    'savings_daily_average_balance_minimum'		=> $data['saldo_minimal'],
                    'savings_daily_average_balance'				=> $savings_account_daily_average_balance,
                    'savings_profit_sharing_temp_amount'		=> $savings_interest_temp_amount,
                    'savings_profit_sharing_temp_period'		=> $savings_profit_sharing_period,
                    'savings_tax_temp_amount'					=> $savings_tax_temp_amount,
                    'savings_interest_temp_amount'				=> $savings_interest_temp_amount,
                    'savings_account_last_balance'				=> $savings_account_last_balance,
                    'operated_name'								=> 'SYSTEM',
                    'created_id'								=> auth()->user()->user_id,
                );
                AcctSavingsProfitSharingTemp::create($data_savings_profit_sharing_temp);
            }

            DB::commit();
            
            return redirect('savings-profit-sharing/list-data');
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Bunga Tabungan gagal dihitung',
                'alert' => 'error'
            );
            return redirect('savings-profit-sharing')->with($message);
        }
    }

    public function processUpdate(){
        $preferencecompany  = PreferenceCompany::first();
        $periode 	        = AcctSavingsProfitSharingTemp::select('savings_profit_sharing_temp_period')
        ->first()
        ->savings_profit_sharing_temp_period;

        $profit_sharing_log = SavingsProfitSharingLog::select('*')
        ->where('periode', $periode)
        ->first();

        if(empty($profit_sharing_log)){	
            DB::beginTransaction();

            try{
                $data_profit_sharing_log 	= array (
                    'branch_id'			    => auth()->user()->branch_id,
                    'created_id'		    => auth()->user()->user_id,
                    'created_on'		    => date('Y-m-d'),
                    'periode'			    => $periode,
                    'step'				    => 5,
                );

                // dd($data_profit_sharing_log);
                SavingsProfitSharingLog::create($data_profit_sharing_log);

                $data_system_period_log = array (
                    'period'		=> $periode,
                    'created_id'	=> auth()->user()->user_id,
                );
                SystemPeriodLog::create($data_system_period_log);

                $acctsavingsprofitsharingtemp = AcctSavingsProfitSharingTemp::get();

                foreach($acctsavingsprofitsharingtemp as $key => $val){
                    $data_profit_sharing = array(
                        'branch_id'                             => $val['branch_id'],
                        'savings_account_id'                    => $val['savings_account_id'],
                        'savings_id'                            => $val['savings_id'],
                        'member_id'                             => $val['member_id'],
                        'savings_profit_sharing_date'           => $val['savings_profit_sharing_temp_date'],
                        'savings_interest_rate'                 => 0,
                        'savings_daily_average_balance_minimum' => $val['savings_daily_average_balance_minimum'],
                        'savings_daily_average_balance'         => $val['savings_daily_average_balance'],
                        'savings_profit_sharing_amount'         => $val['savings_profit_sharing_temp_amount'],
                        'savings_interest_amount'               => $val['savings_interest_temp_amount'],
                        'savings_tax_amount'                    => $val['savings_tax_temp_amount'],
                        'savings_account_last_balance'          => $val['savings_account_last_balance'],
                        'savings_profit_sharing_period'         => $val['savings_profit_sharing_temp_period'],
                        'operated_name'                         => $val['operated_name'],
                        'created_id'                            => $val['created_id'],
                    );
                    AcctSavingsProfitSharing::create($data_profit_sharing);
                }

                $corebranch = CoreBranch::select('*')
                ->where('data_state', 0)
                ->get();

                foreach ($corebranch as $key => $vCB) {
                    $savings_profit_sharing_amount = AcctSavingsProfitSharingTemp::where('branch_id', $vCB['branch_id'])
                    ->sum('savings_profit_sharing_temp_amount');
                    
                    $savings_tax_amount 	= AcctSavingsProfitSharingTemp::where('branch_id', $vCB['branch_id'])
                    ->sum('savings_tax_temp_amount');

                    $data_transfer = array (
                        'branch_id'									=> $vCB['branch_id'],
                        'savings_transfer_mutation_date'			=> date('Y-m-d'),
                        'savings_transfer_mutation_amount'			=> $savings_profit_sharing_amount - $savings_tax_amount,
                        'operated_name'								=> 'SYSTEM',
                        'created_id'								=> auth()->user()->user_id,
                    );

                    AcctSavingsTransferMutation::create($data_transfer);

                    $savings_transfer_mutation_id = AcctSavingsTransferMutation::select('savings_transfer_mutation_id')
                    ->where('acct_savings_transfer_mutation.created_id', $data_transfer['created_id'])
                    ->orderBy('acct_savings_transfer_mutation.created_at', 'DESC')
                    ->first()
                    ->savings_transfer_mutation_id;

                    $savingsprofitsharingtemp = AcctSavingsProfitSharingTemp::select('*')
                    ->where('branch_id', $vCB['branch_id'])
                    ->get();

                    foreach($savingsprofitsharingtemp as $ktemp => $vtemp){
                        $data_transfer_to = array(
                            'savings_transfer_mutation_id'			=> $savings_transfer_mutation_id,
                            'savings_account_id'					=> $vtemp['savings_account_id'],
                            'savings_id'			                => $vtemp['savings_id'],
                            'branch_id'			                    => $vtemp['branch_id'],
                            'member_id'								=> $vtemp['member_id'],
                            'mutation_id'							=> $preferencecompany['savings_profit_sharing_id'],
                            'savings_transfer_mutation_to_amount'	=> $vtemp['savings_profit_sharing_temp_amount'] - $vtemp['savings_tax_temp_amount'],
                            'savings_account_last_balance'			=> $vtemp['savings_account_last_balance'],
                        );
                        AcctSavingsTransferMutationTo::create($data_transfer_to);
                    }

                    $acctsavings 			= AcctSavings::select('savings_id', 'savings_name')
                    ->where('data_state', 0)
                    ->where('savings_status', 0)
                    ->get();

                    //-------------------------------------Jurnal--------------------------------------------------------//
                    foreach ($acctsavings as $key => $val) {
                        $totalsavingsprofitsharing 	= AcctSavingsProfitSharing::where('savings_id', $val['savings_id'])
                        ->where('branch_id', $vCB['branch_id'])
                        ->sum('savings_profit_sharing_amount');

                        $totalsavingstax 	        = AcctSavingsProfitSharing::where('savings_id', $val['savings_id'])
                        ->where('branch_id', $vCB['branch_id'])
                        ->sum('savings_tax_amount');
                
                        $transaction_module_code 	= "BS";
                        $transaction_module_id 		= PreferenceTransactionModule::select('transaction_module_id')
                        ->where('transaction_module_code', $transaction_module_code)
                        ->first()
                        ->transaction_module_id;

                        $journal_voucher_period 	= $periode;
                        
                        $data_journal 				= array(
                            'branch_id'						=> $vCB['branch_id'],
                            'journal_voucher_period' 		=> $journal_voucher_period,
                            'journal_voucher_date'			=> date('Y-m-d'),
                            'journal_voucher_title'			=> 'JASA SIMPANAN '.$val['savings_name'].' PERIODE '.$journal_voucher_period,
                            'journal_voucher_description'	=> 'JASA SIMPANAN '.$val['savings_name'].' PERIODE '.$journal_voucher_period,
                            'transaction_module_id'			=> $transaction_module_id,
                            'transaction_module_code'		=> $transaction_module_code,
                            'created_id' 					=> auth()->user()->user_id,
                        );
                        AcctJournalVoucher::create($data_journal);

                        $journal_voucher_id 			= AcctJournalVoucher::select('journal_voucher_id')
                        ->where('created_id', $data_journal['created_id'])
                        ->orderBy('journal_voucher_id', 'DESC')
                        ->first()
                        ->journal_voucher_id;

                        $account_basil_id 				= AcctSavings::select('account_basil_id')
                        ->where('acct_savings.savings_id', $val['savings_id'])
                        ->first()
                        ->account_basil_id;

                        $account_id_default_status 		= AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $account_basil_id)
                        ->where('acct_account.data_state', 0)
                        ->first()
                        ->account_default_status;

                        $data_debet = array (
                            'journal_voucher_id'			=> $journal_voucher_id,
                            'account_id'					=> $account_basil_id,
                            'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                            'journal_voucher_amount'		=> $totalsavingsprofitsharing - $totalsavingstax,
                            'journal_voucher_debit_amount'	=> $totalsavingsprofitsharing - $totalsavingstax,
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 0,
                        );
                        AcctJournalVoucherItem::create($data_debet);

                        $account_id 					= AcctSavings::select('account_id')
                        ->where('savings_id', $val['savings_id'])
                        ->first()
                        ->account_id;

                        $account_id_default_status 		= AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $account_id)
                        ->where('acct_account.data_state', 0)
                        ->first()
                        ->account_default_status;

                        $data_credit =array(
                            'journal_voucher_id'			=> $journal_voucher_id,
                            'account_id'					=> $account_id,
                            'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                            'journal_voucher_amount'		=> $totalsavingsprofitsharing - $totalsavingstax,
                            'journal_voucher_credit_amount'	=> $totalsavingsprofitsharing - $totalsavingstax,
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 1,
                        );
                        AcctJournalVoucherItem::create($data_credit);
                    }
                    // foreach ($acctsavings as $key => $val) {
                    //     $totalsavingstax 	= AcctSavingsProfitSharingTemp::where('savings_id', $val['savings_id'])
                    //     ->where('branch_id', $vCB['branch_id'])
                    //     ->sum('savings_tax_temp_amount');
                
                    //     $transaction_module_code 	= "PS";
                    //     $transaction_module_id 		= PreferenceTransactionModule::select('transaction_module_id')
                    //     ->where('transaction_module_code', $transaction_module_code)
                    //     ->first()
                    //     ->transaction_module_id;

                    //     $journal_voucher_period 	= $periode;
                        
                    //     $data_journal 				= array(
                    //         'branch_id'						=> $vCB['branch_id'],
                    //         'journal_voucher_period' 		=> $journal_voucher_period,
                    //         'journal_voucher_date'			=> date('Y-m-d'),
                    //         'journal_voucher_title'			=> 'PAJAK SIMPANAN '.$val['savings_name'].' PERIODE '.$journal_voucher_period,
                    //         'journal_voucher_description'	=> 'PAJAK SIMPANAN '.$val['savings_name'].' PERIODE '.$journal_voucher_period,
                    //         'transaction_module_id'			=> $transaction_module_id,
                    //         'transaction_module_code'		=> $transaction_module_code,
                    //         'created_id' 					=> auth()->user()->user_id,
                    //     );
                    //     // AcctJournalVoucher::create($data_journal);

                    //     $journal_voucher_id 			= AcctJournalVoucher::select('journal_voucher_id')
                    //     ->where('created_id', $data_journal['created_id'])
                    //     ->orderBy('journal_voucher_id', 'DESC')
                    //     ->first()
                    //     ->journal_voucher_id;

                    //     $account_tax_id 				= $preferencecompany['account_savings_tax_id'];
                    //     $account_id_default_status      = AcctAccount::select('account_default_status')
                    //     ->where('acct_account.account_id', $account_tax_id)
                    //     ->where('acct_account.data_state', 0)
                    //     ->first()
                    //     ->account_default_status;

                    //     $data_debet = array (
                    //         'journal_voucher_id'			=> $journal_voucher_id,
                    //         'account_id'					=> $account_tax_id,
                    //         'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    //         'journal_voucher_amount'		=> $totalsavingstax,
                    //         'journal_voucher_debit_amount'	=> $totalsavingstax,
                    //         'account_id_default_status'		=> $account_id_default_status,
                    //         'account_id_status'				=> 0,
                    //     );
                    //     // AcctJournalVoucherItem::create($data_debet);

                    //     $account_id 					= AcctSavings::select('account_id')
                    //     ->where('savings_id', $val['savings_id'])
                    //     ->first()
                    //     ->account_id;

                    //     $account_id_default_status      = AcctAccount::select('account_default_status')
                    //     ->where('acct_account.account_id', $account_id)
                    //     ->where('acct_account.data_state', 0)
                    //     ->first()
                    //     ->account_default_status;

                    //     $data_credit =array(
                    //         'journal_voucher_id'			=> $journal_voucher_id,
                    //         'account_id'					=> $account_id,
                    //         'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    //         'journal_voucher_amount'		=> $totalsavingstax,
                    //         'journal_voucher_credit_amount'	=> $totalsavingstax,
                    //         'account_id_default_status'		=> $account_id_default_status,
                    //         'account_id_status'				=> 1,
                    //     );
                    //     // AcctJournalVoucherItem::create($data_credit);
                    // }
                }
                DB::commit();

                $message = array(
                    'pesan' => 'Bunga Tabungan selesai di proses',
                    'alert' => 'success'
                );
            } catch (\Exception $e) {
                DB::rollback();
                $message = array(
                    'pesan' => 'Bunga Tabungan gagal dihitung',
                    'alert' => 'error'
                );
            }
            return redirect('savings-profit-sharing')->with($message);
        } else {
            $message = array(
                'pesan' => 'Bunga Tabungan sudah selesai di proses',
                'alert' => 'success'
            );
            return redirect('savings-profit-sharing')->with($message);
        }
    }
    
}
