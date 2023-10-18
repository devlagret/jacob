<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctAccountBalanceDetail;
use App\Models\AcctAccountOpeningBalance;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavingsAccount;
use App\Models\CoreBranch;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AcctGeneralLedgerReportController extends Controller
{
    public function index()
    {
        $sessiondata = session()->get('filter_generalledgerreport');
        
        if (empty($sessiondata)){
            $sessiondata['start_month_period']  = date('m');
            $sessiondata['end_month_period']    = date('m');
            $sessiondata['year_period']         = date('Y');
            $sessiondata['account_id']          = null;
            $sessiondata['branch_id']           = auth()->user()->branch_id;
        }
        
        $monthlist  = array_filter(Configuration::Month());
        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state', 0)
        ->get();

        $acctaccount = AcctAccount::select('account_id', 'account_name')
        ->where('data_state', 0)
        ->get();
        
        $year_now 	=	date('Y');
        for($i=($year_now-2); $i<($year_now+2); $i++){
            $year[$i] = $i;
        }

        if($sessiondata['account_id'] != null){
            $accountname = AcctAccount::select(DB::raw("CONCAT(account_code,' - ',account_name) AS full_account"))
            ->where('account_id', $sessiondata['account_id'])
            ->first()
            ->full_account;
        }else{
            $accountname = "";
        }

        if($sessiondata['start_month_period'] == 01){
            $last_month_start 	= 12;
            $last_year 			= $sessiondata['year_period'] - 1;
        } else {
            $last_month_start 	= $sessiondata['start_month_period'] - 1;
            $last_year			= $sessiondata['year_period'];
        }

        $openingbalance 		= AcctAccountOpeningBalance::select('opening_balance')
        ->where('account_id', $sessiondata['account_id'])
        ->where('branch_id', $sessiondata['branch_id']);
        if(!empty($last_month_start)){
            $openingbalance 	= $openingbalance->where('month_period', $last_month_start)
            ->where('year_period', ($last_year))
            ->orderBy('account_opening_balance_id', 'DESC');
        } else {
            $openingbalance 	= $openingbalance->where('year_period', ($last_year))
            ->orderBy('month_period', 'ASC');
        }
        $openingbalance 		= $openingbalance->first();

        if(empty($openingbalance)){
            $openingbalance 	= AcctAccountOpeningBalance::select('opening_balance')
			->where('account_id', $sessiondata['account_id'])
			->where('branch_id', $sessiondata['branch_id'])
			->orderBy('month_period', 'ASC')
            ->first();
        }

        if(isset($openingbalance['opening_balance'])){
            $opening_balance = $openingbalance['opening_balance'];
        }else{
            $opening_balance = 0;
        }

//!=============================================================================================================================================
        
        //*Pakai AcctAccountBalanceDetail
        // $account_in_amount 		= AcctAccountBalanceDetail::where('account_id', $sessiondata['account_id'])
        // ->whereMonth('transaction_date', $last_month_start)
        // ->whereYear('transaction_date', $last_year)
        // ->where('branch_id', $sessiondata['branch_id'])
        // ->sum('account_in');

        // $account_out_amount 	= AcctAccountBalanceDetail::where('account_id', $sessiondata['account_id'])
        // ->whereMonth('transaction_date', $last_month_start)
        // ->whereYear('transaction_date', $last_year)
        // ->where('branch_id', $sessiondata['branch_id'])
        // ->sum('account_out');

        //*Pakai AccJournalVoucherItem
        $account_in_amount 		= AcctJournalVoucherItem::join('acct_journal_voucher', 'acct_journal_voucher.journal_voucher_id', '=', 'acct_journal_voucher_item.journal_voucher_id')
        ->where('acct_journal_voucher_item.account_id', $sessiondata['account_id'])
        ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
        ->whereColumn('acct_journal_voucher_item.account_id_status', 'acct_journal_voucher_item.account_id_default_status')
        ->whereMonth('acct_journal_voucher.journal_voucher_date', $last_month_start)
        ->whereYear('acct_journal_voucher.journal_voucher_date', $last_year)
        ->sum(DB::raw('acct_journal_voucher_item.journal_voucher_debit_amount + acct_journal_voucher_item.journal_voucher_credit_amount'));

        $account_out_amount 		= AcctJournalVoucherItem::join('acct_journal_voucher', 'acct_journal_voucher.journal_voucher_id', '=', 'acct_journal_voucher_item.journal_voucher_id')
        ->where('acct_journal_voucher_item.account_id', $sessiondata['account_id'])
        ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
        ->whereColumn('acct_journal_voucher_item.account_id_status', '<>', 'acct_journal_voucher_item.account_id_default_status')
        ->whereMonth('acct_journal_voucher.journal_voucher_date', $last_month_start)
        ->whereYear('acct_journal_voucher.journal_voucher_date', $last_year)
        ->sum(DB::raw('acct_journal_voucher_item.journal_voucher_debit_amount + acct_journal_voucher_item.journal_voucher_credit_amount'));

//!=============================================================================================================================================
        
        $opening_balance_amount = ($opening_balance + $account_in_amount) - $account_out_amount;

        $accountidstatus 		= AcctAccount::select('account_default_status')
        ->where('account_id', $sessiondata['account_id'])
        ->where('data_state', 0)
        ->first();

        if(isset($accountidstatus['account_default_status'])){
            $account_id_status = $accountidstatus['account_default_status'];
        }else{
            $account_id_status = 0;
        }

//!=============================================================================================================================================

        //*Pakai AcctAccountBalanceDetail
        $accountbalancedetail	= AcctAccountBalanceDetail::select('acct_account_balance_detail.account_balance_detail_id', 'acct_account_balance_detail.transaction_type', 'acct_account_balance_detail.transaction_code', 'acct_account_balance_detail.transaction_date', 'acct_account_balance_detail.transaction_id', 'acct_account_balance_detail.account_id', 'acct_account.account_code', 'acct_account.account_name', 'acct_account_balance_detail.opening_balance', 'acct_account_balance_detail.account_in', 'acct_account_balance_detail.account_out', 'acct_account_balance_detail.last_balance')
        ->join('acct_account', 'acct_account_balance_detail.account_id', '=', 'acct_account.account_id')
        ->where('acct_account_balance_detail.account_id', $sessiondata['account_id'])
        ->whereMonth('acct_account_balance_detail.transaction_date', '>=', $sessiondata['start_month_period'])
        ->whereMonth('acct_account_balance_detail.transaction_date', '<=', $sessiondata['end_month_period'])
        ->whereYear('acct_account_balance_detail.transaction_date', $sessiondata['year_period'])
        ->where('acct_account_balance_detail.branch_id', $sessiondata['branch_id'])
        ->orderBy('acct_account_balance_detail.transaction_date', 'ASC')	
        ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
        ->get();

        $acctgeneralledgerreport 	= array();
        if(!empty($accountbalancedetail)){
            $last_balance   = $opening_balance_amount;
            foreach ($accountbalancedetail as $key => $val) {
                $journalvoucheritem	= AcctJournalVoucherItem::select('journal_voucher_description')
                ->where('journal_voucher_id', $val['transaction_id'])
                ->where('account_id', $val['account_id'])
                ->first();

                if(isset($journalvoucheritem['journal_voucher_description'])){
                    $description = $journalvoucheritem['journal_voucher_description'];
                }else{
                    $description = '';
                }

                $journalvoucher = AcctJournalVoucher::select('journal_voucher_no')
                ->where('journal_voucher_id', $val['transaction_id'])
                ->first();

                if(isset($journalvoucher['journal_voucher_no'])){
                    $journal_voucher_no = $journalvoucher['journal_voucher_no'];
                }else{
                    $journal_voucher_no = '';
                }

                $last_balance = ($last_balance + $val['account_in']) - $val['account_out'];

                if($account_id_status == 0 ){
                    $debet 	= $val['account_in'];
                    $kredit = $val['account_out'];

                    if($last_balance >= 0){
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    } else {
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    }
                } else {
                    $debet 	= $val['account_out'];
                    $kredit = $val['account_in'];

                    if($last_balance >= 0){
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    } else {
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    }
                }

                $acctgeneralledgerreport[] = array (
                    'transaction_date'			=> $val['transaction_date'],
                    'transaction_no'			=> $journal_voucher_no,
                    'transaction_description'	=> $description,
                    'account_name'				=> $accountname,
                    'account_in'				=> $debet,
                    'account_out'				=> $kredit,
                    'last_balance_debet'		=> $last_balance_debet,
                    'last_balance_credit'		=> $last_balance_kredit,
                );
            }
        } else {
            $acctgeneralledgerreport 	= array();
            $opening_balance_amount 	= 0;
            $account_id_status 			= 0;
        }

        //*Pakai AcctJournalVoucherItem
        // $acctjournalvoucheritem = AcctjournalVoucheritem::select('acct_journal_voucher.journal_voucher_id', 'acct_journal_voucher.journal_voucher_date', 'acct_journal_voucher.journal_voucher_no', 'acct_journal_voucher.journal_voucher_description', 'acct_journal_voucher_item.journal_voucher_debit_amount', 'acct_journal_voucher_item.journal_voucher_credit_amount', 'acct_journal_voucher_item.account_id_status', 'acct_journal_voucher_item.account_id_default_status')
        // ->join('acct_account', 'acct_journal_voucher_item.account_id', '=', 'acct_account.account_id')
        // ->join('acct_journal_voucher', 'acct_journal_voucher_item.journal_voucher_id', '=', 'acct_journal_voucher.journal_voucher_id')
        // ->where('acct_journal_voucher_item.account_id', $sessiondata['account_id'])
        // ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
        // ->whereMonth('acct_journal_voucher.journal_voucher_date', '>=', $sessiondata['start_month_period'])
        // ->whereMonth('acct_journal_voucher.journal_voucher_date', '<=', $sessiondata['end_month_period'])
        // ->whereYear('acct_journal_voucher.journal_voucher_date', $sessiondata['year_period'])
        // ->orderBy('acct_journal_voucher.journal_voucher_date', 'ASC')	
        // ->orderBy('acct_journal_voucher_item.journal_voucher_item_id', 'ASC')
        // ->get();

        // $acctgeneralledgerreport 	= array();
        // if(!empty($acctjournalvoucheritem)){
        //     $last_balance   = $opening_balance_amount;
        //     foreach ($acctjournalvoucheritem as $key => $val) {
        //         if($val['account_id_status'] == $val['account_id_default_status']){
        //             $last_balance = $last_balance + ($val['journal_voucher_debit_amount']+$val['journal_voucher_credit_amount']);
        //         }else{
        //             $last_balance = $last_balance - ($val['journal_voucher_debit_amount']+$val['journal_voucher_credit_amount']);
        //         }

        //         $debet 	= $val['journal_voucher_debit_amount'];
        //         $kredit = $val['journal_voucher_credit_amount'];

        //         if($val['account_id_default_status'] == 0){
        //             if($last_balance >= 0){
        //                 $last_balance_debet 	= $last_balance;
        //                 $last_balance_kredit 	= 0;
        //             } else {
        //                 $last_balance_debet 	= 0;
        //                 $last_balance_kredit 	= $last_balance;
        //             }
        //         }else{
                    
        //             $debet 	= $val['journal_voucher_credit_amount'];
        //             $kredit = $val['journal_voucher_debit_amount'];
    
        //             if($last_balance >= 0){
        //                 $last_balance_debet 	= 0;
        //                 $last_balance_kredit 	= $last_balance;
        //             } else {
        //                 $last_balance_debet 	= $last_balance;
        //                 $last_balance_kredit 	= 0;
        //             }
        //         }


        //         $acctgeneralledgerreport[] = array (
        //             'transaction_date'			=> $val['journal_voucher_date'],
        //             'transaction_no'			=> $val['journal_voucher_no'],
        //             'transaction_description'	=> $val['journal_voucher_description'],
        //             'account_name'				=> $accountname,
        //             'account_in'				=> $debet,
        //             'account_out'				=> $kredit,
        //             'last_balance_debet'		=> $last_balance_debet,
        //             'last_balance_credit'		=> $last_balance_kredit,
        //         );
        //     }
        // } else {
        //     $acctgeneralledgerreport 	= array();
        //     $opening_balance_amount 	= 0;
        //     $account_id_status 			= 0;
        // }

//!=============================================================================================================================================\


        return view('content.AcctGeneralLedgerReport.List.index', compact('monthlist', 'year', 'corebranch', 'acctaccount', 'sessiondata', 'acctgeneralledgerreport', 'opening_balance_amount', 'account_id_status'));
    }

    public function filter(Request $request){
        if($request->start_month_period){
            $start_month_period = $request->start_month_period;
        }else{
            $start_month_period = date('m');
        }

        if($request->end_month_period){
            $end_month_period = $request->end_month_period;
        }else{
            $end_month_period = date('m');
        }

        if($request->year_period){
            $year_period = $request->year_period;
        }else{
            $year_period = date('Y');
        }

        if($request->account_id){
            $account_id = $request->account_id;
        }else{
            $account_id = null;
        }

        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = auth()->user()->branch_id;
        }

        $sessiondata = array(
            'start_month_period'    => $start_month_period,
            'end_month_period'      => $end_month_period,
            'year_period'           => $year_period,
            'account_id'            => $account_id,
            'branch_id'             => $branch_id
        );

        session()->put('filter_generalledgerreport', $sessiondata);

        return redirect('general-ledger-report');
    }

    public function filterReset(){
        session()->forget('filter_generalledgerreport');

        return redirect('general-ledger-report');
    }

    public function processPrinting(){
        $preferencecompany 	= PreferenceCompany::first();
        $monthlist 			= array_filter(Configuration::Month());
        $accounstatus 		= array_filter(Configuration::AccountStatus());
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);

        $sessiondata = session()->get('filter_generalledgerreport');
        
        if (empty($sessiondata)){
            $sessiondata['start_month_period']  = date('m');
            $sessiondata['end_month_period']    = date('m');
            $sessiondata['year_period']         = date('Y');
            $sessiondata['account_id']          = null;
            $sessiondata['branch_id']           = auth()->user()->branch_id;
        }

        if($sessiondata['account_id'] != null){
            $accountname = AcctAccount::select(DB::raw("CONCAT(account_code,' - ',account_name) AS full_account"))
            ->where('account_id', $sessiondata['account_id'])
            ->first()
            ->full_account;
        }else{
            $accountname = "";
        }

        if($sessiondata['start_month_period'] == 01){
            $last_month_start 	= 12;
            $last_year 			= $sessiondata['year_period'] - 1;
        } else {
            $last_month_start 	= $sessiondata['start_month_period'] - 1;
            $last_year			= $sessiondata['year_period'];
        }
        
        $openingbalance 		= AcctAccountOpeningBalance::select('opening_balance')
        ->where('account_id', $sessiondata['account_id'])
        ->where('branch_id', $sessiondata['branch_id']);
        if(!empty($last_month_start)){
            $openingbalance 	= $openingbalance->where('month_period', $last_month_start)
            ->where('year_period', ($last_year))
            ->orderBy('account_opening_balance_id', 'DESC');
        } else {
            $openingbalance 	= $openingbalance->where('year_period', ($last_year))
            ->orderBy('month_period', 'ASC');
        }
        $openingbalance 		= $openingbalance->first();

        if(empty($openingbalance)){
            $openingbalance 	= AcctAccountOpeningBalance::select('opening_balance')
			->where('account_id', $sessiondata['account_id'])
			->where('branch_id', $sessiondata['branch_id'])
			->orderBy('month_period', 'ASC')
            ->first();
        }

        if(isset($openingbalance['opening_balance'])){
            $opening_balance = $openingbalance['opening_balance'];
        }else{
            $opening_balance = 0;
        }

