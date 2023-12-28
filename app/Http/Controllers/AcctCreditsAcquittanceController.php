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

class AcctCreditsAcquittanceController extends Controller
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

    public function processAdd(Request $request)
    {
        $preferencecompany = PreferenceCompany::first();

        $fields = request()->validate([
            'credits_account_id' => ['required'],
        ]);

        // notif jumlah pelunasan 
        if($request->credits_account_last_balance < $request->credits_acquittance_principal)
            {
                $message = array(
                    'pesan' => 'Jumlah pelunasan tidak boleh kurang atau melebihi sisa pokok!',
                    'alert' => 'error'
                );
                return redirect('credits-acquittance/add')->with($message);
            }else if($request->credits_account_last_balance > $request->credits_acquittance_principal)
            {
                $message = array(
                    'pesan' => 'Jumlah pelunasan tidak boleh kurang atau melebihi sisa pokok!',
                    'alert' => 'error'
                );
                return redirect('credits-acquittance/add')->with($message);
            }else{
                DB::beginTransaction();

            try {
                $data  = array(
                    'credits_account_id'                        => $fields['credits_account_id'],
                    'member_id'                                 => $request->member_id,
                    'credits_id'                                => $request->credits_id,
                    'credits_acquittance_date'                  => date('Y-m-d'),
                    'credits_acquittance_penalty_type'          => $request->penalty_type_id,
                    'credits_account_last_balance'              => $request->credits_account_last_balance,
                    'credits_account_interest_last_balance'     => $request->credits_account_interest_last_balance,
                    'credits_account_accumulated_fines'         => $request->credits_account_accumulated_fines,
                    'credits_acquittance_amount'                => $request->credits_acquittance_amount,
                    'credits_acquittance_principal'             => $request->credits_acquittance_principal,
                    'credits_acquittance_interest'              => $request->credits_acquittance_interest,
                    'credits_acquittance_fine'                  => $request->credits_acquittance_fine,
                    'credits_acquittance_penalty'               => $request->penalty,
                    'credits_acquittance_penalty_amount'        => $request->credits_acquittance_penalty,
                    'created_id'                                => auth()->user()->user_id,
                    'branch_id'                                 => auth()->user()->branch_id,
                );
                // dd($data);
                AcctCreditsAcquittance::create($data);
                
                $kerugian_pelunasan_peminjaman = $data['credits_account_last_balance'] - $data['credits_acquittance_principal'];

                $journal_voucher_period 	= date("Ym", strtotime($data['credits_acquittance_date']));
                $transaction_module_code 	= 'PP';
                $transaction_module_id 		= PreferenceTransactionModule::select('transaction_module_id')
                ->where('transaction_module_code', $transaction_module_code)
                ->first()
                ->transaction_module_id;

                $acctcreditsaccount = AcctCreditsAccount::findOrFail($data['credits_account_id']);
                $acctcreditsaccount->credits_account_last_balance       = $data['credits_account_last_balance'] - $data['credits_acquittance_principal'];
                $acctcreditsaccount->credits_account_accumulated_fines  = $data['credits_account_accumulated_fines'] - $data['credits_acquittance_fine'];
                $acctcreditsaccount->credits_account_status             = 2;
                $acctcreditsaccount->save();

                $acctcashacquittance_last   = AcctCreditsAcquittance::select('acct_credits_acquittance.credits_acquittance_id', 'acct_credits_acquittance.member_id', 'core_member.member_name', 'acct_credits_acquittance.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.credits_id', 'acct_credits.credits_name')
                ->join('core_member','acct_credits_acquittance.member_id', '=', 'core_member.member_id')
                ->join('acct_credits_account','acct_credits_acquittance.credits_account_id', '=', 'acct_credits_account.credits_account_id')
                ->join('acct_credits','acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
                ->where('acct_credits_acquittance.created_id', $data['created_id'])
                ->orderBy('acct_credits_acquittance.credits_acquittance_id', 'DESC')
                ->first();
                
                $data_journal = array(
                    'branch_id'						=> auth()->user()->branch_id,
                    'journal_voucher_period' 		=> $journal_voucher_period,
                    'journal_voucher_date'			=> date('Y-m-d'),
                    'journal_voucher_title'			=> 'PELUNASAN PEMINJAMAN '.$acctcashacquittance_last['credits_name'].' '.$acctcashacquittance_last['member_name'],
                    'journal_voucher_description'	=> 'PELUNASAN PEMINJAMAN '.$acctcashacquittance_last['credits_name'].' '.$acctcashacquittance_last['member_name'],
                    'transaction_module_id'			=> $transaction_module_id,
                    'transaction_module_code'		=> $transaction_module_code,
                    'transaction_journal_id' 		=> $acctcashacquittance_last['credits_acquittance_id'],
                    'transaction_journal_no' 		=> $acctcashacquittance_last['credits_account_serial'],
                    'created_id' 					=> $data['created_id'],
                );
                AcctJournalVoucher::create($data_journal);

                $journal_voucher_id 				= AcctJournalVoucher::select('journal_voucher_id')
                ->where('created_id', $data['created_id'])
                ->orderBy('journal_voucher_id', 'DESC')
                ->first()
                ->journal_voucher_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $preferencecompany['account_cash_id'])
                ->first()
                ->account_default_status;

                $data_debet = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $preferencecompany['account_cash_id'],
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> $data['credits_acquittance_amount'],
                    'journal_voucher_debit_amount'	=> $data['credits_acquittance_amount'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 0,
                    'created_id' 					=> auth()->user()->user_id
                );
                AcctJournalVoucherItem::create($data_debet);

                $receivable_account_id 				= AcctCredits::select('receivable_account_id')
                ->where('credits_id', $data['credits_id'])
                ->first()
                ->receivable_account_id;

                $account_id_default_status 			= AcctAccount::select('account_default_status')
                ->where('account_id', $receivable_account_id)
                ->first()
                ->account_default_status;

                $data_credit = array (
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $receivable_account_id,
                    'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                    'journal_voucher_amount'		=> $data['credits_acquittance_principal'],
                    'journal_voucher_credit_amount'	=> $data['credits_acquittance_principal'],
                    'account_id_default_status'		=> $account_id_default_status,
                    'account_id_status'				=> 1,
                    'created_id' 					=> auth()->user()->user_id
                );
                AcctJournalVoucherItem::create($data_credit);

                if($data['credits_acquittance_interest'] > 0){

                    $account_id_default_status 			= AcctAccount::select('account_default_status')
                    ->where('account_id', $preferencecompany['account_interest_id'])
                    ->first()
                    ->account_default_status;

                    $data_credit =array(
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $preferencecompany['account_interest_id'],
                        'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                        'journal_voucher_amount'		=> $data['credits_acquittance_interest'],
                        'journal_voucher_credit_amount'	=> $data['credits_acquittance_interest'],
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 1,
                        'created_id' 					=> auth()->user()->user_id,
                    );
                    AcctJournalVoucherItem::create($data_credit);
                }

                if($data['credits_acquittance_fine'] > 0){
                    $account_id_default_status 			= AcctAccount::select('account_default_status')
                    ->where('account_id', $preferencecompany['account_credits_payment_fine'])
                    ->first()
                    ->account_default_status;

                    $data_credit =array(
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $preferencecompany['account_credits_payment_fine'],
                        'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                        'journal_voucher_amount'		=> $data['credits_acquittance_fine'],
                        'journal_voucher_credit_amount'	=> $data['credits_acquittance_fine'],
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 1,
                        'created_id' 					=> auth()->user()->user_id,
                    );
                    AcctJournalVoucherItem::create($data_credit);
                }

                if(!empty($data['credits_acquittance_penalty_amount']) || $data['credits_acquittance_penalty_amount'] > 0){

                    $account_id_default_status 			= AcctAccount::select('account_default_status')
                    ->where('account_id', $preferencecompany['account_penalty_id'])
                    ->first()
                    ->account_default_status;

                    $data_credit =array(
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $preferencecompany['account_penalty_id'],
                        'journal_voucher_description'	=> $data_journal['journal_voucher_title'],
                        'journal_voucher_amount'		=> $data['credits_acquittance_penalty_amount'],
                        'journal_voucher_credit_amount'	=> $data['credits_acquittance_penalty_amount'],
                        'account_id_status'				=> 1,
                        'created_id' 					=> auth()->user()->user_id,
                    );
                    AcctJournalVoucherItem::create($data_credit);
                }

                DB::commit();
                $message = array(
                    'pesan' => 'Pelunasan Pinjaman berhasil ditambah',
                    'alert' => 'success'
                );
            } catch (\Exception $e) {
                DB::rollback();
                $message = array(
                    'pesan' => 'Pelunasan Pinjaman gagal ditambah',
                    'alert' => 'error'
                );
            }
            
            return redirect('credits-acquittance')->with($message);
            }

        
    }

    public function printNote($credits_acquittance_id){
        $preferencecompany	= PreferenceCompany::first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);

        $branch_name        = CoreBranch::select('branch_name')
        ->where('branch_id', auth()->user()->branch_id)
        ->first()
        ->branch_name;

        $acctcreditsacquittance	 	= AcctCreditsAcquittance::select('acct_credits_acquittance.credits_acquittance_id', 'acct_credits_acquittance.member_id', 'core_member.member_name', 'core_member.member_address', 'acct_credits_acquittance.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.credits_id', 'acct_credits.credits_name', 'acct_credits_acquittance.credits_acquittance_amount')
        ->join('core_member','acct_credits_acquittance.member_id', '=', 'core_member.member_id')
        ->join('acct_credits_account','acct_credits_acquittance.credits_account_id', '=', 'acct_credits_account.credits_account_id')
        ->join('acct_credits','acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
        ->where('acct_credits_acquittance.credits_acquittance_id', $credits_acquittance_id)
        ->first();

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
                <td width=\"75%\"><div style=\"text-align: left; font-size:14px\">BUKTI PELUNASAN PINJAMAN</div></td>
            </tr>
            <tr>
                <td width=\"25%\"></td>
                <td width=\"75%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
            </tr>
        </table>
        <br>
        <br>
        <br>";

        $export .= "
        Telah diterima dari :
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditsacquittance['member_name']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">No. Akad</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditsacquittance['credits_account_serial']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: ".$acctcreditsacquittance['member_address']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: ".Configuration::numtotxt($acctcreditsacquittance['credits_acquittance_amount'])."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: PELUNASAN PEMINJAMAN</div></td>
            </tr>
             <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
                <td width=\"50%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctcreditsacquittance['credits_acquittance_amount'], 2)."</div></td>
            </tr>				
        </table>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"10%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">".$branch_name.", ".date('d-m-Y')."</div></td>
            </tr>
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\">Penyetor</div></td>
                <td width=\"10%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
            </tr>				
        </table>";

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Kwitansi Pelunasan Pinjaman.pdf';
        $pdf::Output($filename, 'I');
    }
}
