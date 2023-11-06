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
use Illuminate\Http\Request;
use Str;

class ApiController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function login(Request $request)
    {
        $login = Auth::Attempt($request->all());
        if ($login) {
            $user = Auth::user();
            // $user->api_token = Str::random(100);
            $user->save();
            // $user->makeVisible('api_token');

            return response()->json([
                'response_code' => 200,
                'message' => 'Login Berhasil',
                'conntent' => $user
            ]);
        }else{
            return response()->json([
                'response_code' => 404,
                'message' => 'Username atau Password Tidak Ditemukan!'
            ]);
        }
    }

    public function getDataSavings(){
        $data = AcctSavingsAccount::get();

        return json_encode($data);
    }

    public function getDataDeposito(){
        $data = AcctDepositoAccount::get();

        return json_encode($data);
    }

    public function getDataCredit(){
        $data = AcctCreditsAccount::get();

        return json_encode($data);
    }
}
