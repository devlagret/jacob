<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctAccount;
use App\Models\AcctBankAccount;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPayment;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavings;
use App\Models\AcctSavingsMemberDetail;
use App\Models\CoreBranch;
use App\Models\CoreMember;
use App\Models\AcctMutation;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\DataTables\AcctCreditsPaymentBank\AcctCreditsPaymentBankDataTable;
use App\DataTables\AcctCreditsPaymentBank\AcctCreditsAccountDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctCreditsPaymentBankController extends Controller
{
    public function index(AcctCreditsPaymentBankDataTable $dataTable)
    {
        session()->forget('data_creditspaymentbankadd');
        $sessiondata = session()->get('filter_creditspaymentbank');

        $acctcredits = AcctCredits::select('credits_name', 'credits_id')
        ->where('data_state', 0)
        ->get();

        $corebranch = CoreBranch::select('branch_name', 'branch_id')
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctCreditsPaymentBank.List.index', compact('sessiondata', 'acctcredits', 'corebranch'));
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

        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = null;
        }

        $sessiondata = array(
            'start_date'    => $start_date,
            'end_date'      => $end_date,
            'branch_id'     => $branch_id,
            'credits_id'    => $request->credits_id
        );

        session()->put('filter_creditspaymentbank', $sessiondata);

        return redirect('credits-payment-bank');
    }

    public function filterReset(){
        session()->forget('filter_creditspaymentbank');

        return redirect('credits-payment-bank');
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_creditspaymentbankadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['bank_account_id']             = null;
            $sessiondata['credits_payment_fine']        = 0;
            $sessiondata['others_income']               = 0;
            $sessiondata['member_mandatory_savings']    = 0;
            $sessiondata['angsuran_total']              = 0;
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_creditspaymentbankadd', $sessiondata);
    }

    public function add()
    {
        $config                 = theme()->getOption('page', 'view');
        $sessiondata            = session()->get('data_creditspaymentbankadd');

        $acctbankaccount        = AcctBankAccount::select('bank_account_name', 'bank_account_id')
        ->where('data_state', 0)
        ->get();

        $acctcreditsaccount     = array();
        $acctcreditspayment     = array();
        if(isset($sessiondata['credits_account_id'])){
            $acctcreditsaccount = AcctCreditsAccount::select('acct_credits_account.*', 'acct_credits.credits_name', 'core_member.member_no', 'core_member.member_name', 'core_member.member_address')
            ->join('acct_credits', 'acct_credits.credits_id', '=', 'acct_credits_account.credits_id')
            ->join('core_member', 'core_member.member_id', '=', 'acct_credits_account.member_id')
            ->where('acct_credits_account.credits_account_id', $sessiondata['credits_account_id'])
            ->first();

            $acctcreditspayment = AcctCreditsPayment::select('credits_payment_date', 'credits_payment_principal', 'credits_payment_interest', 'credits_principal_last_balance', 'credits_interest_last_balance')
            ->where('credits_account_id', $sessiondata['credits_account_id'])
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
        }else{
            $credits_payment_day_of_delay       = 0;
            $credits_account_accumulated_fines  = 0;
            $credits_payment_fine_amount        = 0;
            $angsuranpokok                      = 0;
            $angsuranbunga                      = 0;
            $angsuranke                         = 0;
        }

        return view('content.AcctCreditsPaymentBank.Add.index', compact('sessiondata', 'acctbankaccount', 'acctcreditsaccount', 'acctcreditspayment', 'angsuranke', 'angsuranpokok', 'angsuranbunga', 'credits_payment_fine_amount', 'credits_account_accumulated_fines', 'credits_payment_day_of_delay'));
    }

    public function modalAcctCreditsAccount(AcctCreditsAccountDataTable $dataTable)
    {
        return $dataTable->render('content.AcctCreditsPaymentBank.Add.AcctCreditsAccountModal.index');
    }

    public function selectAcctCreditsAccount($credits_account_id)
    {
        session()->forget('data_creditspaymentbankadd');
        $sessiondata = session()->get('data_creditspaymentbankadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['bank_account_id']             = null;
            $sessiondata['credits_payment_fine']        = 0;
            $sessiondata['others_income']               = 0;
            $sessiondata['member_mandatory_savings']    = 0;
            $sessiondata['angsuran_total']              = 0;
        }
        $sessiondata['credits_account_id'] = $credits_account_id;
        session()->put('data_creditspaymentbankadd', $sessiondata);

        return redirect('credits-payment-bank/add');
    }

    public function processAdd(Request $request)
    {
        $preferencecompany = PreferenceCompany::first();

        $fields = request()->validate([
            'credits_account_id'    => ['required'],
            'bank_account_id'       => ['required'],
        ]);
        
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
                'member_id'									=> $request->member_id,
				'credits_id'								=> $request->credits_id,
				'credits_account_id'						=> $fields['credits_account_id'],
				'bank_account_id'						    => $fields['bank_account_id'],
				'credits_payment_date'						=> date('Y-m-d'),
				'credits_payment_amount'					=> $request->angsuran_total,
				'credits_payment_principal'					=> $request->angsuran_pokok,
				'credits_payment_interest'					=> $request->angsuran_bunga,
				'credits_others_income'						=> $request->others_income,
				'credits_principal_opening_balance'			=> $request->sisa_pokok,
				'credits_principal_last_balance'			=> $request->sisa_pokok - $request->angsuran_pokok,
				'credits_interest_opening_balance'			=> $request->sisa_bunga,
				'credits_interest_last_balance'				=> $request->sisa_bunga + $request->angsuran_bunga,				
				'credits_payment_fine'						=> $request->credits_payment_fine,
				'credits_account_payment_date'				=> $credits_account_payment_date,
				'credits_payment_to'						=> $request->credits_payment_to,
				'credits_payment_day_of_delay'				=> $request->credits_payment_day_of_delay,
				'branch_id'									=> auth()->user()->branch_id,
				'created_id'								=> auth()->user()->user_id,
            );
            AcctCreditsPayment::create($data);

			$credits_account_status = 0;

			if($request->payment_type_id == 4){
				if($data['credits_principal_last_balance'] <= 0){
					$credits_account_status = 1;
				}
			}else{
				if($request->credits_payment_to == $request->credits_account_period){
					$credits_account_status = 1;
				}
			}

			$transaction_module_code    = 'ANGS';
			$journal_voucher_period     = date("Ym", strtotime($data['credits_payment_date']));
			$transaction_module_id      = PreferenceTransactionModule::select('transaction_module_id')
            ->where('transaction_module_code', $transaction_module_code)
            ->first()
            ->transaction_module_id;
            
			$account_bank_id 					= AcctbankAccount::select('account_id')
            ->where('bank_account_id', $data['bank_account_id'])
            ->first()
            ->account_id;

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

            $acctbankpayment_last 				= AcctCreditsPayment::select('acct_credits_payment.credits_payment_id', 'acct_credits_payment.member_id', 'core_member.member_name', 'acct_credits_payment.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.credits_id', 'acct_credits.credits_name')
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
                'journal_voucher_title'			=> 'ANGSURAN BANK '.$acctbankpayment_last['credits_name'].' '.$acctbankpayment_last['member_name'],
                'journal_voucher_description'	=> 'ANGSURAN BANK '.$acctbankpayment_last['credits_name'].' '.$acctbankpayment_last['member_name'],
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $acctbankpayment_last['credits_payment_id'],
                'transaction_journal_no' 		=> $acctbankpayment_last['credits_account_serial'],
                'created_id' 					=> $data['created_id'],
            );
            AcctJournalVoucher::create($data_journal);

            $journal_voucher_id 				= AcctJournalVoucher::select('journal_voucher_id')
			->where('created_id', $data['created_id'])
			->orderBy('journal_voucher_id', 'DESC')
            ->first()
            ->journal_voucher_id;

            if($data['credits_others_income']!='' && $data['credits_others_income'] > 0){
                $account_id_default_status 			= AcctAccount::select('account_default_status')
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

            $account_id_default_status 			= AcctAccount::select('account_default_status')
            ->where('account_id', $account_bank_id)
            ->where('data_state', 0)
            ->first()
            ->account_default_status;

            $data_debet = array (
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $account_bank_id,
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

            $account_id_default_status 			= AcctAccount::select('account_default_status')
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
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucherItem::create($data_credit);

            $account_id_default_status 			= AcctAccount::select('account_default_status')
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
                'created_id' 					=> auth()->user()->user_id,
            );
            AcctJournalVoucherItem::create($data_credit);

            if($data['credits_payment_fine'] > 0){
                $account_id_default_status 			= AcctAccount::select('account_default_status')
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

            if(!empty($request->member_mandatory_savings) || $request->member_mandatory_savings > 0 || $request->member_mandatory_savings != ''){
                $savings_id = $preferencecompany['mandatory_savings_id'];

                $account_id = AcctSavings::select('account_id')
                ->where('acct_savings.savings_id', $savings_id)
                ->where('acct_savings.data_state', 0)
                ->first()
                ->account_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $account_id)
                ->where('data_state', 0)
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_id,
                    'journal_voucher_description'	=> 'SETORAN BANK '.$acctbankpayment_last['member_name'],
                    'journal_voucher_amount'		=> $request->member_mandatory_savings,
                    'journal_voucher_credit_amount'	=> $request->member_mandatory_savings,
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                    'created_id' 					=> auth()->user()->user_id,
                );
                AcctJournalVoucherItem::create($data_credit);
            }

            DB::commit();
            $message = array(
                'pesan' => 'Angsuran Via Bank berhasil ditambah',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Angsuran Via Bank gagal ditambah',
                'alert' => 'error'
            );
        }
        
        return redirect('credits-payment-bank')->with($message);
    }

    public function printNote($credits_payment_id){
        $preferencecompany	= PreferenceCompany::first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);

        $branch_name        = CoreBranch::select('branch_name')
        ->where('branch_id', auth()->user()->branch_id)
        ->first()
        ->branch_name;

        $acctcreditspayment	= AcctCreditsPayment::select('acct_credits_payment.credits_payment_id', 'acct_credits_payment.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_credits_payment.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.credits_id', 'acct_credits.credits_name', 'acct_credits_payment.credits_payment_to', 'acct_credits_payment.credits_payment_amount', 'acct_credits_payment.savings_account_id', 'acct_bank_account.bank_account_name')
        ->join('core_member','acct_credits_payment.member_id', '=', 'core_member.member_id')
        ->join('acct_credits_account','acct_credits_payment.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->join('acct_bank_account','acct_credits_payment.bank_account_id', '=', 'acct_bank_account.bank_account_id')
        ->join('acct_credits','acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
        ->where('acct_credits_payment.credits_payment_id', $credits_payment_id)
        ->first();

        $branch_city        = CoreBranch::select('branch_city')
        ->where('branch_id', auth()->user()->branch_id)
        ->first()
        ->branch_city;

        $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 6, 6, 6);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage('P');

        $pdf::SetFont('helvetica', '', 10);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $export = "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"25%\"></td>
                <td width=\"75%\"><div style=\"text-align: left; font-size:14px\">BUKTI SETORAN ANGSURAN VIA BANK</div></td>
            </tr>
            <tr>
                <td width=\"25%\"></td>
                <td width=\"75%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
            </tr>
        </table>
        <br>
        <br>
        <br>";

        $keperluan = ": ANGSURAN PEMBIAYAAN KE ".$acctcreditspayment['credits_payment_to'];

        $export .= "
        Telah diterima dari :
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['member_name']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">No. Akad</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['credits_account_serial']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Bank Transfer</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['bank_account_name']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditspayment['member_address']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: ".Configuration::numtotxt($acctcreditspayment['credits_payment_amount'])."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Keterangan</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">".$keperluan."</div></td>
            </tr>
             <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctcreditspayment['credits_payment_amount'], 2)."</div></td>
            </tr>				
        </table>";

        $export .= "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"10%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">".$branch_city."</div></td>
            </tr>
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\">Penyetor</div></td>
                <td width=\"10%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>               
            </tr>				
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Kwitansi Angsuran Via Bank.pdf';
        $pdf::Output($filename, 'I');
    }
}
