<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsCashMutation;
use App\Models\CoreBranch;
use App\Models\CoreCity;
use App\Models\CoreKecamatan;
use App\Models\CoreMember;
use App\Models\AcctMutation;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\DataTables\AcctSavingsCashMutation\AcctSavingsCashMutationDataTable;
use App\DataTables\AcctSavingsCashMutation\AcctSavingsAccountDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Carbon\Carbon;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctSavingsCashMutationController extends Controller
{
    public function index(AcctSavingsCashMutationDataTable $dataTable)
    {
        session()->forget('data_savingscashmutationadd');
        $sessiondata = session()->get('filter_savingscashmutation');

        return $dataTable->render('content.AcctSavingsCashMutation.List.index', compact('sessiondata'));
    }

    public function filter(Request $request)
    {
        if ($request->start_date) {
            $start_date = $request->start_date;
        } else {
            $start_date = date('Y-m-d');
        }

        if ($request->end_date) {
            $end_date = $request->end_date;
        } else {
            $end_date = date('Y-m-d');
        }

        $sessiondata = [
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];

        session()->put('filter_savingscashmutation', $sessiondata);

        return redirect('savings-cash-mutation');
    }

    public function filterReset()
    {
        session()->forget('filter_savingscashmutation');

        return redirect('savings-cash-mutation');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_savingscashmutationadd');
        if (!$sessiondata || $sessiondata == '') {
            $sessiondata['mutation_id'] = null;
            $sessiondata['savings_cash_mutation_amount'] = 0;
            $sessiondata['savings_cash_mutation_amount_adm'] = 0;
            $sessiondata['savings_cash_mutation_last_balance'] = 0;
            $sessiondata['savings_account_blockir_amount'] = 0;
            $sessiondata['savings_account_blockir_type'] = 0;
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_savingscashmutationadd', $sessiondata);
    }

    public function add()
    {
        $config = theme()->getOption('page', 'view');
        $sessiondata = session()->get('data_savingscashmutationadd');
        $membergender = array_filter(Configuration::MemberGender());
        $memberidentity = array_filter(Configuration::MemberIdentity());
        $familyrelationship = array_filter(Configuration::FamilyRelationship());

        $acctmutation = AcctMutation::select('mutation_id', 'mutation_name')
            ->where('mutation_module', 'TAB')
            ->where('data_state', 0)
            ->get();

        $acctsavingsaccount = [];
        if (isset($sessiondata['savings_account_id'])) {
            $acctsavingsaccount = AcctSavingsAccount::withoutGlobalScopes()->with('savingdata','member.city','member.kecamatan')->find($sessiondata['savings_account_id']);
        }
        return view('content.AcctSavingsCashMutation.Add.index', compact('sessiondata', 'membergender', 'memberidentity', 'familyrelationship', 'acctmutation', 'acctsavingsaccount'));
    }

    public function modalAcctSavingsAccount(AcctSavingsAccountDataTable $dataTable)
    {
        return $dataTable->render('content.AcctSavingsCashMutation.Add.AcctSavingsAccountModal.index');
    }

    public function selectAcctSavingsAccount($savings_account_id)
    {
        $sessiondata = session()->get('data_savingscashmutationadd');
        if (!$sessiondata || $sessiondata == '') {
            $sessiondata['mutation_id'] = null;
            $sessiondata['savings_cash_mutation_amount'] = 0;
            $sessiondata['savings_cash_mutation_amount_adm'] = 0;
            $sessiondata['savings_cash_mutation_last_balance'] = 0;
            $sessiondata['savings_account_blockir_amount'] = 0;
            $sessiondata['savings_account_blockir_type'] = 0;
        }
        $sessiondata['savings_account_id'] = $savings_account_id;
        session()->put('data_savingscashmutationadd', $sessiondata);

        return redirect('savings-cash-mutation/add');
    }

    public function processAdd(Request $request)
    {
        $preferencecompany = PreferenceCompany::first();

        // dd($request->all());

        $fields = request()->validate([
            'savings_account_id' => ['required'],
            'mutation_id' => ['required'],
            'savings_cash_mutation_amount' => ['required'],
            'savings_cash_mutation_date' => ['required'],
        ]);

        DB::beginTransaction();

        //---------------------------------------------setoran tunai------------------------------------------------------------//
        if ($fields['mutation_id'] == 1|| $fields['mutation_id'] == 3) {
            try {
                $data = [
                    'savings_account_id' => $fields['savings_account_id'],
                    'mutation_id' => $fields['mutation_id'],
                    'member_id' => $request->member_id,
                    'savings_id' => $request->savings_id,
                    'savings_cash_mutation_date' => date('Y-m-d', strtotime($fields['savings_cash_mutation_date'])),
                    'savings_cash_mutation_opening_balance' => $request->savings_cash_mutation_last_balance,
                    'savings_cash_mutation_amount' => $fields['savings_cash_mutation_amount'],
                    'savings_cash_mutation_amount_adm' => $request->savings_cash_mutation_amount_adm,
                    'savings_cash_mutation_last_balance' => $request->savings_cash_mutation_last_balance,
                    'savings_cash_mutation_remark' => $request->savings_cash_mutation_remark,
                    'branch_id' => auth()->user()->branch_id,
                    'operated_name' => auth()->user()->username,
                    'created_id' => auth()->user()->user_id,
                    'pickup_state'=> 1,
                    'pickup_date'=> Carbon::now(),
                ];
                AcctSavingsCashMutation::create($data);

                $transaction_module_code = 'TTAB';
                $transaction_module_id = PreferenceTransactionModule::select('transaction_module_id')
                    ->where('transaction_module_code', $transaction_module_code)
                    ->first()->transaction_module_id;

                $journal_voucher_period = date('Ym', strtotime($data['savings_cash_mutation_date']));

                $acctsavingscash_last = AcctSavingsCashMutation::select('acct_savings_cash_mutation.savings_cash_mutation_id', 'acct_savings_cash_mutation.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_cash_mutation.member_id', 'core_member.member_name')
                    ->join('acct_savings_account', 'acct_savings_cash_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
                    ->join('core_member', 'acct_savings_cash_mutation.member_id', '=', 'core_member.member_id')
                    ->where('acct_savings_cash_mutation.created_id', $data['created_id'])
                    ->orderBy('acct_savings_cash_mutation.savings_cash_mutation_id', 'DESC')
                    ->first();

                $data_journal = [
                    'branch_id' => auth()->user()->branch_id,
                    'journal_voucher_period' => $journal_voucher_period,
                    'journal_voucher_date' => date('Y-m-d'),
                    'journal_voucher_title' => 'MUTASI TUNAI ' . $acctsavingscash_last['member_name'],
                    'journal_voucher_description' => 'MUTASI TUNAI ' . $acctsavingscash_last['member_name'],
                    'transaction_module_id' => $transaction_module_id,
                    'transaction_module_code' => $transaction_module_code,
                    'transaction_journal_id' => $acctsavingscash_last['savings_cash_mutation_id'],
                    'transaction_journal_no' => $acctsavingscash_last['savings_account_no'],
                    'created_id' => $data['created_id'],
                ];
                AcctJournalVoucher::create($data_journal);

                $journal_voucher_id = AcctJournalVoucher::select('journal_voucher_id')
                    ->where('acct_journal_voucher.created_id', $data['created_id'])
                    ->orderBy('acct_journal_voucher.journal_voucher_id', 'DESC')
                    ->first()->journal_voucher_id;

                if ($data['mutation_id'] == $preferencecompany['cash_deposit_id']) {
                    $account_id_default_status = AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                        ->where('acct_account.data_state', 0)
                        ->first()->account_default_status;

                    $data_debet = [
                        'journal_voucher_id' => $journal_voucher_id,
                        'account_id' => $preferencecompany['account_cash_id'],
                        'journal_voucher_description' => 'SETORAN TUNAI ' . $acctsavingscash_last['member_name'],
                        'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                        'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount'],
                        'account_id_default_status' => $account_id_default_status,
                        'account_id_status' => 0,
                        'created_id' => auth()->user()->user_id,
                    ];
                    AcctJournalVoucherItem::create($data_debet);

                    $account_id = AcctSavings::select('account_id')
                        ->where('savings_id', $data['savings_id'])
                        ->first()->account_id;

                    $account_id_default_status = AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $account_id)
                        ->where('acct_account.data_state', 0)
                        ->first()->account_default_status;

                    $data_credit = [
                        'journal_voucher_id' => $journal_voucher_id,
                        'account_id' => $account_id,
                        'journal_voucher_description' => 'SETORAN TUNAI ' . $acctsavingscash_last['member_name'],
                        'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                        'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount'],
                        'account_id_default_status' => $account_id_default_status,
                        'account_id_status' => 1,
                        'created_id' => auth()->user()->user_id,
                    ];
                    AcctJournalVoucherItem::create($data_credit);

                    if ($data['savings_cash_mutation_amount_adm'] > 0) {
                        $data_debet = [
                            'journal_voucher_id' => $journal_voucher_id,
                            'account_id' => $preferencecompany['account_cash_id'],
                            'journal_voucher_description' => $data_journal['journal_voucher_title'],
                            'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                            'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount_adm'],
                            'account_id_default_status' => $account_id_default_status,
                            'account_id_status' => 0,
                            'created_id' => auth()->user()->user_id,
                        ];
                        AcctJournalVoucherItem::create($data_debet);

                        $account_id_default_status = AcctAccount::select('account_default_status')
                            ->where('acct_account.account_id', $preferencecompany['account_mutation_adm_id'])
                            ->where('acct_account.data_state', 0)
                            ->first()->account_default_status;

                        $data_credit = [
                            'journal_voucher_id' => $journal_voucher_id,
                            'account_id' => $preferencecompany['account_mutation_adm_id'],
                            'journal_voucher_description' => $data_journal['journal_voucher_title'],
                            'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                            'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount_adm'],
                            'account_id_status' => 1,
                            'created_id' => auth()->user()->user_id,
                        ];
                        AcctJournalVoucherItem::create($data_credit);
                    }
                } elseif ($data['mutation_id'] == 2) {
                    $account_id = AcctSavings::select('account_id')
                        ->where('savings_id', $data['savings_id'])
                        ->first()->account_id;

                    $account_id_default_status = AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $account_id)
                        ->where('acct_account.data_state', 0)
                        ->first()->account_default_status;

                    $data_debet = [
                        'journal_voucher_id' => $journal_voucher_id,
                        'account_id' => $account_id,
                        'journal_voucher_description' => 'PENARIKAN TUNAI ' . $acctsavingscash_last['member_name'],
                        'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                        'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount'],
                        'account_id_default_status' => $account_id_default_status,
                        'account_id_status' => 0,
                        'created_id' => auth()->user()->user_id,
                    ];
                    AcctJournalVoucherItem::create($data_debet);

                    $account_id_default_status = AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                        ->where('acct_account.data_state', 0)
                        ->first()->account_default_status;

                    $data_credit = [
                        'journal_voucher_id' => $journal_voucher_id,
                        'account_id' => $preferencecompany['account_cash_id'],
                        'journal_voucher_description' => 'PENARIKAN TUNAI ' . $acctsavingscash_last['member_name'],
                        'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                        'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount'],
                        'account_id_default_status' => $account_id_default_status,
                        'account_id_status' => 1,
                        'created_id' => auth()->user()->user_id,
                    ];
                    AcctJournalVoucherItem::create($data_credit);

                    if ($data['savings_cash_mutation_amount_adm'] > 0) {
                        $data_debet = [
                            'journal_voucher_id' => $journal_voucher_id,
                            'account_id' => $preferencecompany['account_cash_id'],
                            'journal_voucher_description' => $data_journal['journal_voucher_title'],
                            'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                            'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount_adm'],
                            'account_id_default_status' => $account_id_default_status,
                            'account_id_status' => 0,
                            'created_id' => auth()->user()->user_id,
                        ];
                        AcctJournalVoucherItem::create($data_debet);

                        $account_id_default_status = AcctAccount::select('account_default_status')
                            ->where('acct_account.account_id', $preferencecompany['account_mutation_adm_id'])
                            ->where('acct_account.data_state', 0)
                            ->first()->account_default_status;

                        $data_credit = [
                            'journal_voucher_id' => $journal_voucher_id,
                            'account_id' => $preferencecompany['account_mutation_adm_id'],
                            'journal_voucher_description' => $data_journal['journal_voucher_title'],
                            'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                            'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount_adm'],
                            'account_id_status' => 1,
                            'created_id' => auth()->user()->user_id,
                        ];
                        AcctJournalVoucherItem::create($data_credit);
                    }
                } elseif ($data['mutation_id'] == 3) {
                    $account_id_default_status = AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                        ->where('acct_account.data_state', 0)
                        ->first()->account_default_status;

                    $data_debet = [
                        'journal_voucher_id' => $journal_voucher_id,
                        'account_id' => $preferencecompany['account_cash_id'],
                        'journal_voucher_description' => 'KOREKSI KREDIT ' . $acctsavingscash_last['member_name'],
                        'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                        'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount'],
                        'account_id_default_status' => $account_id_default_status,
                        'account_id_status' => 0,
                        'created_id' => auth()->user()->user_id,
                    ];
                    AcctJournalVoucherItem::create($data_debet);

                    $account_id = AcctSavings::select('account_id')
                        ->where('savings_id', $data['savings_id'])
                        ->first()->account_id;

                    $account_id_default_status = AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $account_id)
                        ->where('acct_account.data_state', 0)
                        ->first()->account_default_status;

                    $data_credit = [
                        'journal_voucher_id' => $journal_voucher_id,
                        'account_id' => $account_id,
                        'journal_voucher_description' => 'KOREKSI KREDIT ' . $acctsavingscash_last['member_name'],
                        'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                        'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount'],
                        'account_id_default_status' => $account_id_default_status,
                        'account_id_status' => 1,
                        'created_id' => auth()->user()->user_id,
                    ];
                    AcctJournalVoucherItem::create($data_credit);

                    if ($data['savings_cash_mutation_amount_adm'] > 0) {
                        $account_id_default_status = AcctAccount::select('account_default_status')
                            ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                            ->where('acct_account.data_state', 0)
                            ->first()->account_default_status;

                        $data_debet = [
                            'journal_voucher_id' => $journal_voucher_id,
                            'account_id' => $preferencecompany['account_cash_id'],
                            'journal_voucher_description' => $data_journal['journal_voucher_title'],
                            'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                            'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount_adm'],
                            'account_id_default_status' => $account_id_default_status,
                            'account_id_status' => 0,
                            'created_id' => auth()->user()->user_id,
                        ];
                        AcctJournalVoucherItem::create($data_debet);

                        $account_id_default_status = AcctAccount::select('account_default_status')
                            ->where('acct_account.account_id', $preferencecompany['account_mutation_adm_id'])
                            ->where('acct_account.data_state', 0)
                            ->first()->account_default_status;

                        $data_credit = [
                            'journal_voucher_id' => $journal_voucher_id,
                            'account_id' => $preferencecompany['account_mutation_adm_id'],
                            'journal_voucher_description' => $data_journal['journal_voucher_title'],
                            'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                            'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount_adm'],
                            'account_id_status' => 1,
                            'created_id' => auth()->user()->user_id,
                        ];
                        AcctJournalVoucherItem::create($data_credit);
                    }
                } elseif ($data['mutation_id'] == 4) {
                    $account_id = AcctSavings::select('account_id')
                        ->where('savings_id', $data['savings_id'])
                        ->first()->account_id;

                    $account_id_default_status = AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $account_id)
                        ->where('acct_account.data_state', 0)
                        ->first()->account_default_status;

                    $data_debet = [
                        'journal_voucher_id' => $journal_voucher_id,
                        'account_id' => $account_id,
                        'journal_voucher_description' => 'KOREKSI DEBET ' . $acctsavingscash_last['member_name'],
                        'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                        'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount'],
                        'account_id_default_status' => $account_id_default_status,
                        'account_id_status' => 0,
                        'created_id' => auth()->user()->user_id,
                    ];
                    AcctJournalVoucherItem::create($data_debet);

                    $account_id_default_status = AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                        ->where('acct_account.data_state', 0)
                        ->first()->account_default_status;

                    $data_credit = [
                        'journal_voucher_id' => $journal_voucher_id,
                        'account_id' => $preferencecompany['account_cash_id'],
                        'journal_voucher_description' => 'KOREKSI DEBET ' . $acctsavingscash_last['member_name'],
                        'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                        'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount'],
                        'account_id_default_status' => $account_id_default_status,
                        'account_id_status' => 1,
                        'created_id' => auth()->user()->user_id,
                    ];
                    AcctJournalVoucherItem::create($data_credit);

                    if ($data['savings_cash_mutation_amount_adm'] > 0) {
                        $account_id_default_status = AcctAccount::select('account_default_status')
                            ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                            ->where('acct_account.data_state', 0)
                            ->first()->account_default_status;

                        $data_debet = [
                            'journal_voucher_id' => $journal_voucher_id,
                            'account_id' => $preferencecompany['account_cash_id'],
                            'journal_voucher_description' => $data_journal['journal_voucher_title'],
                            'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                            'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount_adm'],
                            'account_id_default_status' => $account_id_default_status,
                            'account_id_status' => 0,
                            'created_id' => auth()->user()->user_id,
                        ];
                        AcctJournalVoucherItem::create($data_debet);

                        $account_id_default_status = AcctAccount::select('account_default_status')
                            ->where('acct_account.account_id', $preferencecompany['account_mutation_adm_id'])
                            ->where('acct_account.data_state', 0)
                            ->first()->account_default_status;

                        $data_credit = [
                            'journal_voucher_id' => $journal_voucher_id,
                            'account_id' => $preferencecompany['account_mutation_adm_id'],
                            'journal_voucher_description' => $data_journal['journal_voucher_title'],
                            'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                            'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount_adm'],
                            'account_id_status' => 1,
                            'created_id' => auth()->user()->user_id,
                        ];
                        AcctJournalVoucherItem::create($data_credit);
                    }
                } else {
                    $closed_savings_account = AcctSavingsAccount::withoutGlobalScopes()->findOrFail($data['savings_account_id']);
                    $closed_savings_account->savings_account_status = 1;
                    $closed_savings_account->save();

                    $account_id = AcctSavings::select('account_id')
                        ->where('savings_id', $data['savings_id'])
                        ->first()->account_id;

                    $account_id_default_status = AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $account_id)
                        ->where('acct_account.data_state', 0)
                        ->first()->account_default_status;

                    $data_debet = [
                        'journal_voucher_id' => $journal_voucher_id,
                        'account_id' => $account_id,
                        'journal_voucher_description' => 'TUTUP REKENING ' . $acctsavingscash_last['member_name'],
                        'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                        'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount'],
                        'account_id_default_status' => $account_id_default_status,
                        'account_id_status' => 0,
                        'created_id' => auth()->user()->user_id,
                    ];
                    AcctJournalVoucherItem::create($data_debet);

                    $account_id_default_status = AcctAccount::select('account_default_status')
                        ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                        ->where('acct_account.data_state', 0)
                        ->first()->account_default_status;

                    $data_credit = [
                        'journal_voucher_id' => $journal_voucher_id,
                        'account_id' => $preferencecompany['account_cash_id'],
                        'journal_voucher_description' => 'TUTUP REKENING ' . $acctsavingscash_last['member_name'],
                        'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                        'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount'],
                        'account_id_default_status' => $account_id_default_status,
                        'account_id_status' => 1,
                        'created_id' => auth()->user()->user_id,
                    ];
                    AcctJournalVoucherItem::create($data_credit);

                    if ($data['savings_cash_mutation_amount_adm'] > 0) {
                        $data_debet = [
                            'journal_voucher_id' => $journal_voucher_id,
                            'account_id' => $preferencecompany['account_cash_id'],
                            'journal_voucher_description' => $data_journal['journal_voucher_title'],
                            'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                            'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount_adm'],
                            'account_id_default_status' => $account_id_default_status,
                            'account_id_status' => 0,
                            'created_id' => auth()->user()->user_id,
                        ];
                        AcctJournalVoucherItem::create($data_debet);

                        $account_id_default_status = AcctAccount::select('account_default_status')
                            ->where('acct_account.account_id', $preferencecompany['account_mutation_adm_id'])
                            ->where('acct_account.data_state', 0)
                            ->first()->account_default_status;

                        $data_credit = [
                            'journal_voucher_id' => $journal_voucher_id,
                            'account_id' => $preferencecompany['account_mutation_adm_id'],
                            'journal_voucher_description' => $data_journal['journal_voucher_title'],
                            'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                            'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount_adm'],
                            'account_id_status' => 1,
                            'created_id' => auth()->user()->user_id,
                        ];
                        AcctJournalVoucherItem::create($data_credit);
                    }
                }

                DB::commit();
                $message = [
                    'pesan' => 'Tabungan berhasil ditambah',
                    'alert' => 'success',
                ];
            } catch (\Exception $e) {
                DB::rollback();
                $message = [
                    'pesan' => 'Tabungan gagal ditambah',
                    'alert' => 'error',
                ];
            }
            //-------------------------penarikan tunai,koreksi kredit, koreksi debet, tutup rekening-----------------------------------//
        } elseif ($fields['mutation_id'] == 2  || $fields['mutation_id'] == 4 || $fields['mutation_id'] == 5) {
            //saldo di blokir
            if ($fields['savings_cash_mutation_amount'] > $request->savings_account_range_amount) {
                $message = [
                    'pesan' => 'Saldo Tidak Mecukupi.',
                    'alert' => 'error',
                ];
                return redirect('savings-cash-mutation/add')->with($message);
            } else {
                if ($fields['savings_cash_mutation_amount'] > $request->savings_account_last_balance) {
                    $message = [
                        'pesan' => 'Saldo Tidak Mecukupi.',
                        'alert' => 'error',
                    ];
                    return redirect('savings-cash-mutation/add')->with($message);
                } else {
                    try {
                        $data = [
                            'savings_account_id' => $fields['savings_account_id'],
                            'mutation_id' => $fields['mutation_id'],
                            'member_id' => $request->member_id,
                            'savings_id' => $request->savings_id,
                            'savings_cash_mutation_date' => date('Y-m-d', strtotime($fields['savings_cash_mutation_date'])),
                            'savings_cash_mutation_opening_balance' => $request->savings_cash_mutation_last_balance,
                            'savings_cash_mutation_amount' => $fields['savings_cash_mutation_amount'],
                            'savings_cash_mutation_amount_adm' => $request->savings_cash_mutation_amount_adm,
                            'savings_cash_mutation_last_balance' => $request->savings_cash_mutation_last_balance,
                            'savings_cash_mutation_remark' => $request->savings_cash_mutation_remark,
                            'branch_id' => auth()->user()->branch_id,
                            'operated_name' => auth()->user()->username,
                            'created_id' => auth()->user()->user_id,
                            'pickup_state'=> 1,
                            'pickup_date'=> Carbon::now(),
                        ];
                        AcctSavingsCashMutation::create($data);

                        $transaction_module_code = 'TTAB';
                        $transaction_module_id = PreferenceTransactionModule::select('transaction_module_id')
                            ->where('transaction_module_code', $transaction_module_code)
                            ->first()->transaction_module_id;

                        $journal_voucher_period = date('Ym', strtotime($data['savings_cash_mutation_date']));

                        $acctsavingscash_last = AcctSavingsCashMutation::select('acct_savings_cash_mutation.savings_cash_mutation_id', 'acct_savings_cash_mutation.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_cash_mutation.member_id', 'core_member.member_name')
                            ->join('acct_savings_account', 'acct_savings_cash_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
                            ->join('core_member', 'acct_savings_cash_mutation.member_id', '=', 'core_member.member_id')
                            ->where('acct_savings_cash_mutation.created_id', $data['created_id'])
                            ->orderBy('acct_savings_cash_mutation.savings_cash_mutation_id', 'DESC')
                            ->first();

                        $data_journal = [
                            'branch_id' => auth()->user()->branch_id,
                            'journal_voucher_period' => $journal_voucher_period,
                            'journal_voucher_date' => date('Y-m-d'),
                            'journal_voucher_title' => 'MUTASI TUNAI ' . $acctsavingscash_last['member_name'],
                            'journal_voucher_description' => 'MUTASI TUNAI ' . $acctsavingscash_last['member_name'],
                            'transaction_module_id' => $transaction_module_id,
                            'transaction_module_code' => $transaction_module_code,
                            'transaction_journal_id' => $acctsavingscash_last['savings_cash_mutation_id'],
                            'transaction_journal_no' => $acctsavingscash_last['savings_account_no'],
                            'created_id' => $data['created_id'],
                        ];
                        AcctJournalVoucher::create($data_journal);

                        $journal_voucher_id = AcctJournalVoucher::select('journal_voucher_id')
                            ->where('acct_journal_voucher.created_id', $data['created_id'])
                            ->orderBy('acct_journal_voucher.journal_voucher_id', 'DESC')
                            ->first()->journal_voucher_id;

                        if ($data['mutation_id'] == $preferencecompany['cash_deposit_id']) {
                            $account_id_default_status = AcctAccount::select('account_default_status')
                                ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                                ->where('acct_account.data_state', 0)
                                ->first()->account_default_status;

                            $data_debet = [
                                'journal_voucher_id' => $journal_voucher_id,
                                'account_id' => $preferencecompany['account_cash_id'],
                                'journal_voucher_description' => 'SETORAN TUNAI ' . $acctsavingscash_last['member_name'],
                                'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                                'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount'],
                                'account_id_default_status' => $account_id_default_status,
                                'account_id_status' => 0,
                                'created_id' => auth()->user()->user_id,
                            ];
                            AcctJournalVoucherItem::create($data_debet);

                            $account_id = AcctSavings::select('account_id')
                                ->where('savings_id', $data['savings_id'])
                                ->first()->account_id;

                            $account_id_default_status = AcctAccount::select('account_default_status')
                                ->where('acct_account.account_id', $account_id)
                                ->where('acct_account.data_state', 0)
                                ->first()->account_default_status;

                            $data_credit = [
                                'journal_voucher_id' => $journal_voucher_id,
                                'account_id' => $account_id,
                                'journal_voucher_description' => 'SETORAN TUNAI ' . $acctsavingscash_last['member_name'],
                                'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                                'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount'],
                                'account_id_default_status' => $account_id_default_status,
                                'account_id_status' => 1,
                                'created_id' => auth()->user()->user_id,
                            ];
                            AcctJournalVoucherItem::create($data_credit);

                            if ($data['savings_cash_mutation_amount_adm'] > 0) {
                                $data_debet = [
                                    'journal_voucher_id' => $journal_voucher_id,
                                    'account_id' => $preferencecompany['account_cash_id'],
                                    'journal_voucher_description' => $data_journal['journal_voucher_title'],
                                    'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'account_id_default_status' => $account_id_default_status,
                                    'account_id_status' => 0,
                                    'created_id' => auth()->user()->user_id,
                                ];
                                AcctJournalVoucherItem::create($data_debet);

                                $account_id_default_status = AcctAccount::select('account_default_status')
                                    ->where('acct_account.account_id', $preferencecompany['account_mutation_adm_id'])
                                    ->where('acct_account.data_state', 0)
                                    ->first()->account_default_status;

                                $data_credit = [
                                    'journal_voucher_id' => $journal_voucher_id,
                                    'account_id' => $preferencecompany['account_mutation_adm_id'],
                                    'journal_voucher_description' => $data_journal['journal_voucher_title'],
                                    'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'account_id_status' => 1,
                                    'created_id' => auth()->user()->user_id,
                                ];
                                AcctJournalVoucherItem::create($data_credit);
                            }
                        } elseif ($data['mutation_id'] == 2) {
                            $account_id = AcctSavings::select('account_id')
                                ->where('savings_id', $data['savings_id'])
                                ->first()->account_id;

                            $account_id_default_status = AcctAccount::select('account_default_status')
                                ->where('acct_account.account_id', $account_id)
                                ->where('acct_account.data_state', 0)
                                ->first()->account_default_status;

                            $data_debet = [
                                'journal_voucher_id' => $journal_voucher_id,
                                'account_id' => $account_id,
                                'journal_voucher_description' => 'PENARIKAN TUNAI ' . $acctsavingscash_last['member_name'],
                                'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                                'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount'],
                                'account_id_default_status' => $account_id_default_status,
                                'account_id_status' => 0,
                                'created_id' => auth()->user()->user_id,
                            ];
                            AcctJournalVoucherItem::create($data_debet);

                            $account_id_default_status = AcctAccount::select('account_default_status')
                                ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                                ->where('acct_account.data_state', 0)
                                ->first()->account_default_status;

                            $data_credit = [
                                'journal_voucher_id' => $journal_voucher_id,
                                'account_id' => $preferencecompany['account_cash_id'],
                                'journal_voucher_description' => 'PENARIKAN TUNAI ' . $acctsavingscash_last['member_name'],
                                'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                                'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount'],
                                'account_id_default_status' => $account_id_default_status,
                                'account_id_status' => 1,
                                'created_id' => auth()->user()->user_id,
                            ];
                            AcctJournalVoucherItem::create($data_credit);

                            if ($data['savings_cash_mutation_amount_adm'] > 0) {
                                $data_debet = [
                                    'journal_voucher_id' => $journal_voucher_id,
                                    'account_id' => $preferencecompany['account_cash_id'],
                                    'journal_voucher_description' => $data_journal['journal_voucher_title'],
                                    'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'account_id_default_status' => $account_id_default_status,
                                    'account_id_status' => 0,
                                    'created_id' => auth()->user()->user_id,
                                ];
                                AcctJournalVoucherItem::create($data_debet);

                                $account_id_default_status = AcctAccount::select('account_default_status')
                                    ->where('acct_account.account_id', $preferencecompany['account_mutation_adm_id'])
                                    ->where('acct_account.data_state', 0)
                                    ->first()->account_default_status;

                                $data_credit = [
                                    'journal_voucher_id' => $journal_voucher_id,
                                    'account_id' => $preferencecompany['account_mutation_adm_id'],
                                    'journal_voucher_description' => $data_journal['journal_voucher_title'],
                                    'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'account_id_status' => 1,
                                    'created_id' => auth()->user()->user_id,
                                ];
                                AcctJournalVoucherItem::create($data_credit);
                            }
                        } elseif ($data['mutation_id'] == 3) {
                            $account_id_default_status = AcctAccount::select('account_default_status')
                                ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                                ->where('acct_account.data_state', 0)
                                ->first()->account_default_status;

                            $data_debet = [
                                'journal_voucher_id' => $journal_voucher_id,
                                'account_id' => $preferencecompany['account_cash_id'],
                                'journal_voucher_description' => 'KOREKSI KREDIT ' . $acctsavingscash_last['member_name'],
                                'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                                'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount'],
                                'account_id_default_status' => $account_id_default_status,
                                'account_id_status' => 0,
                                'created_id' => auth()->user()->user_id,
                            ];
                            AcctJournalVoucherItem::create($data_debet);

                            $account_id = AcctSavings::select('account_id')
                                ->where('savings_id', $data['savings_id'])
                                ->first()->account_id;

                            $account_id_default_status = AcctAccount::select('account_default_status')
                                ->where('acct_account.account_id', $account_id)
                                ->where('acct_account.data_state', 0)
                                ->first()->account_default_status;

                            $data_credit = [
                                'journal_voucher_id' => $journal_voucher_id,
                                'account_id' => $account_id,
                                'journal_voucher_description' => 'KOREKSI KREDIT ' . $acctsavingscash_last['member_name'],
                                'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                                'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount'],
                                'account_id_default_status' => $account_id_default_status,
                                'account_id_status' => 1,
                                'created_id' => auth()->user()->user_id,
                            ];
                            AcctJournalVoucherItem::create($data_credit);

                            if ($data['savings_cash_mutation_amount_adm'] > 0) {
                                $account_id_default_status = AcctAccount::select('account_default_status')
                                    ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                                    ->where('acct_account.data_state', 0)
                                    ->first()->account_default_status;

                                $data_debet = [
                                    'journal_voucher_id' => $journal_voucher_id,
                                    'account_id' => $preferencecompany['account_cash_id'],
                                    'journal_voucher_description' => $data_journal['journal_voucher_title'],
                                    'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'account_id_default_status' => $account_id_default_status,
                                    'account_id_status' => 0,
                                    'created_id' => auth()->user()->user_id,
                                ];
                                AcctJournalVoucherItem::create($data_debet);

                                $account_id_default_status = AcctAccount::select('account_default_status')
                                    ->where('acct_account.account_id', $preferencecompany['account_mutation_adm_id'])
                                    ->where('acct_account.data_state', 0)
                                    ->first()->account_default_status;

                                $data_credit = [
                                    'journal_voucher_id' => $journal_voucher_id,
                                    'account_id' => $preferencecompany['account_mutation_adm_id'],
                                    'journal_voucher_description' => $data_journal['journal_voucher_title'],
                                    'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'account_id_status' => 1,
                                    'created_id' => auth()->user()->user_id,
                                ];
                                AcctJournalVoucherItem::create($data_credit);
                            }
                        } elseif ($data['mutation_id'] == 4) {
                            $account_id = AcctSavings::select('account_id')
                                ->where('savings_id', $data['savings_id'])
                                ->first()->account_id;

                            $account_id_default_status = AcctAccount::select('account_default_status')
                                ->where('acct_account.account_id', $account_id)
                                ->where('acct_account.data_state', 0)
                                ->first()->account_default_status;

                            $data_debet = [
                                'journal_voucher_id' => $journal_voucher_id,
                                'account_id' => $account_id,
                                'journal_voucher_description' => 'KOREKSI DEBET ' . $acctsavingscash_last['member_name'],
                                'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                                'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount'],
                                'account_id_default_status' => $account_id_default_status,
                                'account_id_status' => 0,
                                'created_id' => auth()->user()->user_id,
                            ];
                            AcctJournalVoucherItem::create($data_debet);

                            $account_id_default_status = AcctAccount::select('account_default_status')
                                ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                                ->where('acct_account.data_state', 0)
                                ->first()->account_default_status;

                            $data_credit = [
                                'journal_voucher_id' => $journal_voucher_id,
                                'account_id' => $preferencecompany['account_cash_id'],
                                'journal_voucher_description' => 'KOREKSI DEBET ' . $acctsavingscash_last['member_name'],
                                'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                                'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount'],
                                'account_id_default_status' => $account_id_default_status,
                                'account_id_status' => 1,
                                'created_id' => auth()->user()->user_id,
                            ];
                            AcctJournalVoucherItem::create($data_credit);

                            if ($data['savings_cash_mutation_amount_adm'] > 0) {
                                $account_id_default_status = AcctAccount::select('account_default_status')
                                    ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                                    ->where('acct_account.data_state', 0)
                                    ->first()->account_default_status;

                                $data_debet = [
                                    'journal_voucher_id' => $journal_voucher_id,
                                    'account_id' => $preferencecompany['account_cash_id'],
                                    'journal_voucher_description' => $data_journal['journal_voucher_title'],
                                    'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'account_id_default_status' => $account_id_default_status,
                                    'account_id_status' => 0,
                                    'created_id' => auth()->user()->user_id,
                                ];
                                AcctJournalVoucherItem::create($data_debet);

                                $account_id_default_status = AcctAccount::select('account_default_status')
                                    ->where('acct_account.account_id', $preferencecompany['account_mutation_adm_id'])
                                    ->where('acct_account.data_state', 0)
                                    ->first()->account_default_status;

                                $data_credit = [
                                    'journal_voucher_id' => $journal_voucher_id,
                                    'account_id' => $preferencecompany['account_mutation_adm_id'],
                                    'journal_voucher_description' => $data_journal['journal_voucher_title'],
                                    'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'account_id_status' => 1,
                                    'created_id' => auth()->user()->user_id,
                                ];
                                AcctJournalVoucherItem::create($data_credit);
                            }
                        } else {
                            $closed_savings_account = AcctSavingsAccount::withoutGlobalScopes()->findOrFail($data['savings_account_id']);
                            $closed_savings_account->savings_account_status = 1;
                            $closed_savings_account->save();

                            $account_id = AcctSavings::select('account_id')
                                ->where('savings_id', $data['savings_id'])
                                ->first()->account_id;

                            $account_id_default_status = AcctAccount::select('account_default_status')
                                ->where('acct_account.account_id', $account_id)
                                ->where('acct_account.data_state', 0)
                                ->first()->account_default_status;

                            $data_debet = [
                                'journal_voucher_id' => $journal_voucher_id,
                                'account_id' => $account_id,
                                'journal_voucher_description' => 'TUTUP REKENING ' . $acctsavingscash_last['member_name'],
                                'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                                'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount'],
                                'account_id_default_status' => $account_id_default_status,
                                'account_id_status' => 0,
                                'created_id' => auth()->user()->user_id,
                            ];
                            AcctJournalVoucherItem::create($data_debet);

                            $account_id_default_status = AcctAccount::select('account_default_status')
                                ->where('acct_account.account_id', $preferencecompany['account_cash_id'])
                                ->where('acct_account.data_state', 0)
                                ->first()->account_default_status;

                            $data_credit = [
                                'journal_voucher_id' => $journal_voucher_id,
                                'account_id' => $preferencecompany['account_cash_id'],
                                'journal_voucher_description' => 'TUTUP REKENING ' . $acctsavingscash_last['member_name'],
                                'journal_voucher_amount' => $data['savings_cash_mutation_amount'],
                                'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount'],
                                'account_id_default_status' => $account_id_default_status,
                                'account_id_status' => 1,
                                'created_id' => auth()->user()->user_id,
                            ];
                            AcctJournalVoucherItem::create($data_credit);

                            if ($data['savings_cash_mutation_amount_adm'] > 0) {
                                $data_debet = [
                                    'journal_voucher_id' => $journal_voucher_id,
                                    'account_id' => $preferencecompany['account_cash_id'],
                                    'journal_voucher_description' => $data_journal['journal_voucher_title'],
                                    'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'journal_voucher_debit_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'account_id_default_status' => $account_id_default_status,
                                    'account_id_status' => 0,
                                    'created_id' => auth()->user()->user_id,
                                ];
                                AcctJournalVoucherItem::create($data_debet);

                                $account_id_default_status = AcctAccount::select('account_default_status')
                                    ->where('acct_account.account_id', $preferencecompany['account_mutation_adm_id'])
                                    ->where('acct_account.data_state', 0)
                                    ->first()->account_default_status;

                                $data_credit = [
                                    'journal_voucher_id' => $journal_voucher_id,
                                    'account_id' => $preferencecompany['account_mutation_adm_id'],
                                    'journal_voucher_description' => $data_journal['journal_voucher_title'],
                                    'journal_voucher_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'journal_voucher_credit_amount' => $data['savings_cash_mutation_amount_adm'],
                                    'account_id_status' => 1,
                                    'created_id' => auth()->user()->user_id,
                                ];
                                AcctJournalVoucherItem::create($data_credit);
                            }
                        }

                        DB::commit();
                        $message = [
                            'pesan' => 'Tabungan berhasil ditambah',
                            'alert' => 'success',
                        ];
                    } catch (\Exception $e) {
                        DB::rollback();
                        $message = [
                            'pesan' => 'Tabungan gagal ditambah',
                            'alert' => 'error',
                        ];
                    }
                }
            }
        }

        return redirect('savings-cash-mutation')->with($message);
    }

    public function printNote($savings_cash_mutation_id)
    {
        $preferencecompany = PreferenceCompany::first();
        $path = public_path('storage/' . $preferencecompany['logo_koperasi']);

        $acctsavingscashmutation = AcctSavingsCashMutation::select(
            'acct_savings_cash_mutation.savings_cash_mutation_id',
            'acct_savings_cash_mutation.savings_account_id',
            'acct_savings_account.savings_account_no',
            'acct_savings_cash_mutation.savings_id',
            'acct_savings.savings_name',
            'acct_savings_cash_mutation.mutation_id',
            'acct_mutation.mutation_name',
            'acct_savings_cash_mutation.member_id',
            'core_member.member_name',
            'core_member.member_address',
            'core_member.city_id',
            'core_member.kecamatan_id',
            'acct_savings_cash_mutation.branch_id',
            'core_branch.branch_city',
            'core_member.identity_id',
            'core_member.member_identity_no',
            'acct_savings_cash_mutation.savings_cash_mutation_date',
            'acct_savings_cash_mutation.savings_cash_mutation_amount',
            'acct_savings_cash_mutation.savings_cash_mutation_amount_adm',
            'acct_savings_cash_mutation.savings_cash_mutation_opening_balance',
            'acct_savings_cash_mutation.savings_cash_mutation_last_balance',
            'acct_savings_cash_mutation.voided_remark',
            'acct_savings_cash_mutation.validation',
            'acct_savings_cash_mutation.validation_at',
            'acct_savings_cash_mutation.validation_id',
        )
            ->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id', '=', 'acct_mutation.mutation_id')
            ->join('acct_savings_account', 'acct_savings_cash_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
            ->join('core_member', 'acct_savings_cash_mutation.member_id', '=', 'core_member.member_id')
            ->join('acct_savings', 'acct_savings_cash_mutation.savings_id', '=', 'acct_savings.savings_id')
            ->join('core_branch', 'acct_savings_cash_mutation.branch_id', '=', 'core_branch.branch_id')
            ->where('acct_savings_cash_mutation.savings_cash_mutation_id', $savings_cash_mutation_id)
            ->where('acct_savings_cash_mutation.data_state', 0)
            ->first();

        $branch_city = CoreBranch::select('branch_city')
            ->where('branch_id', auth()->user()->branch_id)
            ->first()->branch_city;

        if ($acctsavingscashmutation['mutation_id'] == $preferencecompany['cash_deposit_id']) {
            $keterangan = 'SETORAN TUNAI';
            $keterangan2 = 'Telah diterima dari';
            $paraf = 'Penyetor';
        } elseif ($acctsavingscashmutation['mutation_id'] == $preferencecompany['cash_withdrawal_id']) {
            $keterangan = 'PENARIKAN TUNAI';
            $keterangan2 = 'Telah dibayarkan kepada';
            $paraf = 'Penerima';
        } elseif ($acctsavingscashmutation['mutation_id'] == 3) {
            $keterangan = 'KOREKSI KREDIT';
            $keterangan2 = 'Telah diterima dari';
            $paraf = 'Penyetor';
        } elseif ($acctsavingscashmutation['mutation_id'] == 4) {
            $keterangan = 'KOREKSI DEBET';
            $keterangan2 = 'Telah dibayarkan kepada';
            $paraf = 'Penerima';
        }

        $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 6, 6, 6);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once dirname(__FILE__) . '/lang/eng.php';
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage('P');

        $pdf::SetFont('helvetica', '', 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once dirname(__FILE__) . '/lang/eng.php';
            $pdf::setLanguageArray($l);
        }

        $export =
            "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"25%\"></td>
                <td width=\"75%\"><div style=\"text-align: left; font-size:14px\">BUKTI " .
            $keterangan .
            "</div></td>
            </tr>
            <tr>
                <td width=\"25%\"></td>
                <td width=\"75%\"><div style=\"text-align: left; font-size:14px\">Jam : " .
            date('H:i:s') .
            "</div></td>
            </tr>
        </table>
        <br>
        <br>
        <br>
        " .
            $keterangan2 .
            " :
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: " .
            $acctsavingscashmutation['member_name'] .
            "</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">No. Rekening</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: " .
            $acctsavingscashmutation['savings_account_no'] .
            "</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: " .
            $acctsavingscashmutation['member_address'] .
            "</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: " .
            Configuration::numtotxt($acctsavingscashmutation['savings_cash_mutation_amount']) .
            "</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: " .
            $keterangan .
            "</div></td>
            </tr>
                <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;" .
            number_format($acctsavingscashmutation['savings_cash_mutation_amount'], 2) .
            "</div></td>
            </tr>
                <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Biaya Administrasi</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;" .
            number_format($acctsavingscashmutation['savings_cash_mutation_amount_adm'], 2) .
            "</div></td>
            </tr>
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">" .
            $branch_city .
            ', ' .
            date('d-m-Y') .
            "</div></td>
            </tr>
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\">" .
            $paraf .
            "</div></td>
                <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Kwitansi Mutasi Tunai Tabungan.pdf';
        $pdf::Output($filename, 'I');
    }

    public function validation($savings_cash_mutation_id)
    {
        $savingsaccount = AcctSavingsCashMutation::findOrFail($savings_cash_mutation_id);
        $savingsaccount->validation = 1;
        $savingsaccount->validation_id = auth()->user()->user_id;
        $savingsaccount->validation_at = date('Y-m-d');
        if ($savingsaccount->save()) {
            $message = [
                'pesan' => 'Mutasi tunai berhasil divalidasi',
                'alert' => 'success',
            ];

            return redirect('savings-cash-mutation/print-validation/' . $savings_cash_mutation_id);
        } else {
            $message = [
                'pesan' => 'Mutasi tunai gagal divalidasi',
                'alert' => 'error',
            ];
            return redirect('savings-cash-mutation')->with($message);
        }
    }

    public function printValidation($savings_cash_mutation_id)
    {
        $preferencecompany = PreferenceCompany::select('logo_koperasi', 'company_name')->first();
        $path = public_path('storage/' . $preferencecompany['logo_koperasi']);

        $acctsavingscashmutation = AcctSavingsCashMutation::select(
            'acct_savings_cash_mutation.savings_cash_mutation_id',
            'acct_savings_cash_mutation.savings_account_id',
            'acct_savings_account.savings_account_no',
            'acct_savings_cash_mutation.savings_id',
            'acct_savings.savings_name',
            'acct_savings_cash_mutation.mutation_id',
            'acct_mutation.mutation_name',
            'acct_savings_cash_mutation.member_id',
            'core_member.member_name',
            'core_member.member_address',
            'core_member.city_id',
            'core_member.kecamatan_id',
            'acct_savings_cash_mutation.branch_id',
            'core_branch.branch_city',
            'core_member.identity_id',
            'core_member.member_identity_no',
            'acct_savings_cash_mutation.savings_cash_mutation_date',
            'acct_savings_cash_mutation.savings_cash_mutation_amount',
            'acct_savings_cash_mutation.savings_cash_mutation_amount_adm',
            'acct_savings_cash_mutation.savings_cash_mutation_opening_balance',
            'acct_savings_cash_mutation.savings_cash_mutation_last_balance',
            'acct_savings_cash_mutation.voided_remark',
            'acct_savings_cash_mutation.validation',
            'acct_savings_cash_mutation.validation_at',
            'acct_savings_cash_mutation.validation_id',
        )
            ->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id', '=', 'acct_mutation.mutation_id')
            ->join('acct_savings_account', 'acct_savings_cash_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
            ->join('core_member', 'acct_savings_cash_mutation.member_id', '=', 'core_member.member_id')
            ->join('acct_savings', 'acct_savings_cash_mutation.savings_id', '=', 'acct_savings.savings_id')
            ->join('core_branch', 'acct_savings_cash_mutation.branch_id', '=', 'core_branch.branch_id')
            ->where('acct_savings_cash_mutation.savings_cash_mutation_id', $savings_cash_mutation_id)
            ->where('acct_savings_cash_mutation.data_state', 0)
            ->first();

        $branch_city = CoreBranch::select('branch_city')
            ->where('branch_id', auth()->user()->branch_id)
            ->first()->branch_city;

        $validation_name = User::select('username')
            ->where('user_id', $acctsavingscashmutation['validation_id'])
            ->first()->username;

        $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 6, 6, 6);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once dirname(__FILE__) . '/lang/eng.php';
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage('P');

        $pdf::SetFont('helvetica', '', 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once dirname(__FILE__) . '/lang/eng.php';
            $pdf::setLanguageArray($l);
        }

        $export = "
        ";

        $export .=
            "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"55%\"><div style=\"text-align: right; font-size:14px\">" .
            $acctsavingscashmutation['savings_account_no'] .
            "</div></td>
                <td width=\"45%\"><div style=\"text-align: right; font-size:14px\">" .
            $acctsavingscashmutation['member_name'] .
            "</div></td>
            </tr>
            <tr>
                <td width=\"52%\"><div style=\"text-align: right; font-size:14px\">" .
            $acctsavingscashmutation['validation_on'] .
            "</div></td>
                <td width=\"18%\"><div style=\"text-align: right; font-size:14px\">" .
            $validation_name .
            "</div></td>
                <td width=\"30%\"><div style=\"text-align: right; font-size:14px\"> IDR &nbsp; " .
            number_format($acctsavingscashmutation['savings_cash_mutation_amount'], 2) .
            "</div></td>
            </tr>
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Validasi Mutasi Tunai Tabungan.pdf';
        $pdf::Output($filename, 'I');
    }
}
