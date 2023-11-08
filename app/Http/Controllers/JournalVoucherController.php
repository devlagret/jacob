<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\CoreBranch;
use App\Helpers\Configuration;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class JournalVoucherController extends Controller
{
    public function index()
    {
        session()->forget('data_journalvoucher');
        session()->forget('array_journalvoucher');
        Session::forget('journal-token');
        $session = session()->get('filter_journalvoucher');
        if (empty($session['start_date'])) {
            $start_date = date('Y-m-d');
        } else {
            $start_date = date('Y-m-d', strtotime($session['start_date']));
        }
        if (empty($session['end_date'])) {
            $end_date = date('Y-m-d');
        } else {
            $end_date = date('Y-m-d', strtotime($session['end_date']));
        }
        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $corebranch         = CoreBranch::where('data_state', 0)
            ->get();
        }else{
            $corebranch         = CoreBranch::where('data_state', 0)
            ->where('branch_id', $branch_id)
            ->get();
        }
        $acctjournalvoucher = AcctJournalVoucherItem::select('acct_journal_voucher_item.journal_voucher_item_id', 'acct_journal_voucher_item.journal_voucher_description', 'acct_journal_voucher_item.journal_voucher_debit_amount', 'acct_journal_voucher_item.journal_voucher_credit_amount', 'acct_journal_voucher_item.account_id', 'acct_account.account_code', 'acct_account.account_name', 'acct_journal_voucher_item.account_id_status', 'acct_journal_voucher.transaction_module_code', 'acct_journal_voucher.journal_voucher_date', 'acct_journal_voucher.journal_voucher_id')
        ->join('acct_journal_voucher','acct_journal_voucher_item.journal_voucher_id','=','acct_journal_voucher.journal_voucher_id')
        ->join('acct_account','acct_journal_voucher_item.account_id','=','acct_account.account_id')
        ->where('acct_journal_voucher.transaction_module_id', 10)
        ->where('acct_journal_voucher.data_state', 0)
        ->where('acct_journal_voucher_item.journal_voucher_amount','<>', 0)
        ->orderBy('acct_journal_voucher.created_at','DESC')
        ->orderBy('acct_journal_voucher.journal_voucher_date','DESC')
        ->where('acct_journal_voucher.journal_voucher_date','>=',$start_date)
        ->where('acct_journal_voucher.journal_voucher_date','<=',$end_date);
        if(!empty($session['branch_id'])) {
            $acctjournalvoucher = $acctjournalvoucher->where('acct_journal_voucher.branch_id', $session['branch_id']);
        }
        $acctjournalvoucher = $acctjournalvoucher->get();

        return view('content.JournalVoucher.List.index',compact('session','corebranch','acctjournalvoucher'));
    }

    public function add()
    {
        if(empty(Session::get('journal-token'))){
            Session::put('journal-token',Str::uuid());
        }
        $accountstatus = Configuration::AccountStatus();
        $acctaccount = AcctAccount::select('account_id','account_code','account_name')
        ->where('data_state',0)
        ->get();
        $session = session()->get('data_journalvoucher');
        $arrayses = session()->get('array_journalvoucher');

        return view('content.JournalVoucher.Add.index',compact('accountstatus','acctaccount','session','arrayses'));
    }

    public function processAdd(Request $request)
    {
        if(empty(Session::get('journal-token'))){
            return redirect('journal-voucher')->with(['pesan' => 'Data Jurnal Umum berhasil ditambah -','alert' => 'success']);
        }
        $token = Session::get('journal-token');
        $journal_voucher_period = date("Ym", strtotime($request->journal_voucher_date));
        $acctjournalvoucheritem = session()->get('array_journalvoucher');
        $transaction_module_code = "JU";
        $transaction_module_id 		= PreferenceTransactionModule::where('transaction_module_code',$transaction_module_code)
        ->first()
        ->transaction_module_id;
        $data = array(
            'branch_id'						=> auth()->user()->branch_id,
            'journal_voucher_period' 		=> $journal_voucher_period,
            'journal_voucher_date'			=> date('Y-m-d', strtotime($request->journal_voucher_date)),
            'journal_voucher_title'			=> $request->journal_voucher_description,
            'journal_voucher_description'	=> $request->journal_voucher_description,
            'transaction_module_id'			=> $transaction_module_id,
            'transaction_module_code'		=> $transaction_module_code,
            'created_id'					=> auth()->user()->user_id,
            'journal_voucher_token'         => $token,
        );

        DB::beginTransaction();

        try {

            AcctJournalVoucher::create($data);

            $journal_voucher_id = AcctJournalVoucher::where('created_id', auth()->user()->user_id)
            ->where('journal_voucher_token',$token)
            ->orderBy('journal_voucher_id','DESC')
            ->first()
            ->journal_voucher_id;
            
            foreach ($acctjournalvoucheritem as $key => $val) {
                $account_default_status = AcctAccount::select('account_default_status')
                ->where('account_id', $val['account_id'])
                ->first()
                ->account_default_status;

                if($val['journal_voucher_status'] == 0){
                    $data_debet =array(
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $val['account_id'],
                        'journal_voucher_description'	=> $data['journal_voucher_description'],
                        'journal_voucher_amount'		=> $val['journal_voucher_amount'],
                        'journal_voucher_debit_amount'	=> $val['journal_voucher_amount'],
                        'account_id_status'				=> 0,
                        'account_id_default_status'		=> $account_default_status,
                        'created_id'					=> auth()->user()->user_id,
                    );
                    AcctJournalVoucherItem::create($data_debet);
                } else {
                    $data_credit =array(
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $val['account_id'],
                        'journal_voucher_description'	=> $data['journal_voucher_description'],
                        'journal_voucher_amount'		=> $val['journal_voucher_amount'],
                        'journal_voucher_credit_amount'	=> $val['journal_voucher_amount'],
                        'account_id_status'				=> 1,
                        'account_id_default_status'		=> $account_default_status,
                        'created_id'					=> auth()->user()->user_id,
                    );
                    AcctJournalVoucherItem::create($data_credit);
                }
            }

            DB::commit();
            $message = array(
                'pesan' => 'Data Jurnal Umum berhasil ditambah',
                'alert' => 'success',
            );
            Session::forget('journal-token');
            return redirect('journal-voucher')->with($message);
        } catch (\Exception $e) {
            DB::rollback();
            Session::forget('journal-token');
            $message = array(
                'pesan' => 'Data Jurnal Umum gagal ditambah',
                'alert' => 'error'
            );
            return redirect('journal-voucher')->with($message);
        }

    }

    public function filter(Request $request)
    {
        if($request->start_date){
            $start_date = $request->start_date;
        }else{
            $start_date = date('d-m-Y');
        }
        if($request->end_date){
            $end_date = $request->end_date;
        }else{
            $end_date = date('d-m-Y');
        }

        $sessiondata = array(
            'start_date'    => $start_date,
            'end_date'      => $end_date,
            'branch_id'     => $request->branch_id,
        );

        session()->put('filter_journalvoucher', $sessiondata);

        return redirect('journal-voucher');
    }

    public function resetFilter()
    {
        session()->forget('filter_journalvoucher');

        return redirect('journal-voucher');
    }   

    public function addArray(Request $request)
    {
        $request->validate([
            'account_id'                        => ['required'],
            'journal_voucher_amount'            => ['required'],
            'journal_voucher_status'            => ['required'],
        ]);

        $arraydatases = array(
            'account_id'                        => $request->account_id,
            'journal_voucher_amount'            => $request->journal_voucher_amount,
            'journal_voucher_status'            => $request->journal_voucher_status,
            'journal_voucher_description_item'  => $request->journal_voucher_description_item,
        );

        $lastdatases = session()->get('array_journalvoucher');
        if($lastdatases !== null){
            array_push($lastdatases, $arraydatases);
            session()->put('array_journalvoucher', $lastdatases);
        } else {
            $lastdatases = [];
            array_push($lastdatases, $arraydatases);
            session()->push('array_journalvoucher', $arraydatases);
        }

        return redirect('journal-voucher/add');
    }

    public function elementsAdd(Request $request)
    {
        $session = session()->get('data_journalvoucher');
        if(!$session || $session == ''){
            $session['journal_voucher_date']           = '';
            $session['journal_voucher_description']        = '';
        }
        $session[$request->name] = $request->value;
        session()->put('data_journalvoucher', $session);
    }

    public function resetElementsAdd()
    {
        session()->forget('data_journalvoucher');
        session()->forget('array_journalvoucher');

        return redirect('journal-voucher/add');
    }

    public function print($journal_voucher_id)
    {
        $preferencecompany 			= PreferenceCompany::first();
        $acctjournalvoucher 		= AcctJournalVoucher::select('acct_journal_voucher.journal_voucher_id', 'acct_journal_voucher.journal_voucher_date', 'acct_journal_voucher.journal_voucher_description','acct_journal_voucher.journal_voucher_no', 'acct_journal_voucher.branch_id', 'core_branch.branch_name')
        ->join('core_branch','acct_journal_voucher.branch_id','=','core_branch.branch_id')
        ->where('acct_journal_voucher.journal_voucher_id', $journal_voucher_id)
        ->first();
        $acctjournalvoucheritem 	= AcctJournalVoucherItem::select('acct_journal_voucher_item.journal_voucher_item_id', 'acct_journal_voucher_item.journal_voucher_id', 'acct_journal_voucher_item.account_id', 'acct_journal_voucher_item.journal_voucher_credit_amount', 'acct_journal_voucher_item.journal_voucher_debit_amount', 'acct_account.account_code', 'acct_account.account_name', 'acct_journal_voucher_item.journal_voucher_amount', 'acct_journal_voucher_item.account_id_status')
        ->join('acct_account','acct_journal_voucher_item.account_id','=','acct_account.account_id')
        ->where('acct_journal_voucher_item.journal_voucher_id', $journal_voucher_id)
        ->get();

        $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(7, 7, 7, 7);
        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 10);

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td rowspan=\"3\" width=\"20%\"><img src=\"".public_path('storage/'.$preferencecompany['logo_koperasi'])."\" alt=\"\" width=\"700%\" height=\"300%\"/></td>
                <td><div style=\"text-align: center; font-size:14px;font-weight: bold\">JURNAL UMUM</div></td>
            </tr>
                <tr>
                <td><div style=\"text-align: center; font-size:10px\">".$acctjournalvoucher['branch_name']."</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center; font-size:10px\">Jam : ".date('H:i:s')."</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl, true, false, false, false, '');
        
        $tbl1 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Tanggal Jurnal</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".date('d-m-Y', strtotime($acctjournalvoucher['journal_voucher_date']))."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">No. Jurnal</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctjournalvoucher['journal_voucher_no']."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Uraian</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctjournalvoucher['journal_voucher_description']."</div></td>
            </tr>		
        </table>";

        $tbl2 = "
        <br>
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
            <tr>
                <td width=\"5%\"><div style=\"text-align: center;font-weight: bold\">No.</div></td>
                <td width=\"40%\"><div style=\"text-align: center;font-weight: bold\">Perkiraan</div></td>
                <td width=\"20%\"><div style=\"text-align: center;font-weight: bold\">Debet</div></td>
                <td width=\"20%\"><div style=\"text-align: center;font-weight: bold\">Kredit</div></td>
            </tr>
        ";
        $no =1;
        $tbl3 = "";
        $total_debet = 0;
        $total_kredit = 0;
        foreach ($acctjournalvoucheritem as $key => $val) {
            $tbl3 .= "
                    <tr>
                        <td width=\"5%\"><div style=\"text-align: center;font-size:12px\">".$no."</div></td>
                        <td width=\"40%\"><div style=\"text-align: left;font-size:12px\">(".$val['account_code'].") ".$val['account_name']."</div></td>
                        <td width=\"20%\"><div style=\"text-align: right;font-size:12px\">".number_format($val['journal_voucher_debit_amount'],2)."</div></td>
                        <td width=\"20%\"><div style=\"text-align: right;font-size:12px\">".number_format($val['journal_voucher_credit_amount'],2)."</div></td>
                    </tr>
            ";
            $total_debet += $val['journal_voucher_debit_amount'];
            $total_kredit += $val['journal_voucher_credit_amount'];
            $no++;
        }
        $tbl4 = "
            <tr>
                <td width=\"5%\"><div style=\"text-align: center;font-size:12px\"></div></td>
                <td width=\"40%\"><div style=\"text-align: left;font-size:12px\"></div></td>
                <td width=\"20%\"><div style=\"text-align: right;font-size:12px\"></div></td>
                <td width=\"20%\"><div style=\"text-align: right;font-size:12px\"></div></td>
            </tr>		
        </table>

        <table cellspacing=\"0\" cellpadding=\"1\" border=\"1\" width=\"100%\">
            <tr>
                <td colspan=\"2\" width=\"45%\"></td>
                <td width=\"20%\"><div style=\"text-align: right;font-weight:bold\">".number_format($total_debet, 2)."</div></td>
                <td width=\"20%\"><div style=\"text-align: right;font-weight:bold\">".number_format($total_kredit, 2)."</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

        $filename = 'Jurnal_'.$acctjournalvoucher['journal_voucher_no'].'_'.$acctjournalvoucher['journal_voucher_date'].'.pdf';

        $pdf::Output($filename, 'I');
    }

    public static function getMinID($journal_voucher_id)
    {
        $data = AcctJournalVoucherItem::where('journal_voucher_id', $journal_voucher_id)
        ->first();

        return $data->journal_voucher_item_id;
    }

    public static function getAccountCode($account_id)
    {
        $data = AcctAccount::where('account_id', $account_id)
        ->first();

        return $data->account_code;
    }

    public static function getAccountName($account_id)
    {
        $data = AcctAccount::where('account_id', $account_id)
        ->first();

        return $data->account_name;
    }
}
