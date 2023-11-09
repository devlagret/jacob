<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcctDepositoAccountBlockir;
use App\Models\AcctDepositoAccount;
use App\DataTables\AcctDepositoAccountBlockir\AcctDepositoAccountBlockirDataTable;
use App\DataTables\AcctDepositoAccountBlockir\CoreMemberDataTable;
use App\Helpers\Configuration;

class AcctDepositoAccountBlockirController extends Controller
{
    public function index(AcctDepositoAccountBlockirDataTable $dataTable)
    {
        session()->forget('core_member_deposito');
        session()->forget('datases');

        return $dataTable->render('content.AcctDepositoAccountBlockir.List.index');
    }

    public function add()
    {
        $blockirtype    = array_filter(Configuration::BlockirType());
        $corememberses  = session()->get('core_member_deposito');
        $datases        = session()->get('datases');

        return view('content.AcctDepositoAccountBlockir.Add.index', compact('blockirtype','corememberses','datases'));
    }

    public function modalMember(CoreMemberDataTable $dataTable)
    {
        return $dataTable->render('content.AcctDepositoAccountBlockir.Add.CoreMemberModal.index');
    }

    public function selectMember($deposito_account_id)
    {
        $coremember = AcctDepositoAccount::join('core_member','core_member.member_id','=','acct_deposito_account.member_id')
        ->join('acct_deposito','acct_deposito.deposito_id','=','acct_deposito_account.deposito_id')
        ->where('acct_deposito_account.data_state',0)
        ->where('acct_deposito_account.deposito_account_id', $deposito_account_id)
        ->first();
        
        $data = array(
            'deposito_account_no'               => $coremember['deposito_account_no'],
            'member_id'                         => $coremember['member_id'],
            'deposito_account_id'               => $coremember['deposito_account_id'],
            'member_name'                       => $coremember['member_name'],
            'deposito_name'                     => $coremember['deposito_name'],
            'member_address'                    => $coremember['member_address'],
            'member_identity_no'                => $coremember['member_identity_no'],
            'deposito_account_amount'           => $coremember['deposito_account_amount'],
            'deposito_id'                       => $coremember['deposito_id'],
        );

        session()->put('core_member_deposito', $data);

        return redirect('deposito-account-blockir/add');
    }

    public function processAdd(Request $request)
    {
        $fields = $request->validate([
            'deposito_account_no'               => ['required'],
            'member_id'                         => ['required'],
            'deposito_account_id'               => ['required'],
            'member_name'                       => ['required'],
            'deposito_name'                     => ['required'],
            'member_address'                    => ['required'],
            'member_identity_no'                => ['required'],
            'deposito_account_amount'           => ['required'],
            'deposito_account_blockir_type'     => ['required'],
            'deposito_account_blockir_amount'   => ['required'],
        ]);

        $corememberses = session()->get('core_member_deposito');

        $data = array(
            'deposito_account_id'               => $fields['deposito_account_id'],
            'branch_id'                         => auth()->user()->branch_id,
            'deposito_id'                       => $corememberses['deposito_id'],
            'member_id'                         => $fields['member_id'],
            'deposito_account_blockir_type'     => $fields['deposito_account_blockir_type'],
            'deposito_account_blockir_date'     => date('Y-m-d'),
            'deposito_account_blockir_amount'   => $fields['deposito_account_blockir_amount'],
            'deposito_account_blockir_status'   => 1,
            'created_id'                        => auth()->user()->user_id,
        );
        
        if (AcctDepositoAccountBlockir::create($data)) {
            $dataupdate                                     = AcctDepositoAccount::findOrFail($fields['deposito_account_id']);
            $dataupdate->deposito_account_blockir_type      = $fields['deposito_account_blockir_type'];
            $dataupdate->deposito_account_blockir_amount    = $fields['deposito_account_blockir_amount'];
            $dataupdate->deposito_account_blockir_status    = 1;
            $dataupdate->updated_id                         = auth()->user()->user_id;

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

        return redirect('deposito-account-blockir')->with($message);
    }

    public function elementsAdd(Request $request)
    {
        $datases = session()->get('datases');
        if(!$datases || $datases == ''){
            $datases['deposito_account_blockir_type']       = '';
            $datases['deposito_account_blockir_amount']     = '';
        }
        $datases[$request->name] = $request->value;
        session()->put('datases', $datases);
    }

    public function resetAdd()
    {
        session()->forget('core_member_deposito');
        session()->forget('datases');

        return redirect('deposito-account-blockir/add');
    }

    public function addUnblockir($deposito_account_blockir_id)
    {
        $depositoaccountblockir = AcctDepositoAccountBlockir::where('deposito_account_blockir_id', $deposito_account_blockir_id)
        ->first();

        $data                                   = AcctDepositoAccountBlockir::findOrFail($deposito_account_blockir_id);
        $data->deposito_account_unblockir_date  = date('Y-m-d');
        $data->deposito_account_blockir_status  = 0;
        $data->updated_id                       = auth()->user()->user_id;

        if ($data->save()) {
            $dataupdate                                     = AcctDepositoAccount::findOrFail($depositoaccountblockir['deposito_account_id']);
            $dataupdate->deposito_account_blockir_type      = 9;
            $dataupdate->deposito_account_blockir_amount    = 0;
            $dataupdate->deposito_account_blockir_status    = 0;
            $dataupdate->updated_id                         = auth()->user()->user_id;

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

        return redirect('deposito-account-blockir')->with($message);
    }
}
