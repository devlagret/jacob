<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcctAccount;
use App\Models\AcctAccountSetting;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPayment;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\AcctDepositoAccrual;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctProfitLossReport;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsCashMutation;
use App\Models\AcctSavingsMemberDetail;
use App\Models\CloseCashierLog;
use App\Models\CoreEmployee;
use App\Models\CoreMember;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
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
        ->where('branch_id',auth()->user()->branch_id)
        ->get();

        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }

    //data simpanan berjangka
    public function getDataDeposito(){
        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $data = AcctDepositoAccount::withoutGlobalScopes()
            ->join('core_member','acct_deposito_account.member_id','core_member.member_id')
            ->join('acct_deposito','acct_deposito.deposito_id','acct_d+eposito_account.deposito_id')
            ->where('acct_deposito_account.data_state',0)
            ->where('acct_deposito_account.data_state',0)
            ->get();
        }else{
            $data = AcctDepositoAccount::withoutGlobalScopes()
            ->join('core_member','acct_deposito_account.member_id','core_member.member_id')
            ->join('acct_deposito','acct_deposito.deposito_id','acct_d+eposito_account.deposito_id')
            ->where('acct_deposito_account.data_state',0)
            ->where('acct_deposito_account.data_state',0)
            ->where('acct_deposito_account.branch_id',auth()->user()->branch_id)
            ->get();
        }
        return response()->json([
            'data' => $data,
        ]);
        // return json_encode($data);
    }


     //member
     public function getDataMembers(){
        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
        $data = CoreMember::withoutGlobalScopes()
        ->where('data_state',0)
        ->orderBy('member_name', 'asc') 
        ->get();
        }else{
        $data = CoreMember::withoutGlobalScopes()
        ->where('data_state',0)
        ->where('branch_id',auth()->user()->branch_id)
        ->orderBy('member_name', 'asc') 
        ->get();
        }
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
        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $data = AcctSavingsCashMutation::with('member','mutation')
            ->withoutGlobalScopes() 
            ->where('savings_cash_mutation_date',Carbon::today())
            ->where('mutation_id',1)
            ->where('data_state',0)
            ->get();
        }else{
            $data = AcctSavingsCashMutation::with('member','mutation')
            ->withoutGlobalScopes() 
            ->where('savings_cash_mutation_date',Carbon::today())
            ->where('branch_id',auth()->user()->branch_id)
            ->where('mutation_id',1)
            ->where('data_state',0)
            ->get();
        }

        return response()->json([
            'data' => $data,
        ]);
    }

      //data mutasi setor simpanan tunai
      public function GetWithdraw(){
        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $data = AcctSavingsCashMutation::with('member','mutation')
            ->withoutGlobalScopes() 
            // ->where('savings_cash_mutation_date','>=',$start_date)
            ->where('savings_cash_mutation_date',Carbon::today())
            ->where('mutation_id',2)
            ->where('data_state',0)
            ->get();
        }else{
            $data = AcctSavingsCashMutation::with('member','mutation')
            ->withoutGlobalScopes() 
            // ->where('savings_cash_mutation_date','>=',$start_date)
            ->where('savings_cash_mutation_date',Carbon::today())
            ->where('branch_id',auth()->user()->branch_id)
            ->where('mutation_id',2)
            ->where('data_state',0)
            ->get();
        }

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
            'member_mandatory_savings_last_balance'	=> $request->member_mandatory_savings_last_balance,
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
                'member_mandatory_savings'				=> $data['member_mandatory_savings'],
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

    
    //ANGSURAN
    public function getDataCredit(){
        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $data = AcctCreditsAccount::withoutGlobalScopes()
            ->join('core_member','acct_credits_account.member_id','core_member.member_id')
            ->join('acct_credits','acct_credits.credits_id','acct_credits_account.credits_id')
            ->where('acct_credits_account.data_state',0)
            ->orderBy('core_member.member_name', 'asc')
            ->get();
        }else{
            $data = AcctCreditsAccount::withoutGlobalScopes()
            ->join('core_member','acct_credits_account.member_id','core_member.member_id')
            ->join('acct_credits','acct_credits.credits_id','acct_credits_account.credits_id')
            ->where('acct_credits_account.data_state',0)
            ->where('acct_credits_account.branch_id',auth()->user()->branch_id)
            ->orderBy('core_member.member_name', 'asc')
            ->get();
        }
        return response()->json([
            'data' => $data,
        ]);
    }

    //data Pinjaman by id Pinjaman
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
        
    }

    //Save Angsuran
    public function processAddCreditsPaymentCash(Request $request,$credits_account_id)
    {

//---------Cek id pinjaman
            $acctcreditsaccount = AcctCreditsAccount::with('credit','member')->find($credits_account_id);

            $acctcreditspayment = AcctCreditsPayment::select('credits_payment_date', 'credits_payment_principal', 'credits_payment_interest', 'credits_principal_last_balance', 'credits_interest_last_balance')
            ->where('credits_account_id', $credits_account_id)
            ->get();

            $credits_payment_date   = date('Y-m-d');
            $date1                  = date_create($credits_payment_date);
            $date2                  = date_create($acctcreditsaccount['credits_account_payment_date']);

            if($date1 > $date2){
                $interval                       = $date1->diff($date2);
                $credits_payment_day_of_delay   = $interval->days;
            } else {
                $credits_payment_day_of_delay 	= 0;
            }
            
            if(strpos($acctcreditsaccount['credits_account_payment_to'], ',') == true ||strpos($acctcreditsaccount['credits_account_payment_to'], '*') == true ){
                $angsuranke = substr($acctcreditsaccount['credits_account_payment_to'], -1) + 1;
            }else{
                $angsuranke = $acctcreditsaccount['credits_account_payment_to'] + 1;
            }

            $credits_payment_fine_amount 		= (($acctcreditsaccount['credits_account_payment_amount'] * $acctcreditsaccount['credits_fine']) / 100 ) * $credits_payment_day_of_delay;
            $credits_account_accumulated_fines 	= $acctcreditsaccount['credits_account_accumulated_fines'] + $credits_payment_fine_amount;

            if($acctcreditsaccount['payment_type_id'] == 1){
                $angsuranpokok 		= $acctcreditsaccount['credits_account_principal_amount'];
                $angsuranbunga 	 	= $acctcreditsaccount['credits_account_payment_amount'] - $angsuranpokok;
            } else if($acctcreditsaccount['payment_type_id'] == 2){
                $angsuranpokok 		= $anuitas[$angsuranke]['angsuran_pokok'];
                $angsuranbunga 	 	= $acctcreditsaccount['credits_account_payment_amount'] - $angsuranpokok;
            } else if($acctcreditsaccount['payment_type_id'] == 3){
                $angsuranpokok 		= $slidingrate[$angsuranke]['angsuran_pokok'];
                $angsuranbunga 	 	= $acctcreditsaccount['credits_account_payment_amount'] - $angsuranpokok;
            } else if($acctcreditsaccount['payment_type_id'] == 4){
                $angsuranpokok		= 0;
                $angsuranbunga		= $angsuran_bunga_menurunharian;
            }
        

        $creditaccount = AcctCreditsAccount::where('credits_account_id',$credits_account_id)
        ->first();

        // if(empty(Session::get('payment-token'))){
        //     return redirect('credits-payment-cash')->with(['pesan' => 'Angsuran Tunai berhasil ditambah','alert' => 'success']);
        // }
        $preferencecompany = PreferenceCompany::first();

        // $fields = request()->validate([
        //     'credits_account_id' => ['required'],
        // ]);
        
        $credits_account_payment_date = date('Y-m-d');
        if($request->credits_payment_to < $request->credits_account_period){
            if($request->credits_payment_period == 1){
                $credits_account_payment_date_old 	= date('Y-m-d', strtotime($request->credits_account_payment_date));
                $credits_account_payment_date 		= date('Y-m-d', strtotime("+1 months", strtotime($credits_account_payment_date_old)));
            } else {
                $credits_account_payment_date_old 	= date('Y-m-d', strtotime($request->credits_account_payment_date));
                $credits_account_payment_date 		= date('Y-m-d', strtotime("+1 weeks", strtotime($credits_account_payment_date_old)));
            }
        }

        DB::beginTransaction();

        try {
            $data  = array(
                'member_id'									=> $creditaccount->member_id,
				'credits_id'								=> $creditaccount->credits_id,
				'credits_account_id'						=> $creditaccount->credits_account_id,
				'credits_payment_date'						=> date('Y-m-d'),
				'credits_payment_amount'					=> $request->angsuran_total,
				'credits_payment_principal'					=> $angsuranpokok,
				'credits_payment_interest'					=> $angsuranbunga,
				'credits_others_income'						=> $request->others_income,
				'credits_principal_opening_balance'			=> $creditaccount->credits_account_last_balance,
				'credits_principal_last_balance'			=> $creditaccount->credits_account_last_balance - $request->angsuran_pokok,
				'credits_interest_opening_balance'			=> $creditaccount->credits_account_interest_last_balance,
				'credits_interest_last_balance'				=> $creditaccount->credits_account_interest_last_balance + $request->angsuran_bunga,				
				'credits_payment_fine'						=> $credits_payment_fine_amount,
				'credits_account_payment_date'				=> $credits_account_payment_date,
				'credits_payment_to'						=> $angsuranke,
				'credits_payment_day_of_delay'				=> $credits_payment_day_of_delay,
				'branch_id'									=> auth()->user()->branch_id,
				'created_id'								=> auth()->user()->user_id,
            );
            AcctCreditsPayment::create($data);
            //

			$credits_account_status = 0;

			if($creditaccount->payment_type_id == 4){
				if($data['credits_principal_last_balance'] <= 0){
					$credits_account_status = 1;
				}
			}else{
				if($creditaccount->credits_payment_to == $creditaccount->credits_payment_period){
					$credits_account_status = 1;
				}
			}

//---------Jurnal Header
			$transaction_module_code    = 'ANGS';
			$journal_voucher_period     = date("Ym", strtotime($data['credits_payment_date']));
			$transaction_module_id      = PreferenceTransactionModule::select('transaction_module_id')
            ->where('transaction_module_code', $transaction_module_code)
            ->first()
            ->transaction_module_id;

            $acctcreditsaccount = AcctCreditsAccount::findOrFail($data['credits_account_id']);
            $acctcreditsaccount->credits_account_last_balance           = $data['credits_principal_last_balance'];
            $acctcreditsaccount->credits_account_last_payment_date      = $data['credits_payment_date'];
            $acctcreditsaccount->credits_account_interest_last_balance  = $data['credits_interest_last_balance'];
            $acctcreditsaccount->credits_account_payment_date           = $credits_account_payment_date;
            $acctcreditsaccount->credits_account_payment_to             = $data['credits_payment_to'];
            $acctcreditsaccount->credits_account_accumulated_fines      = $credits_account_accumulated_fines;
            $acctcreditsaccount->credits_account_status                 = $credits_account_status;
            $acctcreditsaccount->save();

            if($request->member_mandatory_savings > 0 && $request->member_mandatory_savings != ''){
                $data_detail = array (
                    'member_id'						=> $data['member_id'],
                    'mutation_id'					=> 1,
                    'transaction_date'				=> date('Y-m-d'),
                    'mandatory_savings_amount'		=> $request->member_mandatory_savings,
                    'branch_id'						=> auth()->user()->branch_id,
                    'operated_name'					=> auth()->user()->username,
                );
                AcctSavingsMemberDetail::create($data_detail);
            }

            $acctcashpayment_last 				= AcctCreditsPayment::select('acct_credits_payment.credits_payment_id', 'acct_credits_payment.member_id', 'core_member.member_name', 'acct_credits_payment.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.credits_id', 'acct_credits.credits_name')
			->join('core_member','acct_credits_payment.member_id', '=', 'core_member.member_id')
			->join('acct_credits_account','acct_credits_payment.credits_account_id', '=', 'acct_credits_account.credits_account_id')
			->join('acct_credits','acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
			->where('acct_credits_payment.created_id', $data['created_id'])
			->orderBy('acct_credits_payment.credits_payment_id','DESC')
            ->first();


//---------Jurnal Header Angsuran
            $data_journal = array(
                'branch_id'						=> auth()->user()->branch_id,
                'journal_voucher_period' 		=> $journal_voucher_period,
                'journal_voucher_date'			=> date('Y-m-d'),
                'journal_voucher_title'			=> 'ANGSURAN TUNAI '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
                'journal_voucher_description'	=> 'ANGSURAN TUNAI '.$acctcashpayment_last['credits_name'].' '.$acctcashpayment_last['member_name'],
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $acctcashpayment_last['credits_payment_id'],
                'transaction_journal_no' 		=> $acctcashpayment_last['credits_account_serial'],
                'created_id' 					=> $data['created_id'],
            );
            AcctJournalVoucher::create($data_journal);

            $journal_voucher_id 				= AcctJournalVoucher::select('journal_voucher_id')
			->where('created_id', $data['created_id'])
			->orderBy('journal_voucher_id', 'DESC')
            ->first()
            ->journal_voucher_id;
//-----------Jurnal Voucher Item
            if($data['credits_others_income']!='' && $data['credits_others_income'] > 0){
                $account_id_default_status  = AcctAccount::select('account_default_status')
                ->where('account_id', $preferencecompany['account_others_income_id'])
                ->where('data_state', 0)
                ->first()
                ->account_default_status;

                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_others_income_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> $data['credits_others_income'],
                    'journal_voucher_credit_amount'	=> $data['credits_others_income'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                    'created_id' 					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
            }

            $account_id_default_status  = AcctAccount::select('account_default_status')
            ->where('account_id', $preferencecompany['account_cash_id'])
            ->where('data_state', 0)
            ->first()
            ->account_default_status;

            $data_debet = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $preferencecompany['account_cash_id'],
                'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                'journal_voucher_amount'		=> $data['credits_payment_amount'],
                'journal_voucher_debit_amount'	=> $data['credits_payment_amount'],
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 0,
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucherItem::create($data_debet);

            $receivable_account_id 				= AcctCredits::select('receivable_account_id')
            ->where('credits_id', $data['credits_id'])
            ->first()
            ->receivable_account_id;

            $account_id_default_status  = AcctAccount::select('account_default_status')
            ->where('account_id', $receivable_account_id)
            ->where('data_state', 0)
            ->first()
            ->account_default_status;

            $data_credit = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $receivable_account_id,
                'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                'journal_voucher_amount'		=> $data['credits_payment_principal'],
                'journal_voucher_credit_amount'	=> $data['credits_payment_principal'],
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 1,
                'created_id' 					=> auth()->user()->user_id
            );
            AcctJournalVoucherItem::create($data_credit);

            $account_id_default_status  = AcctAccount::select('account_default_status')
            ->where('account_id', $preferencecompany['account_interest_id'])
            ->where('data_state', 0)
            ->first()
            ->account_default_status;

            $data_credit =array(
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $preferencecompany['account_interest_id'],
                'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                'journal_voucher_amount'		=> $data['credits_payment_interest'],
                'journal_voucher_credit_amount'	=> $data['credits_payment_interest'],
                'account_id_default_status'		=> $account_id_default_status,
                'account_id_status'				=> 1,
                'created_id' 					=> auth()->user()->user_id
            );
            AcctJournalVoucherItem::create($data_credit);

            if($data['credits_payment_fine'] > 0){
                $account_id_default_status  = AcctAccount::select('account_default_status')
                ->where('account_id', $preferencecompany['account_credits_payment_fine'])
                ->where('data_state', 0)
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_credits_payment_fine'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> $data['credits_payment_fine'],
                    'journal_voucher_credit_amount'	=> $data['credits_payment_fine'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                    'created_id' 					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
            }

            if($request->member_mandatory_savings > 0 && $request->member_mandatory_savings != ''){
                $savings_id = $preferencecompany['mandatory_savings_id'];

                $account_id = AcctSavings::select('account_id')
                ->where('savings_id', $savings_id)
                ->where('data_state', 0)
                ->first()
                ->account_id;

                $account_id_default_status  = AcctAccount::select('account_default_status')
                ->where('account_id', $account_id)
                ->where('data_state', 0)
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_id,
                    'journal_voucher_description'	=> 'SETORAN TUNAI '.$acctcashpayment_last['member_name'],
                    'journal_voucher_amount'		=> $request->member_mandatory_savings,
                    'journal_voucher_credit_amount'	=> $request->member_mandatory_savings,
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                    'created_id' 					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
            }
//----------End Journal Voucher Item

            DB::commit();
            $message = array(
                'pesan' => 'Angsuran Tunai berhasil ditambah',
                'alert' => 'success'
            );
            return $message;

        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Angsuran Tunai gagal ditambah',
                'alert' => 'error'
            );
            return $message;
        }
        
    }



}
