<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\DataTables\NominativeSavingsPickupDataTable;
use App\Models\AcctCreditsPayment;
use App\Models\AcctSavingsCashMutation;
use App\Models\CoreMember;

class AcctNominativeSavingsPickupController extends Controller
{
    public function index(NominativeSavingsPickupDataTable $datatable) {

       $sessiondata = Session::get('pickup-data');
       
    //    dd($sessiondata);
       return $datatable->render('content.NominativeSavings.Pickup.List.index',['sessiondata'=>$sessiondata]);
    }
    public function filter(Request $request) {
        $filter = Session::get('pickup-data');
        $filter['start_date'] = $request->start_date;
        $filter['end_date'] = $request->end_date;
        $filter['pickup_type'] = $request->pickup_type;
        Session::put('pickup-data', $filter);
        return redirect()->route('nomv-sv-pickup.index');
    }

    public function filterReset(){
        Session::forget('pickup-data');
        return redirect()->route('nomv-sv-pickup.index');
    }

    public function add($type,$id) {

//------Angsuran
        if($type == 1){
            $data = AcctCreditsPayment::selectRaw(
                '1 As type,
                credits_payment_id As id,
                credits_payment_date As tanggal,
                username As operator,
                member_name As anggota,
                credits_account_serial As no_transaksi,
                credits_payment_amount As jumlah,
                credits_payment_principal As jumlah_2,
                credits_payment_interest As jumlah_3,
                credits_others_income As jumlah_4,
                credits_payment_fine As jumlah_5
                CONCAT("Angsuran ",credits_name) As keterangan')
        
                ->join('core_member','acct_credits_payment.member_id', '=', 'core_member.member_id')			
                ->join('acct_credits','acct_credits_payment.credits_id', '=', 'acct_credits.credits_id')
                ->join('system_user','system_user.user_id', '=', 'acct_credits_payment.created_id')
                ->join('acct_credits_account','acct_credits_payment.credits_account_id', '=', 'acct_credits_account.credits_account_id')
                ->where('credits_payment_id', $id)->first();
        }
//------Setoran Tunai Simpanan
        else if($type == 2){
            $data = AcctSavingsCashMutation::selectRaw(
                '2 As type,
                savings_cash_mutation_id As id,
                savings_cash_mutation_date As tanggal,
                username As operator,
                member_name As anggota,
                savings_account_no As no_transaksi,
                savings_cash_mutation_amount As jumlah,
                savings_cash_mutation_amount_adm As jumlah_2,
                0 As jumlah_3,
                0 As jumlah_4,
                0 As jumlah_5
                CONCAT("Setoran Tunai ",savings_name) As keterangan'
            )
            ->withoutGlobalScopes()
            ->join('system_user','system_user.user_id', '=', 'acct_savings_cash_mutation.created_id')
            ->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id', '=', 'acct_mutation.mutation_id')
            ->join('acct_savings_account', 'acct_savings_cash_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
            ->join('core_member', 'acct_savings_cash_mutation.member_id', '=', 'core_member.member_id')
            ->join('acct_savings', 'acct_savings_cash_mutation.savings_id', '=', 'acct_savings.savings_id')
            ->where('savings_cash_mutation_id', $id)->first();
        }
//------Tarik Tunai Simpanan
        else if($type == 3){
            $data = AcctSavingsCashMutation::selectRaw(
                '3 As type,
                savings_cash_mutation_id As id,
                savings_cash_mutation_date As tanggal,
                username As operator,
                member_name As anggota,
                savings_account_no As no_transaksi,
                savings_cash_mutation_amount As jumlah,
                savings_cash_mutation_amount_adm As jumlah_2,
                0 As jumlah_3,
                0 As jumlah_4,
                0 As jumlah_5,
                CONCAT("Tarik Tunai ",savings_name) As keterangan'
            )
            ->withoutGlobalScopes()
            ->join('system_user','system_user.user_id', '=', 'acct_savings_cash_mutation.created_id')
            ->join('acct_mutation', 'acct_savings_cash_mutation.mutation_id', '=', 'acct_mutation.mutation_id')
            ->join('acct_savings_account', 'acct_savings_cash_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
            ->join('core_member', 'acct_savings_cash_mutation.member_id', '=', 'core_member.member_id')
            ->join('acct_savings', 'acct_savings_cash_mutation.savings_id', '=', 'acct_savings.savings_id')
            ->where('savings_cash_mutation_id', $id)->first();
        }
//------Setoran Tunai Simpanan Wajib
        else if($type == 4){
            $data = CoreMember::selectRaw(
                '4 As type,
                member_id As id,
                core_member.updated_at As tanggal,
                username As operator,
                member_name As anggota,
                member_no As no_transaksi,
                member_mandatory_savings As jumlah,
                member_mandatory_savings_last_balance As jumlah_2,
                0 As jumlah_3,
                0 As jumlah_4,
                0 As jumlah_5,
                CONCAT("Setor Tunai Simpanan Wajib ") As keterangan'
            )
            ->withoutGlobalScopes()
            ->join('system_user','system_user.user_id', '=', 'core_member.created_id')
            ->where('member_id', $id)->first();
        }

        // dd($data);
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