//!=============================================================================================================================================
        
        //*Pakai AcctAccountBalanceDetail
        // $account_in_amount 		= AcctAccountBalanceDetail::where('account_id', $sessiondata['account_id'])
        // ->whereMonth('transaction_date', $last_month_start)
        // ->whereYear('transaction_date', $last_year)
        // ->where('branch_id', $sessiondata['branch_id'])
        // ->sum('account_in');

        // $account_out_amount 	= AcctAccountBalanceDetail::where('account_id', $sessiondata['account_id'])
        // ->whereMonth('transaction_date', $last_month_start)
        // ->whereYear('transaction_date', $last_year)
        // ->where('branch_id', $sessiondata['branch_id'])
        // ->sum('account_out');

        //*Pakai AccJournalVoucherItem
        $account_in_amount 		= AcctJournalVoucherItem::join('acct_journal_voucher', 'acct_journal_voucher.journal_voucher_id', '=', 'acct_journal_voucher_item.journal_voucher_id')
        ->where('acct_journal_voucher_item.account_id', $sessiondata['account_id'])
        ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
        ->whereColumn('acct_journal_voucher_item.account_id_status', 'acct_journal_voucher_item.account_id_default_status')
        ->whereMonth('acct_journal_voucher.journal_voucher_date', $last_month_start)
        ->whereYear('acct_journal_voucher.journal_voucher_date', $last_year)
        ->sum(DB::raw('acct_journal_voucher_item.journal_voucher_debit_amount + acct_journal_voucher_item.journal_voucher_credit_amount'));

        $account_out_amount 		= AcctJournalVoucherItem::join('acct_journal_voucher', 'acct_journal_voucher.journal_voucher_id', '=', 'acct_journal_voucher_item.journal_voucher_id')
        ->where('acct_journal_voucher_item.account_id', $sessiondata['account_id'])
        ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
        ->whereColumn('acct_journal_voucher_item.account_id_status', '<>', 'acct_journal_voucher_item.account_id_default_status')
        ->whereMonth('acct_journal_voucher.journal_voucher_date', $last_month_start)
        ->whereYear('acct_journal_voucher.journal_voucher_date', $last_year)
        ->sum(DB::raw('acct_journal_voucher_item.journal_voucher_debit_amount + acct_journal_voucher_item.journal_voucher_credit_amount'));

