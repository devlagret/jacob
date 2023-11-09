<?php

namespace App\Http\Controllers;

use App\DataTables\AcctCreditsAccountReschedule\AcctCreditsAccountRescheduleDataTable;
use App\DataTables\AcctCreditsPaymentSuspend\AcctCreditsAccountDataTable;
use App\DataTables\AcctCreditsPaymentSuspend\AcctCreditsPaymentSuspendDataTable;
use App\Helpers\Configuration;
use App\Helpers\CreditHelper;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsAccountReschedule;
use App\Models\AcctCreditsPayment;
use App\Models\AcctCreditsPaymentSuspend;
use App\Models\PreferenceCompany;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcctCreditsAccountRescheduleController extends Controller
{
    public function index(AcctCreditsAccountRescheduleDataTable $dataTable)
    {
        session()->forget('data_creditsaccountreschedulladd');
        $sessiondata = session()->get('filter-credit-accountreschedull');

        $acctcredits = AcctCredits::select('credits_name', 'credits_id')
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctCreditsAccountReschedule.List.index'   , compact('sessiondata', 'acctcredits'));
    }

    public function filter(Request $request){
        if($request->start_date){
            $start_date = $request->start_date;
        }else{
            $start_date = date('Y-m-d');
        }

        if($request->end_date){
            $end_date = $request->end_date;
        }else{
            $end_date = date('Y-m-d');
        }

        $sessiondata = array(
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'credits_id' => $request->credits_id
        );

        session()->put('filter-credit-accountreschedull', $sessiondata);

        return redirect('credits-account-reschedule');
    }

    public function filterReset(){
        session()->forget('filter-credit-accountreschedull');

        return redirect('credits-account-reschedule');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_creditsaccountreschedulladd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['credits_id']                              = null;
            $sessiondata['start_date']                              = 0;
            $sessiondata['end_date']                                = 0;
            $sessiondata['credits_account_id']             = 0;
            $sessiondata['credits_acquittance_amount']              = 0;
            $sessiondata['penalty']                                 = 0;
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_creditsaccountreschedulladd', $sessiondata);
    }
    public function add()
    {
        $sessiondata            = session()->get('data_creditsaccountreschedulladd');
        $period=Configuration::CreditsPaymentPeriod();
        $acctcreditsaccount     = array();
        // $acctcreditspayment     = array();
        // $credits_account_interest_last_balance = 0;
        // if(isset($sessiondata['credits_account_id'])){
        //     $acctcreditsaccount = AcctCreditsAccount::with('member','credit')->find($sessiondata['credits_account_id']);

        //     $acctcreditspayment = AcctCreditsPayment::select('credits_payment_date', 'credits_payment_principal', 'credits_payment_interest', 'credits_principal_last_balance', 'credits_interest_last_balance')
        //     ->where('credits_account_id', $sessiondata['credits_account_id'])
        //     ->get();

        //     $credits_account_interest_last_balance = ($acctcreditsaccount['credits_account_interest_amount'] * $acctcreditsaccount['credits_account_period']) - ($acctcreditsaccount['credits_account_payment_to'] * $acctcreditsaccount['credits_account_interest_amount']);
        // }

        // dd($credits_account_interest_last_balance);
        return view('content.AcctCreditsAccountReschedule.Add.index', compact('sessiondata', 'period', 'acctcreditsaccount'));
    }

}
