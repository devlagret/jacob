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
use App\Models\CloseCashierLog;
use App\Models\CoreEmployee;
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
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
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
        $data = AcctSavingsAccount::with('member')
        ->get();

        return json_encode($data);
    }

    //data simpanan berjangka
    public function getDataDeposito(){
        $data = AcctDepositoAccount::with('member')
        ->get();

        return json_encode($data);
    }

    //pinjaman
    public function getDataCredit(){
        $data = AcctCreditsAccount::with('member')
        ->get();

        return json_encode($data);
    }
}
