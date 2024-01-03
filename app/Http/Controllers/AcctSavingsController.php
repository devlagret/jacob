<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctSavings;
use App\Models\AcctAccount;
use App\DataTables\AcctSavingsDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;

class AcctSavingsController extends Controller
{
    public function index(AcctSavingsDataTable $dataTable)
    {
        return $dataTable->render('content.AcctSavings.List.index');
    }

    public function add()
    {
        $config                 = theme()->getOption('page', 'view');
        $savingsprofitsharing   = Configuration::SavingsProfitSharing();
        $acctaccount            = AcctAccount::select('account_id', DB::Raw('CONCAT(account_code, " - ", account_name) as full_account'))
        ->where('data_state', 0)
        ->get();

        return view('content.AcctSavings.Add.index', compact('savingsprofitsharing', 'acctaccount'));
    }

    public function processAdd(Request $request)
    {
        $fields = request()->validate([
            'savings_code'              => ['required'],
            'savings_name'              => ['required'],
            'account_id'                => ['required'],
            'savings_profit_sharing'    => ['required'],
            'account_basil_id'          => ['required'],
            'savings_interest_rate'     => ['required'],
        ]);
        
        $savings  = array(
            'savings_code'              => $fields['savings_code'],
            'savings_name'              => $fields['savings_name'],
            'account_id'                => $fields['account_id'],
            'savings_profit_sharing'    => $fields['savings_profit_sharing'],
            'account_basil_id'          => $fields['account_basil_id'],
            'savings_interest_rate'     => $fields['savings_interest_rate'],
            'created_id'                => auth()->user()->user_id,
        );
        
        if(AcctSavings::create($savings)){
            $message = array(
                'pesan' => 'Kode Tabungan berhasil ditambah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Tabungan gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('savings')->with($message);
    }

    public function edit($savings_id)
    {
        $config                 = theme()->getOption('page', 'view');
        $savings                = AcctSavings::findOrFail($savings_id);
        $savingsprofitsharing   = Configuration::SavingsProfitSharing();
        $acctaccount            = AcctAccount::select('account_id', DB::Raw('CONCAT(account_code, " - ", account_name) as full_account'))
        ->where('data_state', 0)
        ->get();

        return view('content.AcctSavings.Edit.index', compact('savings', 'savingsprofitsharing', 'acctaccount'));
    }

    public function processEdit(Request $request)
    {
        $fields = $request->validate([
            'savings_id'                =>  ['required'],
            'savings_code'              =>  ['required'],
            'savings_name'              =>  ['required'],
            'account_id'                =>  ['required'],
            'savings_profit_sharing'    =>  ['required'],
            'account_basil_id'          =>  ['required'],
            'savings_interest_rate'     =>  ['required'],
            'min_saving' => ['required'],
        ]);
        
        $savings                            = AcctSavings::findOrFail($fields['savings_id']);
        $savings->savings_code              = $fields['savings_code'];
        $savings->savings_name              = $fields['savings_name'];
        $savings->account_id                = $fields['account_id'];
        $savings->savings_profit_sharing    = $fields['savings_profit_sharing'];
        $savings->account_basil_id          = $fields['account_basil_id'];
        $savings->savings_interest_rate     = $fields['savings_interest_rate'];
        $savings->minimum_first_deposit_amount = $fields['min_saving'];
        $savings->updated_id                = auth()->user()->user_id;

        if($savings->save()){
            $message = array(
                'pesan' => 'Kode Tabungan berhasil diubah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Tabungan gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('savings')->with($message);
    }

    public function delete($savings_id)
    {
        $savings               = AcctSavings::findOrFail($savings_id);
        $savings->data_state   = 1;
        if($savings->save()){
            $message = array(
                'pesan' => 'Kode Tabungan berhasil dihapus',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Tabungan gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('savings')->with($message);
    }

    public function getSavingsName($savings_id){
        $savings_name = AcctSavings::select('*')
        ->where('savings_id',$savings_id)
        ->first();
        return $savings_name['savings_name'] ?? '';
    }

}
