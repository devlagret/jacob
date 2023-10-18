<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\AcctDepositoDataTable;
use App\Models\AcctDeposito;
use App\Models\AcctAccount;

class AcctDepositoController extends Controller
{
    public function index(AcctDepositoDataTable $dataTable)
    {
        return $dataTable->render('content.AcctDeposito.List.index');
    }

    public function add()
    {
        $acctacount = AcctAccount::select('account_id', 'account_name','account_code')
        ->where('data_state', 0)
        ->get();

        return view('content.AcctDeposito.Add.index', compact('acctacount'));
    }

    public function processAdd(Request $request)
    {
        $fields = $request->validate([
            'deposito_code'             => ['required'],
            'deposito_name'             => ['required'],
            'account_id'                => ['required'],
            'account_basil_id'          => ['required'],
            'deposito_period'           => ['required'],
            'deposito_interest_rate'    => ['required'],
        ]);

        $deposito = array(
            'deposito_code'             => $fields['deposito_code'],
            'deposito_name'             => $fields['deposito_name'],
            'account_id'                => $fields['account_id'],
            'account_basil_id'          => $fields['account_basil_id'],
            'deposito_period'           => $fields['deposito_period'],
            'deposito_interest_rate'    => $fields['deposito_interest_rate'],
            'created_id'                => auth()->user()->user_id,
        );

        if(AcctDeposito::create($deposito)){
            $message = array(
                'pesan' => 'Kode Simpanan Berjangka berhasil ditambah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Simpanan Berjangka gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('deposito')->with($message);
    }

    public function edit($id)
    {
        $acctacount = AcctAccount::select('account_id', 'account_name','account_code')
        ->where('data_state', 0)
        ->get();
        $deposito = AcctDeposito::select('deposito_id','account_id','account_basil_id','deposito_code','deposito_name','deposito_period','deposito_interest_rate')
        ->where('data_state', 0)
        ->where('deposito_id', $id)
        ->first();

        return view('content.AcctDeposito.Edit.index',compact('deposito','acctacount'));
    }

    public function processEdit(Request $request)
    {
        $fields = $request->validate([
            'deposito_id'               => ['required'],
            'deposito_code'             => ['required'],
            'deposito_name'             => ['required'],
            'account_id'                => ['required'],
            'account_basil_id'          => ['required'],
            'deposito_period'           => ['required'],
            'deposito_interest_rate'    => ['required'],
        ]);

        $deposito                             = AcctDeposito::findOrFail($fields['deposito_id']);
        $deposito->deposito_name              = $fields['deposito_name'];
        $deposito->deposito_code              = $fields['deposito_code'];
        $deposito->account_id                 = $fields['account_id'];
        $deposito->account_basil_id           = $fields['account_basil_id'];
        $deposito->deposito_period            = $fields['deposito_period'];
        $deposito->deposito_interest_rate     = $fields['deposito_interest_rate'];
        $deposito->updated_id                 = auth()->user()->user_id;

        if($deposito->save()){
            $message = array(
                'pesan' => 'Kode Simpanan Berjangka berhasil diubah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Simpanan Berjangka gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('deposito')->with($message);
    }

    public function delete($id)
    {
        $deposito                 = AcctDeposito::findOrFail($id);
        $deposito->data_state     = 1;
        $deposito->updated_id     = auth()->user()->user_id;

        if($deposito->save()){
            $message = array(
                'pesan' => 'Kode Simpanan Berjangka berhasil dihapus',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Simpanan Berjangka gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('deposito')->with($message);
    }
}
