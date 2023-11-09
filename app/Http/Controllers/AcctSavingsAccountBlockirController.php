<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcctSavingsAccountBlockir;
use App\Models\AcctSavingsAccount;
use App\Models\CoreMember;
use App\DataTables\AcctSavingsAccountBlockir\AcctSavingsAccountBlockirDataTable;
use App\DataTables\AcctSavingsAccountBlockir\CoreMemberDataTable;
use App\Helpers\Configuration;

class AcctSavingsAccountBlockirController extends Controller
{
    public function index(AcctSavingsAccountBlockirDataTable $dataTable)
    {
        session()->forget('core_member_savings');
        session()->forget('datases');

        return $dataTable->render('content.AcctSavingsAccountBlockir.List.index');
    }

    public function add()
    { 
        $blockirtype    = array_filter(Configuration::BlockirType());
        $corememberses  = session()->get('core_member_savings');
        $datases        = session()->get('datases');

        return view('content.AcctSavingsAccountBlockir.Add.index', compact('blockirtype','corememberses','datases'));
    }

    public function elementsAdd(Request $request)
    {
        $datases = session()->get('datases');
        if(!$datases || $datases == ''){
            $datases['savings_account_blockir_type']       = '';
            $datases['savings_account_blockir_amount']     = '';
        }
        $datases[$request->name] = $request->value;
        session()->put('datases', $datases);
    }

    public function resetAdd()
    {
        session()->forget('core_member_savings');
        session()->forget('datases');

        return redirect('savings-account-blockir/add');
    }

    public function processAdd(Request $request)
    {
        $fields = $request->validate([
            'savings_account_no'                => ['required'],
            'member_id'                         => ['required'],
            'savings_account_id'                => ['required'],
            'member_name'                       => ['required'],
            'savings_name'                      => ['required'],
            'member_address'                    => ['required'],
            'member_identity_no'                => ['required'],
            'savings_account_last_balance'      => ['required'],
            'savings_account_blockir_type'      => ['required'],
            'savings_account_blockir_amount'    => ['required'],
        ]);

        $corememberses  = session()->get('core_member_savings');

        $data = array(
            'savings_account_id'                => $fields['savings_account_id'],
            'branch_id'                         => auth()->user()->branch_id,
            'member_id'                         => $fields['member_id'],
            'savings_id'                        => $corememberses['savings_id'],
            'savings_account_blockir_type'      => $fields['savings_account_blockir_type'],
            'savings_account_blockir_date'      => date('Y-m-d'),
            'savings_account_blockir_amount'    => $fields['savings_account_blockir_amount'],
            'savings_account_blockir_status'    => 1,
            'created_id'                        => auth()->user()->user_id,
        );

        if (AcctSavingsAccountBlockir::create($data)) {
            $dataupdate                                 = AcctSavingsAccount::findOrFail($fields['savings_account_id']);
            $dataupdate->savings_account_blockir_type   = $fields['savings_account_blockir_type'];
            $dataupdate->savings_account_blockir_amount = $fields['savings_account_blockir_amount'];
            $dataupdate->savings_account_blockir_status = 1;
            $dataupdate->updated_id                     = auth()->user()->user_id;

            if ($dataupdate->save()) {
                $message = array(
                    'pesan' => ' Blockir Rekening berhasil ditambah',
                    'alert' => 'success'
                );
            }else{
                $message = array(
                    'pesan' => ' Blockir Rekening gagal ditambah',
                    'alert' => 'error'
                );
            }
        }

        return redirect('savings-account-blockir')->with($message);
    }

    public function addUnblockir($savings_account_blockir_id)
    {
        $savingsaccountblockir = AcctSavingsAccountBlockir::where('savings_account_blockir_id', $savings_account_blockir_id)
        ->first();

        $data                                  = AcctSavingsAccountBlockir::findOrFail($savings_account_blockir_id);
        $data->savings_account_unblockir_date  = date('Y-m-d');
        $data->savings_account_blockir_status  = 0;
        $data->updated_id                      = auth()->user()->user_id;

        if ($data->save()) {
            $dataupdate                                    = AcctSavingsAccount::findOrFail($savingsaccountblockir['savings_account_id']);
            $dataupdate->savings_account_blockir_type      = 9;
            $dataupdate->savings_account_blockir_amount    = 0;
            $dataupdate->savings_account_blockir_status    = 0;
            $dataupdate->updated_id                        = auth()->user()->user_id;

            if ($dataupdate->save()) {
                $message = array(
                    'pesan' => ' UnBlockir Rekening berhasil ditambah',
                    'alert' => 'success'
                );
            }else{
                $message = array(
                    'pesan' => ' UnBlockir Rekening gagal ditambah',
                    'alert' => 'error'
                );
            }
        }

        return redirect('savings-account-blockir')->with($message);
    }

    public function modalMember(CoreMemberDataTable $dataTable)
    {
        return $dataTable->render('content.AcctSavingsAccountBlockir.Add.CoreMemberModal.index');
    }

    public function selectMember($savings_account_id)
    {
        $coremember = AcctSavingsAccount::join('core_member','core_member.member_id','=','acct_savings_account.member_id')
        ->join('acct_savings','acct_savings.savings_id','=','acct_savings_account.savings_id')
        ->where('acct_savings_account.data_state',0)
        ->where('acct_savings_account.savings_account_id', $savings_account_id)
        ->first();

        $data = array(
            'savings_account_no'               => $coremember['savings_account_no'],
            'member_id'                        => $coremember['member_id'],
            'savings_account_id'               => $savings_account_id,
            'member_name'                      => $coremember['member_name'],
            'savings_name'                     => $coremember['savings_name'],
            'member_address'                   => $coremember['member_address'],
            'member_identity_no'               => $coremember['member_identity_no'],
            'savings_account_last_balance'     => $coremember['savings_account_last_balance'],
            'savings_id'                       => $coremember['savings_id'],
        );

        session()->put('core_member_savings', $data);

        return redirect('savings-account-blockir/add');
    }
}
