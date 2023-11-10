<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcctAccount;
use App\Models\AcctAccountSetting;
use App\Models\AcctCreditsAccount;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\AcctDepositoAccrual;
use App\Models\AcctProfitLossReport;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsCashMutation;
use App\Models\CloseCashierLog;
use App\Models\CoreEmployee;
use App\Models\CoreMember;
use App\Models\CoreMemberKopkar;
use App\Models\Expenditure;
use App\Models\InvtItem;
use App\Models\InvtItemBarcode;
use App\Models\InvtItemCategory;
use App\Models\InvtItemPackge;
use App\Models\InvtItemRack;
use App\Models\InvtItemStock;
use App\Models\InvtItemUnit;
use App\Models\InvtWarehouse;
use App\Models\JournalVoucher;
use App\Models\JournalVoucherItem;
use App\Models\PreferenceTransactionModule;
use App\Models\PreferenceVoucher;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SystemLoginLog;
use App\Models\User;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Str;

class ApiController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), ['username'=>'required', 'password'=>'required'],[  'required' => 'The :attribute field is required.']);
        if ($validator->fails()) {
            return 'username and/or password required';
        }
        $login = Auth::Attempt($validator->validated());
        if ($login) {
            $user = Auth::user();
            $user->save();
            $token = $user->createToken('token-name')->plainTextToken;
            return response()->json([
                'message' => 'Login Berhasil',
                'conntent' => $user,
                'token' => $token
            ],201);
        }else{
            return response()->json([
                'response_code' => 404,
                'message' => 'Username atau Password Tidak Ditemukan!'
            ]);
        }
    }
    public function tst(Request $request) {
        
        return response(['mesage'=>'test']);
    }
    //data simpanan
    public function getDataSavings(){
        $data = AcctSavingsAccount::with('member','savingdata')
        ->get();

        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }

    //data simpanan berjangka
    public function getDataDeposito(){
        $data = AcctDepositoAccount::withoutGlobalScopes()
        ->join('core_member','acct_deposito_account.member_id','core_member.member_id')
        ->join('acct_deposito','acct_deposito.deposito_id','acct_d+eposito_account.deposito_id')
        ->where('acct_deposito_account.data_state',0)
        ->get();
        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }

    //pinjaman
    public function getDataCredit(){
        $data = AcctCreditsAccount::withoutGlobalScopes()
        ->join('core_member','acct_deposito_account.member_id','core_member.member_id')
        ->join('acct_credits','acct_credits.credits_id','acct_credits_account.credits_id')
        ->get();
        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }

     //member
     public function getDataMembers(){
        $data = CoreMember::withoutGlobalScopes()
        ->get();
        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }

     //data simpanan by id simpanan
    public function PostSavingsById($savings_account_id){
        $data = AcctSavingsAccount::withoutGlobalScopes()
        ->join('core_member','acct_savings_account.member_id','core_member.member_id')
        ->join('acct_savings','acct_savings.savings_id','acct_savings_account.savings_id')
        ->where('acct_savings_account.savings_account_id',$savings_account_id)
        ->first();

        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }

     //data simpanan by no simpanan
     public function PostSavingsByNo($savings_account_no){
        $data = AcctSavingsAccount::withoutGlobalScopes()
        ->join('core_member','acct_savings_account.member_id','core_member.member_id')
        ->join('acct_savings','acct_savings.savings_id','acct_savings_account.savings_id')
        ->where('acct_savings_account.savings_account_no','LIKE',$savings_account_no)
        ->first();

        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }


    public function logout(Request $request){
        $user = auth()->user();
        $user_state = User::findOrFail($user['user_id']);
        $user_state->save();

        auth()->user()->tokens()->delete();
    
        return [
            'message' => 'Logged Out'
        ];
    }

    public function getLoginState(Request $request){
        return response([
            'state'          => "login",
        ],201);
    }
    public function deposit(Request $request) {
        $request->validate(['savings_account_id'=>'required','savings_cash_mutation_amount'=>'required']);
        try {
            $savingacc = AcctSavingsAccount::find($request->savings_account_id);
        DB::beginTransaction();
        AcctSavingsCashMutation::create( [
            'savings_account_id' => $request['savings_account_id'],
            'mutation_id' => 1,
            'member_id' => $savingacc->member_id,
            'savings_id' => $savingacc->savings_id,
            'savings_cash_mutation_date' => date('Y-m-d'),
            'savings_cash_mutation_opening_balance' => $savingacc->savings_cash_mutation_last_balance,
            'savings_cash_mutation_amount' => $request->savings_cash_mutation_amount,
            'savings_cash_mutation_amount_adm' => $request->savings_cash_mutation_amount_adm,
            'savings_cash_mutation_last_balance' => $savingacc->savings_cash_mutation_last_balance,
            'savings_cash_mutation_remark' => $request->savings_cash_mutation_remark,
            'branch_id' => Auth::user()->branch_id,
            'operated_name' => Auth::user()->username,
            'created_id' => Auth::user()->user_id,
        ]);
        DB::commit();
        return response('Deposit Success');
        } catch (Exception $e) {
        DB::rollBack();
        report($e);
        return response($e,500);
        }
    }
    public function withdraw(Request $request) {
        $request->validate(['savings_account_id'=>'required','savings_cash_mutation_amount'=>'required']);
        try {
            $savingacc = AcctSavingsAccount::find($request->savings_account_id);
        DB::beginTransaction();
        AcctSavingsCashMutation::create( [
            'savings_account_id' => $request['savings_account_id'],
            'mutation_id' => 2,
            'member_id' => $savingacc->member_id,
            'savings_id' => $savingacc->savings_id,
            'savings_cash_mutation_date' => date('Y-m-d'),
            'savings_cash_mutation_opening_balance' => $savingacc->savings_cash_mutation_last_balance,
            'savings_cash_mutation_amount' => $request->savings_cash_mutation_amount,
            'savings_cash_mutation_amount_adm' => $request->savings_cash_mutation_amount_adm,
            'savings_cash_mutation_last_balance' => $savingacc->savings_cash_mutation_last_balance,
            'savings_cash_mutation_remark' => $request->savings_cash_mutation_remark,
            'branch_id' => Auth::user()->branch_id,
            'operated_name' => Auth::user()->username,
            'created_id' => Auth::user()->user_id,
        ]);
        DB::commit();
        return response('Withdraw Success');
        } catch (Exception $e) {
        DB::rollBack();
        report($e);
        return response($e,500);
        }
    }
}
