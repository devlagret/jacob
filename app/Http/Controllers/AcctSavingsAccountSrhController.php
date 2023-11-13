<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\AcctSavingsAccountSrhDataTable;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsAccountDetail;

class AcctSavingsAccountSrhController extends Controller
{
    public function index()
    {
        $acctsavingsaccount = session()->get('savingsaccountsrh');
        $sessiondata = session()->get('filter_savingsaccountsrh');
        $acctsavingsaccountdetail = AcctSavingsAccountDetail::select('acct_savings_account.savings_account_no','acct_savings_account_detail.today_transaction_date','acct_mutation.mutation_code','acct_savings_account_detail.mutation_out','acct_savings_account_detail.mutation_in','acct_savings_account_detail.last_balance','core_member.member_name','acct_mutation.mutation_name','acct_savings_account_detail.transaction_code','acct_savings_account_detail.daily_average_balance')
        ->join('acct_savings_account', 'acct_savings_account_detail.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('acct_savings', 'acct_savings_account_detail.savings_id', '=', 'acct_savings.savings_id')
        ->join('acct_mutation', 'acct_savings_account_detail.mutation_id', '=', 'acct_mutation.mutation_id')
        ->join('core_member', 'acct_savings_account_detail.member_id', '=', 'core_member.member_id')
        ->where('acct_savings_account_detail.today_transaction_date', '>=', empty($sessiondata) ? date('Y-m-d') : date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_savings_account_detail.today_transaction_date', '<=', empty($sessiondata) ? date('Y-m-d') : date('Y-m-d', strtotime($sessiondata['end_date'])));
        if (!empty($acctsavingsaccount)) {

            $acctsavingsaccountdetail= $acctsavingsaccountdetail->where('acct_savings_account_detail.savings_account_id', $acctsavingsaccount['savings_account_id']);
        }
        $acctsavingsaccountdetail= $acctsavingsaccountdetail->orderBy('acct_savings_account_detail.savings_account_detail_id', 'ASC')
        ->get();

        return view('content.AcctSavingsAccountSrh.index', compact('sessiondata','acctsavingsaccount','acctsavingsaccountdetail'));
    }

    public function filter(Request $request)
    {
        if($request->start_date){
            $start_date = $request->start_date;
        }else{
            $start_date = date('d-m-Y');
        }
        if($request->end_date){
            $end_date = $request->end_date;
        }else{
            $end_date = date('d-m-Y');
        }

        $sessiondata = array(
            'start_date' => $start_date,
            'end_date'  => $end_date,
        );

        session()->put('filter_savingsaccountsrh', $sessiondata);

        return redirect('savings-account-srh');
    }

    public function resetFilter()
    {
        session()->forget('filter_savingsaccountsrh');
        session()->forget('savingsaccountsrh');

        return redirect('savings-account-srh');
    }

    public function modalSavingsAccount(AcctSavingsAccountSrhDataTable $dataTable)
    {
        return $dataTable->render('content.AcctSavingsAccountSrh.AcctSavingsAccountModal.index');
    }

    public function selectSavingsAccount($savings_account_id)
    {
        $savingsaccount = AcctSavingsAccount::withoutGlobalScopes()
        ->where('acct_savings_account.savings_account_id',$savings_account_id)
        ->join('core_member','acct_savings_account.member_id', '=', 'core_member.member_id')
        ->first();

        $data = array(
            'savings_account_id'            =>  $savings_account_id,
            'savings_account_no'            =>  $savingsaccount['savings_account_no'],
            'member_name'                   =>  $savingsaccount['member_name'],
            'member_address'                =>  $savingsaccount['member_address'],
        );

        session()->put('savingsaccountsrh', $data);

        return redirect('savings-account-srh');
    }
}
