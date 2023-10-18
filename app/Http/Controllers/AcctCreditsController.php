<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctCredits;
use App\Models\AcctAccount;
use App\DataTables\AcctCreditsDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;

class AcctCreditsController extends Controller
{
    public function index(AcctCreditsDataTable $dataTable)
    {
        return $dataTable->render('content.AcctCredits.List.index');
    }

    public function add()
    {
        $config                 = theme()->getOption('page', 'view');
        $acctaccount            = AcctAccount::select('account_id', DB::Raw('CONCAT(account_code, " - ", account_name) as full_account'))
        ->where('data_state', 0)
        ->get();

        return view('content.AcctCredits.Add.index', compact('acctaccount'));
    }

    public function processAdd(Request $request)
    {
        $fields = request()->validate([
            'credits_code'              => ['required'],
            'credits_name'              => ['required'],
            'receivable_account_id'     => ['required'],
            'income_account_id'         => ['required'],
            'credits_fine'              => ['required'],
        ]); 

        $credits  = array(  
            'credits_code'              => $fields['credits_code'],
            'credits_name'              => $fields['credits_name'],
            'receivable_account_id'     => $fields['receivable_account_id'],
            'income_account_id'         => $fields['income_account_id'],
            'credits_fine'              => $fields['credits_fine'],
            'created_id'                => auth()->user()->user_id,
        );
        
        if(AcctCredits::create($credits)){
            $message = array(
                'pesan' => 'Kode Pinjaman berhasil ditambah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Pinjaman gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('credits')->with($message);
    }

    public function edit($credits_id)
    {
        $config                 = theme()->getOption('page', 'view');
        $credits                = AcctCredits::findOrFail($credits_id);
        $acctaccount            = AcctAccount::select('account_id', DB::Raw('CONCAT(account_code, " - ", account_name) as full_account'))
        ->where('data_state', 0)
        ->get();

        return view('content.AcctCredits.Edit.index', compact('credits', 'acctaccount'));
    }

    public function processEdit(Request $request)
    {
        $fields = $request->validate([
            'credits_id'                    => ['required'],
            'credits_code'                  => ['required'],
            'credits_name'                  => ['required'],
            'receivable_account_id'         => ['required'],
            'income_account_id'             => ['required'],
            'credits_fine'                  => ['required'],
        ]);

        $credits                            = AcctCredits::findOrFail($fields['credits_id']);
        $credits->credits_code              = $fields['credits_code'];
        $credits->credits_name              = $fields['credits_name'];
        $credits->receivable_account_id     = $fields['receivable_account_id'];
        $credits->income_account_id         = $fields['income_account_id'];
        $credits->credits_fine              = $fields['credits_fine'];
        $credits->updated_id                = auth()->user()->user_id;

        if($credits->save()){
            $message = array(
                'pesan' => 'Kode Pinjaman berhasil diubah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Pinjaman gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('credits')->with($message);
    }

    public function delete($credits_id)
    {
        $credits               = AcctCredits::findOrFail($credits_id);
        $credits->data_state   = 1;
        if($credits->save()){
            $message = array(
                'pesan' => 'Kode Pinjaman berhasil dihapus',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Pinjaman gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('credits')->with($message);
    }
}
