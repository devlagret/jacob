<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\DataTables\NominativeSavingsPickupDataTable;
use App\Models\AcctAccount;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPayment;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavings;
use App\Models\AcctSavingsCashMutation;
use App\Models\AcctSavingsMemberDetail;
use App\Models\CoreMember;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;

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
                credits_payment_fine As jumlah_5,
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
                0 As jumlah_5,
                CONCAT("Setoran Tunai ",savings_name) As keterangan')
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
                CONCAT("Tarik Tunai ",savings_name) As keterangan')
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
                CONCAT("Setor Tunai Simpanan Wajib ") As keterangan')
            ->withoutGlobalScopes()
            ->join('system_user','system_user.user_id', '=', 'core_member.created_id')
            ->where('member_id', $id)->first();
        }

        // dd($data);
        return view('content.NominativeSavings.Pickup.Add.index',compact('data','type'));
    }


    public function processAdd(Request $request) {


        // dd($request->all());

//------Angsuran
        if($request->type == 1){


            $creditspayment = AcctCreditsPayment::select('*')
            ->where('credits_payment_id', $request->id)
            ->first();
            // dd($creditspayment);

            //---------Cek id pinjaman
            $acctcreditsaccount = AcctCreditsAccount::with('credit','member')->find($creditspayment->credits_account_id);

            // dd($acctcreditsaccount);

            $acctcreditspayment = AcctCreditsPayment::select('credits_payment_date', 'credits_payment_principal', 'credits_payment_interest', 'credits_principal_last_balance', 'credits_interest_last_balance')
            ->where('credits_account_id', $request->id)
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

            $credits_payment_fine_amount 		= (($acctcreditsaccount['credits_account_payment_amount'] * $acctcreditsaccount['credit']['credits_fine']) / 100 ) * $credits_payment_day_of_delay;
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
        

        $creditaccount = AcctCreditsAccount::where('credits_account_id',$creditspayment->credits_account_id)
        ->first();

        $preferencecompany = PreferenceCompany::first();

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

            $data  = array(
                'member_id'									=> $creditaccount->member_id,
				'credits_id'								=> $creditaccount->credits_id,
				'credits_account_id'						=> $creditaccount->credits_account_id,
				'credits_payment_date'						=> Carbon::now(),
				'credits_payment_amount'					=> $request->jumlah,
				'credits_payment_principal'					=> $request->jumlah_2,
				'credits_payment_interest'					=> $request->jumlah_3,
				'credits_others_income'						=> $request->jumlah_4,
				'credits_principal_opening_balance'			=> $creditaccount->credits_account_last_balance,
				'credits_principal_last_balance'			=> $creditaccount->credits_account_last_balance - $request->angsuran_pokok,
				'credits_interest_opening_balance'			=> $creditaccount->credits_account_interest_last_balance,
				'credits_interest_last_balance'				=> $creditaccount->credits_account_interest_last_balance + $request->angsuran_bunga,				
				'credits_payment_fine'						=> $request->jumlah_5,
				'credits_account_payment_date'				=> $credits_account_payment_date,
				'credits_payment_to'						=> $angsuranke,
				'credits_payment_day_of_delay'				=> $credits_payment_day_of_delay,
				'branch_id'									=> auth()->user()->branch_id,
				'created_id'								=> auth()->user()->user_id,
				'pickup_state'								=> 0,
				'pickup_date'								=> date('Y-m-d'),

            );
            // dd($data);
            // AcctCreditsPayment::create($data);
            

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
            $acctcreditsaccount->credits_account_accumulated_fines      = $request->credits_account_accumulated_fines;
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

            // if($request->member_mandatory_savings > 0 && $request->member_mandatory_savings != ''){
            //     $savings_id = $preferencecompany['mandatory_savings_id'];

            //     $account_id = AcctSavings::select('account_id')
            //     ->where('savings_id', $savings_id)
            //     ->where('data_state', 0)
            //     ->first()
            //     ->account_id;

            //     $account_id_default_status  = AcctAccount::select('account_default_status')
            //     ->where('account_id', $account_id)
            //     ->where('data_state', 0)
            //     ->first()
            //     ->account_default_status;

            //     $data_credit =array(
            //         'journal_voucher_id'			=> $journal_voucher_id,
            //         'account_id'					=> $account_id,
            //         'journal_voucher_description'	=> 'SETORAN TUNAI '.$acctcashpayment_last['member_name'],
            //         'journal_voucher_amount'		=> $request->member_mandatory_savings,
            //         'journal_voucher_credit_amount'	=> $request->member_mandatory_savings,
            //         'account_id_default_status'		=> $account_id_default_status,
            //         'account_id_status'				=> 1,
            //         'created_id' 					=> auth()->user()->user_id,
            //     );
            //     AcctJournalVoucherItem::create($data_credit);
            // }

            AcctCreditsPayment::where('credits_payment_id',$request->id)
                                ->update(['pickup_state'=> 1,
                                          'pickup_date'=> Carbon::now()]);




        }else
        if($request->type == 2){
            
        }else
        if($request->type == 3){
            
        }else
        if($request->type == 4){
            
        }
       


        // $mutation = AcctSavingsCashMutation::find($request->savings_cash_mutation_id);
        // $mutation->pickup_remark = $request->pickup_remark;
        // $mutation->pickup_status = 1;
        // $mutation->process_date = Carbon::now();
        // $mutation->save();
        return redirect()->route('nomv-sv-pickup.index')->with(['pesan' => 'Update Berhasil',
        'alert' => 'success']);
    }
}
