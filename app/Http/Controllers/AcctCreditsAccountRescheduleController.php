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

        session()->put('filter-credit-p-suspend', $sessiondata);

        return redirect('credits-payment-suspend');
    }

    public function filterReset(){
        session()->forget('filter_creditspaymentsuspend');

        return redirect('credits-payment-suspend');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_creditspaymentsuspendadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['penalty_type_id']                         = null;
            $sessiondata['credits_acquittance_interest']            = 0;
            $sessiondata['credits_acquittance_fine']                = 0;
            $sessiondata['credits_acquittance_penalty']             = 0;
            $sessiondata['credits_acquittance_amount']              = 0;
            $sessiondata['penalty']                                 = 0;
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_creditspaymentsuspendadd', $sessiondata);
    }

}
