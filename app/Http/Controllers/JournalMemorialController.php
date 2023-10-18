<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoreBranch;
use App\Models\AcctJournalVoucherItem;

class JournalMemorialController extends Controller
{
    public function index()
    {
        $session = session()->get('filter_journalmemorial');
        if (empty($session['start_date'])) {
            $start_date = date('Y-m-d');
        } else {
            $start_date = date('Y-m-d', strtotime($session['start_date']));
        }
        if (empty($session['end_date'])) {
            $end_date = date('Y-m-d');
        } else {
            $end_date = date('Y-m-d', strtotime($session['end_date']));
        }
        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state',0)
        ->get();
        $acctmemorialjournal = AcctJournalVoucherItem::select('acct_journal_voucher_item.journal_voucher_item_id', 'acct_journal_voucher_item.journal_voucher_description', 'acct_journal_voucher_item.journal_voucher_debit_amount', 'acct_journal_voucher_item.journal_voucher_credit_amount', 'acct_journal_voucher_item.account_id', 'acct_account.account_code', 'acct_account.account_name', 'acct_journal_voucher_item.account_id_status', 'acct_journal_voucher.transaction_module_code', 'acct_journal_voucher.journal_voucher_date', 'acct_journal_voucher.journal_voucher_id')
        ->join('acct_journal_voucher','acct_journal_voucher_item.journal_voucher_id','=','acct_journal_voucher.journal_voucher_id')
        ->join('acct_account','acct_journal_voucher_item.account_id','=','acct_account.account_id')
        ->where('acct_journal_voucher.journal_voucher_date','>=', $start_date)
        ->where('acct_journal_voucher.journal_voucher_date','<=', $end_date)
        ->where('acct_journal_voucher.data_state', 0)
        ->where('acct_journal_voucher_item.data_state', 0)
        ->where('acct_journal_voucher.posted', 0)
        ->where('acct_journal_voucher_item.journal_voucher_amount','<>', 0)
        ->where('acct_journal_voucher.transaction_module_id','!=', 10)
        ->orderBy('acct_journal_voucher_item.journal_voucher_item_id', 'ASC');
        if(!empty($session['branch_id'])) {
            $acctmemorialjournal = $acctmemorialjournal->where('acct_journal_voucher.branch_id', $session['branch_id']);
        }
        $acctmemorialjournal = $acctmemorialjournal->get();

        return view('content.JournalMemorial.List.index',compact('corebranch','session','acctmemorialjournal'));
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
            'start_date'    => $start_date,
            'end_date'      => $end_date,
            'branch_id'     => $request->branch_id,
        );

        session()->put('filter_journalmemorial', $sessiondata);

        return redirect('journal-memorial');
    }

    public function resetFilter()
    {
        session()->forget('filter_journalmemorial');

        return redirect('journal-memorial');
    }
}
