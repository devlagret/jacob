<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\SystemEndOfDays;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;

class SystemBranchCloseController extends Controller
{
    public function index()
    {
        $endofdays = SystemEndOfDays::select('*')
        ->orderBy('created_at', 'DESC')
        ->first();

        if($endofdays['end_of_days_status'] == 1){
            $journal = AcctJournalVoucherItem::select('acct_journal_voucher_item.journal_voucher_item_id', 'acct_journal_voucher_item.journal_voucher_description', 'acct_journal_voucher_item.journal_voucher_debit_amount', 'acct_journal_voucher_item.journal_voucher_credit_amount', 'acct_journal_voucher_item.account_id', 'acct_account.account_code', 'acct_account.account_name', 'acct_journal_voucher_item.account_id_status', 'acct_journal_voucher.transaction_module_code', 'acct_journal_voucher.journal_voucher_date', 'acct_journal_voucher.journal_voucher_id')
            ->join('acct_journal_voucher', 'acct_journal_voucher_item.journal_voucher_id', 'acct_journal_voucher.journal_voucher_id')
            ->join('acct_account', 'acct_journal_voucher_item.account_id', 'acct_account.account_id')
            ->where('acct_journal_voucher.journal_voucher_date', date('Y-m-d',strtotime($endofdays['created_at'])))
            ->where('acct_journal_voucher.data_state', 0)
            ->where('acct_journal_voucher_item.journal_voucher_amount', '!=', 0)
            ->orderBy('acct_journal_voucher.created_at', 'DESC')
            ->orderBy('acct_journal_voucher.journal_voucher_date', 'DESC')
            ->get();

        }else{
            $journal = [];
        }

        return view('content.SystemBranchClose.index', compact('endofdays', 'journal'));
    }

    public function process(Request $request)
    {
        $endofdays                      = SystemEndOfDays::findOrFail($request->end_of_days_id);
        $endofdays->end_of_days_status  = 0;
        $endofdays->debit_amount        = $request->debit_amount;
        $endofdays->credit_amount       = $request->credit_amount;
        $endofdays->close_id            = auth()->user()->user_id;
        $endofdays->closed_at           = date('Y-m-d H:i:s');

        if($endofdays->save()){
            $message = array(
                'pesan' => 'Cabang Telah Ditutup, Selamat Istirahat...',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Tutup Cabang gagal',
                'alert' => 'error'
            );
        }

        return redirect('branch-close')->with($message);
    }
}
