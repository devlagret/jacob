<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\DataTables\NominativeSavingsPickupDataTable;
use App\Models\AcctSavingsCashMutation;
use App\Models\CoreMember;

class AcctNominativeSavingsPickupController extends Controller
{
    public function index(NominativeSavingsPickupDataTable $datatable) {
       $sessiondata = Session::get('pickup-data');
        return $datatable->render('content.NominativeSavings.Pickup.List.index',['sessiondata'=>$sessiondata]);
    }
    public function filter(Request $request) {
        $filter = Session::get('pickup-data');
        $filter['start_date'] = $request->start_date;
        $filter['end_date'] = $request->end_date;
        Session::put('pickup-data', $filter);
        return redirect()->route('nomv-sv-pickup.index');
    }
    public function filterReset(){
        Session::forget('pickup-data');
        return redirect()->route('nomv-sv-pickup.index');
    }
    public function add($savings_cash_mutation_id) {
        $data = AcctSavingsCashMutation::with('member','mutation')->find($savings_cash_mutation_id);
        return view('content.NominativeSavings.Pickup.Add.index',compact('data'));
    }
    public function processAdd(Request $request) {
        $mutation = AcctSavingsCashMutation::find($request->savings_cash_mutation_id);
        $mutation->pickup_remark = $request->pickup_remark;
        $mutation->pickup_status = 1;
        $mutation->process_date = Carbon::now();
        $mutation->save();
        return redirect()->route('nomv-sv-pickup.index')->with(['pesan' => 'Update Berhasil',
        'alert' => 'success']);
    }
}