//!=============================================================================================================================================
        
        $opening_balance_amount = ($opening_balance + $account_in_amount) - $account_out_amount;

        $accountidstatus 		= AcctAccount::select('account_default_status')
        ->where('account_id', $sessiondata['account_id'])
        ->where('data_state', 0)
        ->first();

        if(isset($accountidstatus['account_default_status'])){
            $account_id_status = $accountidstatus['account_default_status'];
        }else{
            $account_id_status = 0;
        }

//!=============================================================================================================================================

        //*Pakai AcctAccountBalanceDetail
        // $accountbalancedetail	= AcctAccountBalanceDetail::select('acct_account_balance_detail.account_balance_detail_id', 'acct_account_balance_detail.transaction_type', 'acct_account_balance_detail.transaction_code', 'acct_account_balance_detail.transaction_date', 'acct_account_balance_detail.transaction_id', 'acct_account_balance_detail.account_id', 'acct_account.account_code', 'acct_account.account_name', 'acct_account_balance_detail.opening_balance', 'acct_account_balance_detail.account_in', 'acct_account_balance_detail.account_out', 'acct_account_balance_detail.last_balance')
        // ->join('acct_account', 'acct_account_balance_detail.account_id', '=', 'acct_account.account_id')
        // ->where('acct_account_balance_detail.account_id', $sessiondata['account_id'])
        // ->whereMonth('acct_account_balance_detail.transaction_date', '>=', $sessiondata['start_month_period'])
        // ->whereMonth('acct_account_balance_detail.transaction_date', '<=', $sessiondata['end_month_period'])
        // ->whereYear('acct_account_balance_detail.transaction_date', $sessiondata['year_period'])
        // ->where('acct_account_balance_detail.branch_id', $sessiondata['branch_id'])
        // ->orderBy('acct_account_balance_detail.transaction_date', 'ASC')	
        // ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
        // ->get();
        
        // $acctgeneralledgerreport 	= array();
        // if(!empty($accountbalancedetail)){
        //     $last_balance   = $opening_balance_amount;
        //     foreach ($accountbalancedetail as $key => $val) {
        //         $journalvoucheritem 		= AcctJournalVoucherItem::select('journal_voucher_description')
        //         ->where('journal_voucher_id', $val['transaction_id'])
        //         ->where('account_id', $val['account_id'])
        //         ->first();

        //         if(isset($journalvoucheritem['journal_voucher_description'])){
        //             $description = $journalvoucheritem['journal_voucher_description'];
        //         }else{
        //             $description = '';
        //         }

        //         $journalvoucher = AcctJournalVoucher::select('journal_voucher_no')
        //         ->where('journal_voucher_id', $val['transaction_id'])
        //         ->first();

        //         if(isset($journalvoucher['journal_voucher_no'])){
        //             $journal_voucher_no = $journalvoucher['journal_voucher_no'];
        //         }else{
        //             $journal_voucher_no = '';
        //         }

        //         $last_balance = ($last_balance + $val['account_in']) - $val['account_out'];

        //         if($account_id_status == 0 ){
        //             $debet 	= $val['account_in'];
        //             $kredit = $val['account_out'];

        //             if($last_balance >= 0){
        //                 $last_balance_debet 	= $last_balance;
        //                 $last_balance_kredit 	= 0;
        //             } else {
        //                 $last_balance_debet 	= 0;
        //                 $last_balance_kredit 	= $last_balance;
        //             }
        //         } else {
        //             $debet 	= $val['account_out'];
        //             $kredit = $val['account_in'];

        //             if($last_balance >= 0){
        //                 $last_balance_debet 	= 0;
        //                 $last_balance_kredit 	= $last_balance;
        //             } else {
        //                 $last_balance_debet 	= $last_balance;
        //                 $last_balance_kredit 	= 0;
        //             }
        //         }

        //         $acctgeneralledgerreport[] = array (
        //             'transaction_date'			=> $val['transaction_date'],
        //             'transaction_no'			=> $journal_voucher_no,
        //             'transaction_description'	=> $description,
        //             'account_name'				=> $accountname,
        //             'account_in'				=> $debet,
        //             'account_out'				=> $kredit,
        //             'last_balance_debet'		=> $last_balance_debet,
        //             'last_balance_credit'		=> $last_balance_kredit,
        //         );
        //     }
        // } else {
        //     $acctgeneralledgerreport 	= array();
        //     $opening_balance_amount 	= 0;
        //     $account_id_status 			= 0;
        // }


        //*Pakai AcctJournalVoucherItem
        $acctjournalvoucheritem = AcctjournalVoucheritem::select('acct_journal_voucher.journal_voucher_id', 'acct_journal_voucher.journal_voucher_date', 'acct_journal_voucher.journal_voucher_no', 'acct_journal_voucher.journal_voucher_description', 'acct_journal_voucher_item.journal_voucher_debit_amount', 'acct_journal_voucher_item.journal_voucher_credit_amount', 'acct_journal_voucher_item.account_id_status', 'acct_journal_voucher_item.account_id_default_status')
        ->join('acct_account', 'acct_journal_voucher_item.account_id', '=', 'acct_account.account_id')
        ->join('acct_journal_voucher', 'acct_journal_voucher_item.journal_voucher_id', '=', 'acct_journal_voucher.journal_voucher_id')
        ->where('acct_journal_voucher_item.account_id', $sessiondata['account_id'])
        ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
        ->whereMonth('acct_journal_voucher.journal_voucher_date', '>=', $sessiondata['start_month_period'])
        ->whereMonth('acct_journal_voucher.journal_voucher_date', '<=', $sessiondata['end_month_period'])
        ->whereYear('acct_journal_voucher.journal_voucher_date', $sessiondata['year_period'])
        ->orderBy('acct_journal_voucher.journal_voucher_date', 'ASC')	
        ->orderBy('acct_journal_voucher_item.journal_voucher_item_id', 'ASC')
        ->get();

        $acctgeneralledgerreport 	= array();
        if(!empty($acctjournalvoucheritem)){
            $last_balance   = $opening_balance_amount;
            foreach ($acctjournalvoucheritem as $key => $val) {
                if($val['account_id_status'] == $val['account_id_default_status']){
                    $last_balance = $last_balance + ($val['journal_voucher_debit_amount']+$val['journal_voucher_credit_amount']);
                }else{
                    $last_balance = $last_balance - ($val['journal_voucher_debit_amount']+$val['journal_voucher_credit_amount']);
                }

                $debet 	= $val['journal_voucher_debit_amount'];
                $kredit = $val['journal_voucher_credit_amount'];

                if($val['account_id_default_status'] == 0){
                    if($last_balance >= 0){
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    } else {
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    }
                }else{
                    if($last_balance >= 0){
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    } else {
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    }
                }


                $acctgeneralledgerreport[] = array (
                    'transaction_date'			=> $val['journal_voucher_date'],
                    'transaction_no'			=> $val['journal_voucher_no'],
                    'transaction_description'	=> $val['journal_voucher_description'],
                    'account_name'				=> $accountname,
                    'account_in'				=> $debet,
                    'account_out'				=> $kredit,
                    'last_balance_debet'		=> $last_balance_debet,
                    'last_balance_credit'		=> $last_balance_kredit,
                );
            }
        } else {
            $acctgeneralledgerreport 	= array();
            $opening_balance_amount 	= 0;
            $account_id_status 			= 0;
        }

