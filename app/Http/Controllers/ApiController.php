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
use App\Models\AcctSavingsMemberDetail;
use App\Models\CloseCashierLog;
use App\Models\CoreEmployee;
use App\Models\CoreMember;
use App\Models\SystemLoginLog;
use App\Models\User;
use Auth;
use Carbon\Carbon;
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
        ->where('acct_deposito_account.data_state',0)
        ->get();
        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }


     //member
     public function getDataMembers(){
        $data = CoreMember::withoutGlobalScopes()
        ->where('data_state',0)
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
        ->where('acct_savings_account.data_state',0)
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
        ->where('acct_savings_account.data_state',0)
        ->first();

        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }

    //data simpanan by no member
    public function PostSavingsByMember($member_id){
        $data = AcctSavingsAccount::with('member','savingdata')
        ->withoutGlobalScopes() 
        ->where('member_id',$member_id)
        ->get();

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
    public function deposit(Request $request,$savings_account_id) {
        $request->validate(['savings_cash_mutation_amount'=>'required']);
        $sai = $request->savings_account_id;
        if(!empty($savings_account_id)){
            $sai = $savings_account_id;
        }
        try {
            $savingacc = AcctSavingsAccount::find($sai);
            $savingacc->savings_account_pickup_date=date('Y-m-d');
            $savingacc->save();
        DB::beginTransaction();
        AcctSavingsCashMutation::create( [
            'savings_account_id' => $request['savings_account_id'],
            'mutation_id' => 1,
            'member_id' => $savingacc->member_id,
            'savings_id' => $savingacc->savings_id,
            'savings_cash_mutation_date' => date('Y-m-d'),
            'pickup_date' => date('Y-m-d'),
            'savings_cash_mutation_opening_balance' => $savingacc->savings_cash_mutation_last_balance,
            'savings_cash_mutation_amount' => $request->savings_cash_mutation_amount,
            'savings_cash_mutation_amount_adm' => $request->savings_cash_mutation_amount_adm,
            'savings_cash_mutation_last_balance' => $savingacc->savings_cash_mutation_last_balance,
            'savings_cash_mutation_remark' => $request->savings_cash_mutation_remark,
            'branch_id' =>  $savingacc->branch_id,
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
    public function withdraw(Request $request,$savings_account_id) {
        $request->validate(['savings_cash_mutation_amount'=>'required']);
        $sai = $request->savings_account_id;
        $savingacc1 = AcctSavingsAccount::find($sai);
        if(!empty($savings_account_id)){
            $sai = $savings_account_id;
        }
        print($savingacc1);

        // if($request->savings_cash_mutation_amount > $savingacc1['savings_cash_mutation_last_balance']){
        //     return response('Withdraw Failed');
        // }
        try {
            $savingacc = AcctSavingsAccount::find(trim(preg_replace("/[^0-9]/", '', $sai)));
        DB::beginTransaction();
        AcctSavingsCashMutation::create( [
            'savings_account_id' => $request['savings_account_id'],
            'mutation_id' => 2,
            'member_id' => $savingacc->member_id,
            'savings_id' => $savingacc->savings_id,
            'savings_cash_mutation_date' => date('Y-m-d'),
            'pickup_date' => date('Y-m-d'),
            'savings_cash_mutation_opening_balance' => $savingacc->savings_cash_mutation_last_balance,
            'savings_cash_mutation_amount' => $request->savings_cash_mutation_amount,
            'savings_cash_mutation_amount_adm' => $request->savings_cash_mutation_amount_adm,
            'savings_cash_mutation_last_balance' => $savingacc->savings_cash_mutation_last_balance,
            'savings_cash_mutation_remark' => $request->savings_cash_mutation_remark,
            'branch_id' =>  $savingacc->branch_id,
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


    //data mutasi setor simpanan tunai 
    public function GetDeposit(){
        $data = AcctSavingsCashMutation::with('member','mutation')
        ->withoutGlobalScopes() 
        // ->where('savings_cash_mutation_date','>=',$start_date)
        ->where('savings_cash_mutation_date',Carbon::today())
        ->where('mutation_id',1)
        ->where('data_state',0)
        ->get();

        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }

      //data mutasi setor simpanan tunai 
      public function GetWithdraw(){
        $data = AcctSavingsCashMutation::with('member','mutation')
        ->withoutGlobalScopes() 
        // ->where('savings_cash_mutation_date','>=',$start_date)
        ->where('savings_cash_mutation_date',Carbon::today())
        ->where('mutation_id',2)
        ->where('data_state',0)
        ->get();

        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }


    //data akhir mutasi setor simpanan tunai by member 
    public function PrintmutationByMember($member_id){
        $data = AcctSavingsCashMutation::with('member','mutation')
        ->withoutGlobalScopes() 
        ->where('member.member_id',$member_id)
        ->where('mutation_id',1)
        ->where('savings_cash_mutation_date',Carbon::today())
        ->where('data_state',0)
        ->orderBy('DESC')
        ->first();

        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }


    //simpanan wajib
    public function processAddMemberSavings(Request $request,$member_id)
    {

        $member = CoreMember::where('member_id',$member_id)
        ->first();


        $data = array(
            'member_id'								=> $member_id,
            'member_name'							=> $member->member_name,
            'member_address'						=> $member->member_address,
            'mutation_id'							=> $request->mutation_id,
            'province_id'						    => $member->province_id,
            'city_id'								=> $member->city_id,
            'kecamatan_id'							=> $member->kecamatan_id,
            'kelurahan_id'							=> $member->kelurahan_id,
            'member_character'						=> $member->member_character,
            'member_principal_savings'				=> $member->member_principal_savings,
            'member_special_savings'				=> $member->member_special_savings,
            'member_mandatory_savings'				=> $request->member_mandatory_savings,
            'member_principal_savings_last_balance'	=> $member->member_principal_savings_last_balance,
            'member_special_savings_last_balance'	=> $member->member_special_savings_last_balance,
            'member_mandatory_savings_last_balance'	=> $member->member_mandatory_savings_last_balance,
            'updated_id'                            => auth()->user()->user_id,
        );



        try {
            DB::beginTransaction();
            CoreMember::where('member_id', $data['member_id'])
            ->update([
                'member_name'							=> $data['member_name'],
                'member_address'						=> $data['member_address'],
                'province_id'							=> $data['province_id'],
                'city_id'								=> $data['city_id'],
                'kecamatan_id'							=> $data['kecamatan_id'],
                'kelurahan_id'							=> $data['kelurahan_id'],
                'member_character'						=> $data['member_character'],
                'member_principal_savings'				=> $data['member_principal_savings'],
                'member_special_savings'				=> $data['member_special_savings'],
                'member_mandatory_savings'				=> $data['member_mandatory_savings'] + $member['member_mandatory_savings'],
                'member_principal_savings_last_balance'	=> $data['member_principal_savings_last_balance'],
                'member_special_savings_last_balance'	=> $data['member_special_savings_last_balance'],
                'member_mandatory_savings_last_balance'	=> $data['member_mandatory_savings_last_balance'],
                'updated_id'                            => $data['updated_id'],
            ]);

            if($data['member_principal_savings'] <> 0 || $data['member_principal_savings'] <> '' || $data['member_mandatory_savings'] <> 0 || $data['member_mandatory_savings'] <> ''  || $data['member_special_savings'] <> 0 || $data['member_special_savings'] <> ''){

                $data_detail = array (
                    'branch_id'						=> $member->branch_id,
                    'member_id'						=> $data['member_id'],
                    'mutation_id'					=> $data['mutation_id'],
                    'transaction_date'				=> date('Y-m-d'),
                    'principal_savings_amount'		=> $data['member_principal_savings'],
                    'special_savings_amount'		=> $data['member_special_savings'],
                    'mandatory_savings_amount'		=> $data['member_mandatory_savings'],
                    'operated_name'					=> $member->username,
                    'created_id'                    => $member->user_id,
                );
                AcctSavingsMemberDetail::create($data_detail);
            }

            DB::commit();
            $message = array(
                'pesan' => 'Data Anggota berhasil diubah',
                'alert' => 'success',
                'member_id' => $data['member_id']
            );
            return $message;
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            $message = array(
                'pesan' => 'Data Anggota gagal diubah',
                'alert' => 'error'
            );
            return $message;
        }

    }

    
    //pinjaman
    public function getDataCredit(){
        $data = AcctCreditsAccount::withoutGlobalScopes()
        ->join('core_member','acct_deposito_account.member_id','core_member.member_id')
        ->join('acct_credits','acct_credits.credits_id','acct_credits_account.credits_id')
        ->where('acct_credits_account.data_state',0)
        ->get();
        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }

    //data pinjaman by id pinjaman
    public function PostCreditsById($credits_account_id){
        $data = AcctCreditsAccount::withoutGlobalScopes()
        ->join('core_member','acct_credits_account.member_id','core_member.member_id')
        ->join('acct_credits','acct_credits.credits_id','acct_credits_account.credits_id')
        ->where('acct_credits_account.credits_account_id',$credits_account_id)
        ->where('acct_credits_account.data_state',0)
        ->first();

        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
        // 
    }


}
