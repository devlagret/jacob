<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\AcctBankAccountDataTable;
use App\Models\AcctBankAccount;
use App\Models\AcctAccount;

class AcctBankAccountController extends Controller
{
    public function index(AcctBankAccountDataTable $dataTable)
    {
        return $dataTable->render('content.AcctBankAccount.List.index');
    }

    public function add()
    {
        $acctacount = AcctAccount::select('account_id', 'account_name','account_code')
        ->where('data_state', 0)
        ->get();

        return view('content.AcctBankAccount.Add.index', compact('acctacount'));
    }

    public function processAdd(Request $request)
    {
        $fields = $request->validate([
            'bank_account_code'     => ['required'],
            'bank_account_name'     => ['required'],
            'bank_account_no'       => ['required'],
            'account_id'            => ['required'],
        ]);

        $bankaccount = array(
            'bank_account_code'     => $fields['bank_account_code'],
            'bank_account_name'     => $fields['bank_account_name'],
            'bank_account_no'       => $fields['bank_account_no'],
            'account_id'            => $fields['account_id'],
            'created_id'            => auth()->user()->user_id
        );

        if(AcctBankAccount::create($bankaccount)){
            $message = array(
                'pesan' => 'Kode Bank berhasil ditambah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Bank gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('bank-account')->with($message);
    }

    public function edit($id)
    {
        $bankaccount = AcctBankAccount::select('bank_account_code', 'bank_account_name', 'bank_account_no', 'account_id', 'bank_account_id')
        ->where('bank_account_id', $id)
        ->where('data_state', 0)
        ->first();
        $acctacount = AcctAccount::select('account_id', 'account_name','account_code')
        ->where('data_state', 0)
        ->get();

        return view('content.AcctBankAccount.Edit.index', compact('bankaccount','acctacount'));
    }

    public function processEdit(Request $request)
    {
        $fields = $request->validate([
            'bank_account_id'       => ['required'],
            'bank_account_code'     => ['required'],
            'bank_account_name'     => ['required'],
            'bank_account_no'       => ['required'],
            'account_id'            => ['required'],
        ]);

        $bankaccount                    = AcctBankAccount::findOrFail($fields['bank_account_id']);
        $bankaccount->bank_account_code = $fields['bank_account_code'];
        $bankaccount->bank_account_name = $fields['bank_account_name'];
        $bankaccount->bank_account_no   = $fields['bank_account_no'];
        $bankaccount->account_id        = $fields['account_id'];
        $bankaccount->updated_id        = auth()->user()->user_id;

        if($bankaccount->save()){
            $message = array(
                'pesan' => 'Kode Bank berhasil diubah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Bank gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('bank-account')->with($message);
    }

    public function delete($id)
    {
        $bankaccount                = AcctBankAccount::findOrFail($id);
        $bankaccount->data_state    = 1;
        $bankaccount->updated_id    = auth()->user()->user_id;

        if($bankaccount->save()){
            $message = array(
                'pesan' => 'Kode Bank berhasil dihapus',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Bank gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('bank-account')->with($message);
    }
}
