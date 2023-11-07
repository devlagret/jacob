<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsAcquittance;
use App\Models\AcctCreditsPayment;
use App\Models\CoreBranch;
use App\Models\CoreMember;
use App\Models\AcctMutation;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\DataTables\AcctCreditsAcquittance\AcctCreditsAcquittanceDataTable;
use App\DataTables\AcctCreditsAcquittance\AcctCreditsAccountDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctCreditsPaymentSuspendController extends Controller
{
    public function index(AcctCreditsAcquittanceDataTable $dataTable)
    {
        session()->forget('data_creditsacquittanceadd');
        $sessiondata = session()->get('filter_creditsacquittance');

        $acctcredits = AcctCredits::select('credits_name', 'credits_id')
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctCreditsAcquittance.List.index', compact('sessiondata', 'acctcredits'));
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

        session()->put('filter_creditsacquittance', $sessiondata);

        return redirect('credits-acquittance');
    }

    public function filterReset(){
        session()->forget('filter_creditsacquittance');

        return redirect('credits-acquittance');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_creditsacquittanceadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['penalty_type_id']                         = null;
            $sessiondata['credits_acquittance_interest']            = 0;
            $sessiondata['credits_acquittance_fine']                = 0;
            $sessiondata['credits_acquittance_penalty']             = 0;
            $sessiondata['credits_acquittance_amount']              = 0;
            $sessiondata['penalty']                                 = 0;
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_creditsacquittanceadd', $sessiondata);
    }

    public function add()
    {
        $config                 = theme()->getOption('page', 'view');
        $sessiondata            = session()->get('data_creditsacquittanceadd');
        $penaltytype            = array_filter(Configuration::PenaltyType());

        $acctcreditsaccount     = array();
        $acctcreditspayment     = array();
        $credits_account_interest_last_balance = 0; 
        if(isset($sessiondata['credits_account_id'])){
            $acctcreditsaccount = AcctCreditsAccount::with('member','credit')->find($sessiondata['credits_account_id']);

            $acctcreditspayment = AcctCreditsPayment::select('credits_payment_date', 'credits_payment_principal', 'credits_payment_interest', 'credits_principal_last_balance', 'credits_interest_last_balance')
            ->where('credits_account_id', $sessiondata['credits_account_id'])
            ->get();

            $credits_account_interest_last_balance = ($acctcreditsaccount['credits_account_interest_amount'] * $acctcreditsaccount['credits_account_period']) - ($acctcreditsaccount['credits_account_payment_to'] * $acctcreditsaccount['credits_account_interest_amount']);
        }

        // dd($credits_account_interest_last_balance);
        return view('content.AcctCreditsAcquittance.Add.index', compact('sessiondata', 'penaltytype', 'acctcreditsaccount', 'acctcreditspayment','credits_account_interest_last_balance'));
    }

    public function modalAcctCreditsAccount(AcctCreditsAccountDataTable $dataTable)
    {
        return $dataTable->render('content.AcctCreditsAcquittance.Add.AcctCreditsAccountModal.index');
    }

    public function selectAcctCreditsAccount($credits_account_id)
    {
        $sessiondata = session()->get('data_creditsacquittanceadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['penalty_type_id']                         = null;
            $sessiondata['credits_acquittance_interest']            = 0;
            $sessiondata['credits_acquittance_fine']                = 0;
            $sessiondata['credits_acquittance_penalty']             = 0;
            $sessiondata['credits_acquittance_amount']              = 0;
            $sessiondata['penalty']                                 = 0;
        }
        $sessiondata['credits_account_id'] = $credits_account_id;
        session()->put('data_creditsacquittanceadd', $sessiondata);

        return redirect('credits-acquittance/add');
    }

    
}