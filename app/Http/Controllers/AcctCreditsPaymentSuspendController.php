<?php

namespace App\Http\Controllers;

use App\DataTables\AcctCreditsPaymentSuspend\AcctCreditsAccountDataTable;
use App\DataTables\AcctCreditsPaymentSuspend\AcctCreditsPaymentSuspendDataTable;
use App\Helpers\Configuration;
use App\Helpers\CreditHelper;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPayment;
use App\Models\AcctCreditsPaymentSuspend;
use App\Models\PreferenceCompany;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcctCreditsPaymentSuspendController extends Controller
{
    public function index(AcctCreditsPaymentSuspendDataTable $dataTable)
    {
        session()->forget('data_creditspaymentsuspendadd');
        $sessiondata = session()->get('filter-credit-p-suspend');

        $acctcredits = AcctCredits::select('credits_name', 'credits_id')
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctCreditsPaymentSuspend.List.index'   , compact('sessiondata', 'acctcredits'));
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

    public function add()
    {
        $sessiondata            = session()->get('data_creditspaymentsuspendadd');
        $period=Configuration::CreditsPaymentPeriod();
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
        return view('content.AcctCreditsPaymentSuspend.Add.index', compact('sessiondata', 'period', 'acctcreditsaccount', 'acctcreditspayment','credits_account_interest_last_balance'));
    }

    public function modalAcctCreditsAccount(AcctCreditsAccountDataTable $dataTable)
    {
        return $dataTable->render('content.AcctCreditsPaymentSuspend.Add.AcctCreditsAccountModal.index');
    }

    public function selectAcctCreditsAccount($credits_account_id)
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
        $sessiondata['credits_account_id'] = $credits_account_id;
        session()->put('data_creditspaymentsuspendadd', $sessiondata);

        return redirect('credits-payment-suspend/add');
    }
    public function processAdd(Request $request) {
          try {
          DB::beginTransaction();
          AcctCreditsPaymentSuspend::create([
            'branch_id'=>Auth::user()->branch_id,
            'credits_account_id'=>$request->credits_account_id,
            'member_id'=>$request->member_id,
            'credits_id'=>$request->credits_id,
            'credits_payment_suspend_date'=>Carbon::now()->format('Y-m-d'),
            'credits_payment_period'=>$request->credits_payment_period,
            'credits_grace_period'=>$request->credits_grace_period,
            'credits_payment_date_old'=>$request->credits_payment_date_old,
            'credits_payment_date_new'=>$request->credits_payment_date_new,
            'created_id'=>Auth::id(),
          ]);
          $ca=AcctCreditsAccount::find($request->credits_account_id);
          $ca->credits_account_payment_date=$request->credits_payment_date_new;
          $ca->save();
          DB::commit();
          return redirect()->route('cps.index')->with(['pesan' => 'Penundaan Angsuran Sukses','alert' => 'success']);
          } catch (\Exception $e) {
          DB::rollBack();
          dd($e);
          report($e);
          return redirect()->route('cps.index')->with(['pesan' => 'Penundaan Angsuran Gagal','alert' => 'error']);
          }
    }
    public function printNote($credits_payment_suspend_id) {
         $acctcreditsaccount		= AcctCreditsPaymentSuspend::with('member','account')->find($credits_payment_suspend_id);
         $paymenttype 			= Configuration::PaymentType();
         $paymentperiod 			= Configuration::CreditsPaymentPeriod();
         $preferencecompany 		= PreferenceCompany::first();

         if($acctcreditsaccount->account->payment_type_id == '' || $acctcreditsaccount->account->payment_type_id == 1){
             $datapola=CreditHelper::reSedule($credits_payment_suspend_id)->flat();
        }else if ($acctcreditsaccount->account->payment_type_id == 2){
             $datapola=CreditHelper::reSedule($credits_payment_suspend_id)->anuitas();
         }else if($acctcreditsaccount->account->payment_type_id == 3){
            $datapola=CreditHelper::reSedule($credits_payment_suspend_id)->slidingrate();
         }else if($acctcreditsaccount->account->payment_type_id == 4){
            //  $datapola=$this->menurunharian($credits_account_id);
         }
         $pdf = new TCPDF(['P', PDF_UNIT, 'A4', true, 'UTF-8', false]);

         $pdf::SetPrintHeader(false);
         $pdf::SetPrintFooter(false);

         $pdf::SetMargins(5, 5, 5, true);

         $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

         if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
             require_once(dirname(__FILE__).'/lang/eng.php');
             $pdf::setLanguageArray($l);
         }

         $pdf::SetFont('helvetica', 'B', 20);

         $pdf::AddPage('P','A4');
         $pdf::SetTitle('Jadwal Angsuran Ditunda');

         $pdf::SetFont('helvetica', '', 9);
         $tblheader = "
             <table id=\"items\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                 <tr>
                     <td style=\"text-align:center;\" width=\"100%\">
                         <div style=\"font-size:14px\";>".$preferencecompany['company_name']."<BR><b>Jadwal Angsuran</b></div>
                     </td>
                 </tr>
                 <tr style=\"line-height: 60%;\">
                     <td style=\"text-align:left;\" width=\"20%\">
                         <div style=\"font-size:12px\";><b>No. Pinjaman</b></div>
                     </td>
                     <td style=\"text-align:left;\" width=\"45%\">
                         <div style=\"font-size:12px\";><b>: {$acctcreditsaccount->account->credits_account_serial}</b></div>
                     </td>

                     <td style=\"text-align:left;\" width=\"20%\">
                         <div style=\"font-size:12px\";><b>Jenis Pinjaman</b></div>
                     </td>
                     <td style=\"text-align:left;\" width=\"50%\">
                         <div style=\"font-size:12px\";><b>: {$acctcreditsaccount->credit->credits_name}</b></div>
                     </td>
                 </tr>
                 <tr style=\"line-height: 60%;\">
                     <td style=\"text-align:left;\" width=\"20%\">
                         <div style=\"font-size:12px\";><b>Nama</b></div>
                     </td>
                     <td style=\"text-align:left;\" width=\"45%\">
                         <div style=\"font-size:12px\";><b>: ".$acctcreditsaccount->member->member_name."</b></div>
                     </td>
                     <td style=\"text-align:left;\" width=\"20%\">
                         <div style=\"font-size:12px\";><b>Jangka Waktu</b></div>
                     </td>
                     <td style=\"text-align:left;\" width=\"50%\">
                         <div style=\"font-size:12px\";><b>: {$acctcreditsaccount->account->credits_account_period} ".$paymentperiod[$acctcreditsaccount['credits_payment_period']]."</b></div>
                     </td>
                 </tr>
                 <tr  style=\"line-height: 60%;\">
                     <td style=\"text-align:left;\" width=\"20%\">
                         <div style=\"font-size:12px\";><b>Tipe Angsuran</b></div>
                     </td>
                     <td style=\"text-align:left;\" width=\"45%\">
                         <div style=\"font-size:12px\";><b>: {$paymenttype[$acctcreditsaccount->account->payment_type_id]}</b></div>
                     </td>
                     <td style=\"text-align:left;\" width=\"20%\">
                         <div style=\"font-size:12px\";><b>Plafon</b></div>
                     </td>
                     <td style=\"text-align:left;\" width=\"50%\">
                         <div style=\"font-size:12px\";><b>: Rp.".number_format($acctcreditsaccount->account->credits_account_amount)."</b></div>
                     </td>
                 </tr>
             </table>
             <br><br>
         ";
         $pdf::setCellHeightRatio(0.9);
         $pdf::writeHTML($tblheader, true, false, false, false, '');
         $pdf::setCellHeightRatio(1);

         $tbl1 = "
         <br>
         <table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
             <tr>
                 <td width=\"4%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Ke</div></td>
                 <td width=\"12%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Tanggal Angsuran</div></td>
                 <td width=\"8%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Hari</div></td>
                 <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Saldo Pokok</div></td>
                 <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Angsuran Pokok</div></td>
                 <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Angsuran Bunga</div></td>
                 <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Total Angsuran</div></td>
                 <td width=\"15%\"><div style=\"text-align: center;font-size:10;font-weight:bold\">Sisa Pokok</div></td>


             </tr>
         ";

         $no = 1;

         $tbl2 = "";

         $tbl3 ="";
         $totalpokok = 0;
         $totalmargin = 0;
         $total = 0;
         $totalpk = 0;
         Carbon::setLocale('id');
         foreach ($datapola as $key => $val) {

             $roundAngsuran=round($val['angsuran'],-3);
             $sisaRoundAngsuran = $val['angsuran'] - $roundAngsuran;
             $sumAngsuranBunga = $val['angsuran_bunga'] + $sisaRoundAngsuran;

             $tbl3 .= "
                 <tr>
                     <td ><div style=\"text-align: left;\">&nbsp; ".$val['ke']."</div></td>
                     <td ><div style=\"text-align: center;\">".date('d-m-Y',strtotime($val['tanggal_angsuran']))." &nbsp; </div></td>
                     <td ><div style=\"text-align: left;\">".Carbon::parse($val['tanggal_angsuran'])->translatedFormat('l')." &nbsp; </div></td>
                     <td ><div style=\"text-align: right;\">".number_format($val['opening_balance'], 2)." &nbsp; </div></td>
                     <td ><div style=\"text-align: right;\">".number_format($val['angsuran_pokok'], 2)." &nbsp; </div></td>
                     <td ><div style=\"text-align: right;\">".number_format($sumAngsuranBunga,2)." &nbsp; </div></td>
                     <td ><div style=\"text-align: right;\">".number_format($roundAngsuran,2)." &nbsp; </div></td>
                     <td ><div style=\"text-align: right;\">".number_format($val['last_balance'], 2)." &nbsp; </div></td>

                 </tr>
             ";

             $no++;
             $totalpokok += $val['angsuran_pokok'];
             $totalmargin += $sumAngsuranBunga;
             $total += $roundAngsuran;
             $totalpk += $val['last_balance'];
         }

         $tbl4 = "
             <tr>
                 <td colspan=\"4\"><div style=\"text-align: right;font-weight:bold\">Total</div></td>
                 <td><div style=\"text-align: right;font-weight:bold\">".number_format($totalpokok, 2)."</div></td>
                 <td><div style=\"text-align: right;font-weight:bold\">".number_format($totalmargin, 2)."</div></td>
                 <td><div style=\"text-align: right;font-weight:bold\">".number_format($total, 2)."</div></td>
                 <td><div style=\"text-align: right;font-weight:bold\">".number_format($totalpk, 2)."</div></td>
             </tr>
         </table>";
         $pdf::writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');
         $filename = 'Jadwal Angsuran Baru '.$acctcreditsaccount['credits_account_serial'].'.pdf';
         $pdf::setTitle($filename);
         $pdf::Output($filename, 'I');
    }

}