//!============================================================================================================================================

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

        $pdf::SetFont('helvetica', '', 8);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $export = "
        
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td><div style=\"text-align: center;font-size:12; font-weight:bold\">BUKU BESAR</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center;font-size:12;\">Periode : ".$monthlist[$sessiondata['start_month_period']].' - '.$monthlist[$sessiondata['end_month_period']].' '.$sessiondata['year_period']."</div></td>
            </tr>					
        </table>
        <br>";
        
        $export .= "
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td width=\"20%\"><div style=\"text-align: lef=ft; font-size:12px;font-weight: bold\">Nama. Perkiraan</div></td>
                <td width=\"5%\"><div style=\"text-align: center; font-size:12px; font-weight: bold\">:</div></td>
                <td width=\"65%\"><div style=\"text-align: left; font-size:12px; font-weight: bold\">".$accountname."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: lef=ft; font-size:12px;font-weight: bold\">Posisi Saldo</div></td>
                <td width=\"5%\"><div style=\"text-align: center; font-size:12px; font-weight: bold\">:</div></td>
                <td width=\"65%\"><div style=\"text-align: left; font-size:12px; font-weight: bold\">".$accounstatus[$account_id_status]."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: lef=ft; font-size:12px;font-weight: bold\">Saldo Awal</div></td>
                <td width=\"5%\"><div style=\"text-align: center; font-size:12px; font-weight: bold\">:</div></td>
                <td width=\"65%\"><div style=\"text-align: left; font-size:12px; font-weight: bold\">".number_format($opening_balance_amount, 2)."</div></td>
            </tr>
        </table>
        <br><br>";
        
        $no = 1;
        $export .= "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
            <tr>
                <td width=\"5%\" rowspan=\"2\"><div style=\"text-align: center; font-weight:bold\">No</div></td>
                <td width=\"12%\" rowspan=\"2\"><div style=\"text-align: center; font-weight:bold\">Tanggal</div></td>
                <td width=\"25%\" rowspan=\"2\"><div style=\"text-align: center; font-weight:bold\">Uraian</div></td>
                <td width=\"15%\" rowspan=\"2\"><div style=\"text-align: center; font-weight:bold\">Debet </div></td>
                <td width=\"15%\" rowspan=\"2\"><div style=\"text-align: center; font-weight:bold\">Kredit </div></td>
                <td width=\"30%\" colspan=\"2\"><div style=\"text-align: center; font-weight:bold\">Saldo </div></td>
            </tr>
            
            <tr>
                <td width=\"15%\"><div style=\"text-align: center; font-weight:bold\">Debet </div></td>
                <td width=\"15%\"><div style=\"text-align: center; font-weight:bold\">Kredit </div></td>
            </tr>";

        $no                         = 1;
        $total_debit                = 0;
        $total_kredit               = 0;
        $total_last_balance_debet   = 0;
        $total_last_balance_credit  = 0; 
        foreach ($acctgeneralledgerreport as $key => $val) {
            $export .="
            <tr>			
                <td style=\"text-align:center\">$no.</td>
                <td style=\"text-align:center\">".date('d-m-Y', strtotime($val['transaction_date']))."</td>
                <td>".$val['transaction_description']."</td>
                <td><div style=\"text-align: right;\">".number_format($val['account_in'], 2)."</div></td>
                <td><div style=\"text-align: right;\">".number_format($val['account_out'], 2)."</div></td>
                <td><div style=\"text-align: right;\">".number_format($val['last_balance_debet'], 2)."</div></td>
                <td><div style=\"text-align: right;\">".number_format($val['last_balance_credit'], 2)."</div></td>
            </tr>";

            $total_debit                += $val['account_in'];
            $total_kredit               += $val['account_out'];
            $total_last_balance_debet   = $val['last_balance_debet'];
            $total_last_balance_credit  = $val['last_balance_credit']; 
            $no++;
        }
        $export .= "
            <tr>			
                <td colspan=\"3\" style=\"text-align:center; font-weight:bold\">Total Debet Kredit</td>
                <td style=\"text-align:right\">".number_format($total_debit, 2)."</td>
                <td style=\"text-align:right\">".number_format($total_kredit, 2)."</td>
                <td style=\"text-align:right\"></td>
                <td style=\"text-align:right\"></td>
            </tr>
            <tr>			
                <td colspan=\"3\" style=\"text-align:center; font-weight:bold\">Saldo Akhir</td>
                <td style=\"text-align:right\"></td>
                <td style=\"text-align:right\"></td>
                <td style=\"text-align:right\">".number_format($total_last_balance_debet, 2)."</td>
                <td style=\"text-align:right\">".number_format($total_last_balance_credit, 2)."</td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Buku Besar '.$accountname.'.pdf';
        $pdf::Output($filename, 'I');
    }

    public function export(){
        $spreadsheet        = new Spreadsheet();
        $preferencecompany 	= PreferenceCompany::first();
        $monthlist 			= array_filter(Configuration::Month());
        $accounstatus 		= array_filter(Configuration::AccountStatus());

        $sessiondata = session()->get('filter_generalledgerreport');
        
        if (empty($sessiondata)){
            $sessiondata['start_month_period']  = date('m');
            $sessiondata['end_month_period']    = date('m');
            $sessiondata['year_period']         = date('Y');
            $sessiondata['account_id']          = null;
            $sessiondata['branch_id']           = auth()->user()->branch_id;
        }

        if($sessiondata['account_id'] != null){
            $accountname = AcctAccount::select(DB::raw("CONCAT(account_code,' - ',account_name) AS full_account"))
            ->where('account_id', $sessiondata['account_id'])
            ->first()
            ->full_account;
        }else{
            $accountname = "";
        }

        if($sessiondata['start_month_period'] == 01){
            $last_month_start 	= 12;
            $last_year 			= $sessiondata['year_period'] - 1;
        } else {
            $last_month_start 	= $sessiondata['start_month_period'] - 1;
            $last_year			= $sessiondata['year_period'];
        }
        
        $openingbalance 		= AcctAccountOpeningBalance::select('opening_balance')
        ->where('account_id', $sessiondata['account_id'])
        ->where('branch_id', $sessiondata['branch_id']);
        if(!empty($last_month_start)){
            $openingbalance 	= $openingbalance->where('month_period', $last_month_start)
            ->where('year_period', ($last_year))
            ->orderBy('account_opening_balance_id', 'DESC');
        } else {
            $openingbalance 	= $openingbalance->where('year_period', ($last_year))
            ->orderBy('month_period', 'ASC');
        }
        $openingbalance 		= $openingbalance->first();

        if(empty($openingbalance)){
            $openingbalance 	= AcctAccountOpeningBalance::select('opening_balance')
			->where('account_id', $sessiondata['account_id'])
			->where('branch_id', $sessiondata['branch_id'])
			->orderBy('month_period', 'ASC')
            ->first();
        }

        if(isset($openingbalance['opening_balance'])){
            $opening_balance = $openingbalance['opening_balance'];
        }else{
            $opening_balance = 0;
        }

//!=============================================================================================================================================
        
        //*Pakai AcctAccountBalanceDetail
        // $account_in_amount 		= AcctAccountBalanceDetail::where('account_id', $sessiondata['account_id'])
        // ->whereMonth('transaction_date', $last_month_start)
        // ->whereYear('transaction_date', $last_year)
        // ->where('branch_id', $sessiondata['branch_id'])
        // ->sum('account_in');

        // $account_out_amount 	= AcctAccountBalanceDetail::where('account_id', $sessiondata['account_id'])
        // ->whereMonth('transaction_date', $last_month_start)
        // ->whereYear('transaction_date', $last_year)
        // ->where('branch_id', $sessiondata['branch_id'])
        // ->sum('account_out');

        //*Pakai AccJournalVoucherItem
        $account_in_amount 		= AcctJournalVoucherItem::join('acct_journal_voucher', 'acct_journal_voucher.journal_voucher_id', '=', 'acct_journal_voucher_item.journal_voucher_id')
        ->where('acct_journal_voucher_item.account_id', $sessiondata['account_id'])
        ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
        ->whereColumn('acct_journal_voucher_item.account_id_status', 'acct_journal_voucher_item.account_id_default_status')
        ->whereMonth('acct_journal_voucher.journal_voucher_date', $last_month_start)
        ->whereYear('acct_journal_voucher.journal_voucher_date', $last_year)
        ->sum(DB::raw('acct_journal_voucher_item.journal_voucher_debit_amount + acct_journal_voucher_item.journal_voucher_credit_amount'));

        $account_out_amount 		= AcctJournalVoucherItem::join('acct_journal_voucher', 'acct_journal_voucher.journal_voucher_id', '=', 'acct_journal_voucher_item.journal_voucher_id')
        ->where('acct_journal_voucher_item.account_id', $sessiondata['account_id'])
        ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
        ->whereColumn('acct_journal_voucher_item.account_id_status', '<>', 'acct_journal_voucher_item.account_id_default_status')
        ->whereMonth('acct_journal_voucher.journal_voucher_date', $last_month_start)
        ->whereYear('acct_journal_voucher.journal_voucher_date', $last_year)
        ->sum(DB::raw('acct_journal_voucher_item.journal_voucher_debit_amount + acct_journal_voucher_item.journal_voucher_credit_amount'));

//!============================================================================================================================================
        
        $opening_balance_amount = ($opening_balance + $account_in_amount) - $account_out_amount;

        $accountidstatus 		= AcctAccount::select('account_default_status')
        ->where('account_id', $sessiondata['account_id'])
        ->where('data_state', 0)
        ->first();

        if(isset($accountidstatus['account_default_status'])){
            $account_id_status = $accountidstatus['account_default_status'];
        }else{
            $account_id_status = 0;
        }
        
//!============================================================================================================================================

        //*Pakai AcctAccountBalanceDetail
        // $accountbalancedetail	= AcctAccountBalanceDetail::select('acct_account_balance_detail.account_balance_detail_id', 'acct_account_balance_detail.transaction_type', 'acct_account_balance_detail.transaction_code', 'acct_account_balance_detail.transaction_date', 'acct_account_balance_detail.transaction_id', 'acct_account_balance_detail.account_id', 'acct_account.account_code', 'acct_account.account_name', 'acct_account_balance_detail.opening_balance', 'acct_account_balance_detail.account_in', 'acct_account_balance_detail.account_out', 'acct_account_balance_detail.last_balance')
        // ->join('acct_account', 'acct_account_balance_detail.account_id', '=', 'acct_account.account_id')
        // ->where('acct_account_balance_detail.account_id', $sessiondata['account_id'])
        // ->whereMonth('acct_account_balance_detail.transaction_date', '>=', $sessiondata['start_month_period'])
        // ->whereMonth('acct_account_balance_detail.transaction_date', '<=', $sessiondata['end_month_period'])
        // ->whereYear('acct_account_balance_detail.transaction_date', $sessiondata['year_period'])
        // ->where('acct_account_balance_detail.branch_id', $sessiondata['branch_id'])
        // ->orderBy('acct_account_balance_detail.transaction_date', 'ASC')	
        // ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
        // ->get();
        
        // $acctgeneralledgerreport 	= array();
        // if(!empty($accountbalancedetail)){
        //     $last_balance   = $opening_balance_amount;
        //     foreach ($accountbalancedetail as $key => $val) {
        //         $journalvoucheritem 		= AcctJournalVoucherItem::select('journal_voucher_description')
        //         ->where('journal_voucher_id', $val['transaction_id'])
        //         ->where('account_id', $val['account_id'])
        //         ->first();

        //         if(isset($journalvoucheritem['journal_voucher_description'])){
        //             $description = $journalvoucheritem['journal_voucher_description'];
        //         }else{
        //             $description = '';
        //         }

        //         $journalvoucher = AcctJournalVoucher::select('journal_voucher_no')
        //         ->where('journal_voucher_id', $val['transaction_id'])
        //         ->first();

        //         if(isset($journalvoucher['journal_voucher_no'])){
        //             $journal_voucher_no = $journalvoucher['journal_voucher_no'];
        //         }else{
        //             $journal_voucher_no = '';
        //         }

        //         $last_balance = ($last_balance + $val['account_in']) - $val['account_out'];

        //         if($account_id_status == 0 ){
        //             $debet 	= $val['account_in'];
        //             $kredit = $val['account_out'];

        //             if($last_balance >= 0){
        //                 $last_balance_debet 	= $last_balance;
        //                 $last_balance_kredit 	= 0;
        //             } else {
        //                 $last_balance_debet 	= 0;
        //                 $last_balance_kredit 	= $last_balance;
        //             }
        //         } else {
        //             $debet 	= $val['account_out'];
        //             $kredit = $val['account_in'];

        //             if($last_balance >= 0){
        //                 $last_balance_debet 	= 0;
        //                 $last_balance_kredit 	= $last_balance;
        //             } else {
        //                 $last_balance_debet 	= $last_balance;
        //                 $last_balance_kredit 	= 0;
        //             }
        //         }

        //         $acctgeneralledgerreport[] = array (
        //             'transaction_date'			=> $val['transaction_date'],
        //             'transaction_no'			=> $journal_voucher_no,
        //             'transaction_description'	=> $description,
        //             'account_name'				=> $accountname,
        //             'account_in'				=> $debet,
        //             'account_out'				=> $kredit,
        //             'last_balance_debet'		=> $last_balance_debet,
        //             'last_balance_credit'		=> $last_balance_kredit,
        //         );
        //     }
        // } else {
        //     $acctgeneralledgerreport 	= array();
        //     $opening_balance_amount 	= 0;
        //     $account_id_status 			= 0;
        // }
        

        //*Pakai AcctJournalVoucherItem
        $acctjournalvoucheritem = AcctjournalVoucheritem::select('acct_journal_voucher.journal_voucher_id', 'acct_journal_voucher.journal_voucher_date', 'acct_journal_voucher.journal_voucher_no', 'acct_journal_voucher.journal_voucher_description', 'acct_journal_voucher_item.journal_voucher_debit_amount', 'acct_journal_voucher_item.journal_voucher_credit_amount', 'acct_journal_voucher_item.account_id_status', 'acct_journal_voucher_item.account_id_default_status')
        ->join('acct_account', 'acct_journal_voucher_item.account_id', '=', 'acct_account.account_id')
        ->join('acct_journal_voucher', 'acct_journal_voucher_item.journal_voucher_id', '=', 'acct_journal_voucher.journal_voucher_id')
        ->where('acct_journal_voucher_item.account_id', $sessiondata['account_id'])
        ->where('acct_journal_voucher.branch_id', $sessiondata['branch_id'])
        ->whereMonth('acct_journal_voucher.journal_voucher_date', '>=', $sessiondata['start_month_period'])
        ->whereMonth('acct_journal_voucher.journal_voucher_date', '<=', $sessiondata['end_month_period'])
        ->whereYear('acct_journal_voucher.journal_voucher_date', $sessiondata['year_period'])
        ->orderBy('acct_journal_voucher.journal_voucher_date', 'ASC')	
        ->orderBy('acct_journal_voucher_item.journal_voucher_item_id', 'ASC')
        ->get();

        $acctgeneralledgerreport 	= array();
        if(!empty($acctjournalvoucheritem)){
            $last_balance   = $opening_balance_amount;
            foreach ($acctjournalvoucheritem as $key => $val) {
                if($val['account_id_status'] == $val['account_id_default_status']){
                    $last_balance = $last_balance + ($val['journal_voucher_debit_amount']+$val['journal_voucher_credit_amount']);
                }else{
                    $last_balance = $last_balance - ($val['journal_voucher_debit_amount']+$val['journal_voucher_credit_amount']);
                }

                $debet 	= $val['journal_voucher_debit_amount'];
                $kredit = $val['journal_voucher_credit_amount'];

                if($val['account_id_default_status'] == 0){
                    if($last_balance >= 0){
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    } else {
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    }
                }else{
                    if($last_balance >= 0){
                        $last_balance_debet 	= 0;
                        $last_balance_kredit 	= $last_balance;
                    } else {
                        $last_balance_debet 	= $last_balance;
                        $last_balance_kredit 	= 0;
                    }
                }


                $acctgeneralledgerreport[] = array (
                    'transaction_date'			=> $val['journal_voucher_date'],
                    'transaction_no'			=> $val['journal_voucher_no'],
                    'transaction_description'	=> $val['journal_voucher_description'],
                    'account_name'				=> $accountname,
                    'account_in'				=> $debet,
                    'account_out'				=> $kredit,
                    'last_balance_debet'		=> $last_balance_debet,
                    'last_balance_credit'		=> $last_balance_kredit,
                );
            }
        } else {
            $acctgeneralledgerreport 	= array();
            $opening_balance_amount 	= 0;
            $account_id_status 			= 0;
        }

//!============================================================================================================================================

        if(count($acctgeneralledgerreport)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Buku Besar")
                                            ->setSubject("")
                                            ->setDescription("Buku Besar")
                                            ->setKeywords("Buku Besar")
                                            ->setCategory("Buku Besar");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Buku Besar");

            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(70);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    
            $spreadsheet->getActiveSheet()->mergeCells("B1:G1");
            $spreadsheet->getActiveSheet()->mergeCells("B8:B9");
            $spreadsheet->getActiveSheet()->mergeCells("C8:C9");
            $spreadsheet->getActiveSheet()->mergeCells("D8:D9");
            $spreadsheet->getActiveSheet()->mergeCells("E8:E9");
            $spreadsheet->getActiveSheet()->mergeCells("F8:F9");
            $spreadsheet->getActiveSheet()->mergeCells("G8:H8");
            $spreadsheet->getActiveSheet()->mergeCells("B5:C5");
            $spreadsheet->getActiveSheet()->mergeCells("B6:C6");
            $spreadsheet->getActiveSheet()->mergeCells("B7:C7");

            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B8:H8')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B9:H9')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B8:H8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B9:H9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B5:D5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('B6:D6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('B7:D7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('B5:D5')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('B6:D6')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('B7:D7')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('D7')->getNumberFormat()->setFormatCode('0.00');

            $spreadsheet->getActiveSheet()->setCellValue('B1',"Buku Besar Dari Periode ".$monthlist[$sessiondata['start_month_period']]." - ".$monthlist[$sessiondata['end_month_period']]." ".$sessiondata['year_period']);	
            $spreadsheet->getActiveSheet()->setCellValue('B5',"Nama Perkiraan");
            $spreadsheet->getActiveSheet()->setCellValue('D5', $accountname);
            $spreadsheet->getActiveSheet()->setCellValue('B6',"Posisi Saldo");
            $spreadsheet->getActiveSheet()->setCellValue('D6',$accounstatus[$account_id_status]);
            $spreadsheet->getActiveSheet()->setCellValue('B7',"Saldo Awal");
            $spreadsheet->getActiveSheet()->setCellValue('D7',$opening_balance_amount);
            $spreadsheet->getActiveSheet()->setCellValue('B8',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C8',"Tanggal");
            $spreadsheet->getActiveSheet()->setCellValue('D8',"Uraian");
            $spreadsheet->getActiveSheet()->setCellValue('E8',"Debet");
            $spreadsheet->getActiveSheet()->setCellValue('F8',"Kredit");
            $spreadsheet->getActiveSheet()->setCellValue('G8',"Saldo");
            $spreadsheet->getActiveSheet()->setCellValue('G9',"Debet");
            $spreadsheet->getActiveSheet()->setCellValue('H9',"Kredit");
            
            $last_balance_debet 	= 0;
            $last_balance_credit 	= 0;
            $total_debet 			= 0;
            $total_kredit			= 0;
            $row                    = 10;
            $no	                    = 0;
            foreach($acctgeneralledgerreport as $key=>$val){
                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getActiveSheet()->getStyle('B'.$row.':H'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('E'.$row.':H'.$row)->getNumberFormat()->setFormatCode('0.00');
                $spreadsheet->getActiveSheet()->getStyle('B'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('C'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('D'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('E'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('F'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('G'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('H'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $no++;
                $spreadsheet->getActiveSheet()->setCellValue('B'.$row, $no);
                $spreadsheet->getActiveSheet()->setCellValue('C'.$row, date('d-m-Y', strtotime($val['transaction_date'])));
                $spreadsheet->getActiveSheet()->setCellValue('D'.$row, $val['transaction_description']);
                $spreadsheet->getActiveSheet()->setCellValue('E'.$row, $val['account_in']);
                $spreadsheet->getActiveSheet()->setCellValue('F'.$row, $val['account_out']);
                $spreadsheet->getActiveSheet()->setCellValue('G'.$row, $val['last_balance_debet']);
                $spreadsheet->getActiveSheet()->setCellValue('H'.$row, $val['last_balance_credit']);

                $last_balance_debet 	= $val['last_balance_debet'];
                $last_balance_credit 	= $val['last_balance_credit'];
                $total_debet 			+= $val['account_in'];
                $total_kredit			+= $val['account_out'];
                $row++;
            }

            $spreadsheet->getActiveSheet()->mergeCells('B'.($row).':D'.($row));
            $spreadsheet->getActiveSheet()->mergeCells('B'.($row+1).':D'.($row+1));

            $spreadsheet->getActiveSheet()->getStyle('B'.($row).':B'.($row+1))->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('B'.($row).':B'.($row+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B'.($row).':H'.($row+1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->setCellValue('B'.$row, 'Total Debet Kredit');
            $spreadsheet->getActiveSheet()->setCellValue('B'.$row+1, 'Saldo Akhir');
            $spreadsheet->getActiveSheet()->setCellValue('E'.$row, $total_debet);
            $spreadsheet->getActiveSheet()->setCellValue('F'.$row, $total_kredit);
            $spreadsheet->getActiveSheet()->setCellValue('G'.$row+1, $last_balance_debet);
            $spreadsheet->getActiveSheet()->setCellValue('H'.$row+1, $last_balance_credit);
            
            ob_clean();
            $filename='Buku Besar '.$accountname.'.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }else{
            echo "Maaf data yang di eksport tidak ada !";
        }
    }
}
