<?php

namespace App\Http\Controllers;

use App\Models\AcctAccount;
use App\Models\AcctSavings;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class preferenceCompanyController extends Controller
{
    public function index() {
        $data = PreferenceCompany::first();
        $saving = AcctSavings::where('data_state',0)->get()->pluck('savings_name','savings_id');
        $acc = AcctAccount::where('data_state',0)->select(DB::raw("account_id, CONCAT(account_code,' - ', account_name) as account_code "))->where('data_state',0)->get()->pluck('account_code','account_id');
        return view('content.PreferenceCompany.index',compact('data','acc','saving'));
    }
    public function processEdit(Request $request) {
        $configdata = $request->account;
         try {
         DB::beginTransaction();
         $config = PreferenceCompany::first();
         $configold = $config;
        //  $config = $this->updateAccount($request,$config,$configold);
         $config->account_interest_id = $configdata['account_interest_id'];
         $config->deposito_profit_sharing_id = $configdata['deposito_profit_sharing_id'];
         $config->savings_profit_sharing_id = $configdata['savings_profit_sharing_id'];
         $config->account_credits_payment_fine = $configdata['account_credits_payment_fine'];
         $config->account_penalty_id = $configdata['account_penalty_id'];
         $config->account_notary_cost_id = $configdata['account_notary_cost_id'];
         $config->account_insurance_cost_id = $configdata['account_insurance_cost_id'];
         $config->account_mutation_adm_id = $configdata['account_mutation_adm_id'];
         $config->account_savings_tax_id = $configdata['account_savings_tax_id'];
         $config->save();
         DB::commit();
         return redirect()->back()->with(['pesan' => 'Edit Konfigurasi Perusahaan Sukses','alert' => 'success']);
         } catch (\Exception $e) {
         DB::rollBack();
         report($e);
         return redirect()->back()->with(['pesan' => 'Edit Konfigurasi Perusahaan Gagal','alert' => 'error']);
         }
    }
    protected function updateAccount(Request $request,$config,$configold) {
        $accountdata = $request->account;
        if($configold->principal_savings_id!=$accountdata['principal_savings_id']){
            $account = AcctAccount::find($configold->principal_savings_id);
            $account->savings_status=0;
            $account->save();
            $account = AcctAccount::find($accountdata['principal_savings_id']);
            $account->savings_status=1;
            $account->save();
            $config->principal_savings_id = $accountdata['principal_savings_id'];
        }
        return $config;
    }
}
