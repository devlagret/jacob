<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcctAccount;
use App\Models\AcctAccountBalanceDetail;
use App\Models\AcctAccountOpeningBalance;
use App\Models\CoreBranch;
use App\Models\AcctJournalVoucherItem;
use App\Models\PreferenceCompany;
use DB;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\Auth;

class DailyCashFlowReportController extends Controller
{
    public function index()
    {
        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state',0)
        ->get();

        return view('content.DailyCashFlowReport.index',compact('corebranch'));
    }

    public function print(Request $request)
    {
        $sesi = array (
            "start_date" 		=> date('Y-m-d', strtotime($request->start_date)),
            "branch_id"			=> $request->branch_id,
        );

        if(empty($sesi['branch_id'])){
            $branch_id = auth()->user()->branch_id;
        } else {
            $branch_id = $sesi['branch_id'];
        }

        $preferencecompany = PreferenceCompany::first();
        // $accountbalancedetail	= AcctAccountBalanceDetail::select('acct_account_balance_detail.account_balance_detail_id', 'acct_account_balance_detail.transaction_type', 'acct_account_balance_detail.transaction_code', 'acct_account_balance_detail.transaction_date', 'acct_account_balance_detail.transaction_id', 'acct_account_balance_detail.account_id', 'acct_account.account_code', 'acct_account.account_name', 'acct_account_balance_detail.opening_balance', 'acct_account_balance_detail.account_in', 'acct_account_balance_detail.account_out', 'acct_account_balance_detail.last_balance')
        // ->join('acct_account', 'acct_account_balance_detail.account_id','=','acct_account.account_id')
        // ->where('acct_account_balance_detail.account_id', $preferencecompany['account_cash_id'])
        // ->where('acct_account_balance_detail.branch_id', $branch_id)
        // ->where('acct_account_balance_detail.transaction_date', $sesi['start_date'])
        // ->orderBy('acct_account_balance_detail.transaction_date', 'ASC')
        // ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
        // ->get();
        // $opening_date = AcctAccountBalanceDetail::where('account_id', $preferencecompany['account_cash_id'])
        // ->where('branch_id', $branch_id)
        // ->where('transaction_date', $sesi['start_date'])
        // ->first();
        // $opening_balance = AcctAccountBalanceDetail::where('transaction_date', $opening_date->transaction_date)
        // ->where('account_id', $preferencecompany['account_cash_id'])
        // ->where('branch_id', $branch_id)
        // ->orderBy('account_balance_detail_id', 'DESC')
        // ->first();

        // if(empty($opening_balance)){
        //     $opening_date = AcctAccountBalanceDetail::where('account_id', $preferencecompany['account_cash_id'])
        //     ->where('branch_id', $branch_id)
        //     ->where('transaction_date','<',$sesi['start_date'])
        //     ->first();

        //     $opening_balance = AcctAccountBalanceDetail::where('transaction_date', $opening_date->transaction_date)
        //     ->where('account_id', $preferencecompany['account_cash_id'])
        //     ->where('branch_id', $branch_id)
        //     ->orderBy('account_balance_detail_id', 'DESC')
        //     ->first();
        // }
            // echo(Auth::user()->branch_id);
        $opening_balance = AcctAccountBalanceDetail::
        where('acct_account_balance_detail.transaction_date',date('Y-m-d',strtotime($sesi['start_date'])))
        ->where('acct_account_balance_detail.branch_id',Auth::user()->branch_id)
        ->where('account_id', $preferencecompany['account_cash_id'])
        ->orderBy('acct_account_balance_detail.account_balance_detail_id', 'ASC')
        ->first();
        // return [$opening_balance];
        $data = AcctJournalVoucherItem::where('acct_journal_voucher_item.account_id', $preferencecompany['account_cash_id'])
        ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_journal_voucher_item.journal_voucher_id')
        ->where('acct_journal_voucher.branch_id', $branch_id)
        ->where('acct_journal_voucher.journal_voucher_date','>=',date('Y-m',strtotime($sesi['start_date'])).'-01')
        ->where('acct_journal_voucher.journal_voucher_date','<=',$sesi['start_date'])
        ->get();

        $totaldebit     = 0;
        $totalkredit    = 0;
        foreach ($data as $key => $val) {
            $totaldebit     += $val['journal_voucher_debit_amount'];
            $totalkredit    += $val['journal_voucher_credit_amount'];
        }
        $opening_balance = $opening_balance->opening_balance ;
        // $opening_balance = ($opening_balance->opening_balance + $totaldebit) - $totalkredit;

        $accountbalancedetail	= AcctJournalVoucherItem::where('acct_journal_voucher_item.account_id', $preferencecompany['account_cash_id'])
        ->join('acct_journal_voucher','acct_journal_voucher.journal_voucher_id','=','acct_journal_voucher_item.journal_voucher_id')
        ->where('acct_journal_voucher.branch_id', $branch_id)
        ->where('acct_journal_voucher.journal_voucher_date', $sesi['start_date'])
        ->orderBy('acct_journal_voucher.journal_voucher_date', 'ASC')
        ->orderBy('acct_journal_voucher_item.journal_voucher_item_id', 'ASC')
        ->get();
     
        $account_id_status = AcctAccount::where('account_id', $preferencecompany['account_cash_id'])
        ->where('data_state',0)
        ->first()
        ->account_default_status;

        $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

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

        $pdf::SetFont('helvetica', '', 9);
        
        $tbl = "
            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
                <tr>
                    <td rowspan=\"2\" width=\"10%\"><img src=\"".public_path('storage/'.$preferencecompany['logo_koperasi'])."\" alt=\"\" width=\"700%\" height=\"300%\"/></td>
                </tr>
            </table>
            <br/>
            <br/>
            <br/>
            <br/>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: center; font-size:14px\">LAPORAN ARUS KAS HARIAN</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: center; font-size:10px\">Per ".date('d-m-Y', strtotime($sesi['start_date']))."</div></td>
                </tr>
            </table>";

        $tbl1 = "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td width=\"10%\"><div style=\"text-align: left; font-size:12px\">No. Rek</div></td>
                    <td width=\"25%\"><div style=\"text-align: left; font-size:12px\">".$this->getAccountCode($preferencecompany['account_cash_id'])."</div></td>
                    <td width=\"10%\"><div style=\"text-align: left; font-size:12px\">Saldo</div></td>
                    <td><div style=\"text-align: left; font-size:12px\">".number_format($opening_balance, 2)."</div></td>
                </tr>
                <tr>
                    <td width=\"10%\"><div style=\"text-align: left; font-size:12px\">Nama</div></td>
                    <td><div style=\"text-align: left; font-size:12px\">".$this->getAccountName($preferencecompany['account_cash_id'])."</div></td>
                </tr>
            </table>";

        $pdf::writeHTML($tbl.$tbl1, true, false, false, false, '');

        $tbl1 = "
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"5%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Tanggal</div></td>
                <td width=\"40%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Uraian</div></td>
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Debit (Rp)</div></td>
                <td width=\"20%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Kredit (Rp)</div></td>
                
            </tr>				
        </table>";

        $no = 1;

        $tbl2 = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

        $tbl3 = "";
        $totaldebit   = 0;
        $totalkredit  = 0;
        $sisasaldo    = 0;

        // foreach ($accountbalancedetail as $key => $val) {
        //     $description = $this->getJournalVoucherDescription($val['transaction_id'], $preferencecompany['account_cash_id']);

        //     if($account_id_status == 1){
        //         $tbl3 .= "
        //             <tr>
        //                 <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
        //                 <td width=\"10%\"><div style=\"text-align: left;\">".date('d-m-Y', strtotime($val['transaction_date']))."</div></td>
        //                 <td width=\"40%\"><div style=\"text-align: left;\">".$description."</div></td>
        //                 <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['account_out'], 2)."</div></td>
        //                 <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['account_in'], 2)."</div></td>
        //             </tr>
        //         ";

        //         $totaldebit     += $val['account_out'];
        //         $totalkredit    += $val['account_in'];
        //         $sisasaldo      = ($opening_balance->opening_balance + $totaldebit) - $totalkredit;
        //         $no++;

        //         $tbl4 = "
        //             <tr>
        //                 <td colspan =\"3\"><div style=\"font-size:10;text-align:right;font-style:italic\">Jumlah Mutasi</div></td>
        //                 <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totaldebit, 2)."</div></td>
        //                 <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalkredit, 2)."</div></td>
                        
        //             </tr>
        //             <tr>
        //                 <td colspan =\"3\"><div style=\"font-size:10;text-align:right;font-style:italic\">Saldo Akhir</div></td>
        //                 <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($sisasaldo, 2)."</div></td>
        //                 <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                        
        //             </tr>
                                
        //         </table>";
        //     } else {
        //         $tbl3 .= "
        //             <tr>
        //                 <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
        //                 <td width=\"10%\"><div style=\"text-align: left;\">".date('d-m-Y', strtotime($val['transaction_date']))."</div></td>
        //                 <td width=\"40%\"><div style=\"text-align: left;\">".$description."</div></td>
        //                 <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['account_in'], 2)."</div></td>
        //                 <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['account_out'], 2)."</div></td>
        //             </tr>
        //         ";

        //         $totaldebit += $val['account_in'];
        //         $totalkredit += $val['account_out'];
        //         $sisasaldo = ($opening_balance->opening_balance + $totaldebit) - $totalkredit;
        //         $no++;

        //         $tbl4 = "
        //             <tr>
        //                 <td colspan =\"3\"><div style=\"font-size:10;text-align:right;font-style:italic\">Jumlah Mutasi</div></td>
        //                 <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totaldebit, 2)."</div></td>
        //                 <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalkredit, 2)."</div></td>
                        
        //             </tr>
        //             <tr>
        //                 <td colspan =\"3\"><div style=\"font-size:10;text-align:right;font-style:italic\">Saldo Akhir</div></td>
        //                 <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($sisasaldo, 2)."</div></td>
        //                 <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                        
        //             </tr>
                                
        //         </table>";
        //     }
        // }
        foreach ($accountbalancedetail as $key => $val) {

            if($account_id_status == 1){
                $tbl3 .= "
                    <tr>
                        <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                        <td width=\"10%\"><div style=\"text-align: left;\">".date('d-m-Y', strtotime($val['journal_voucher_date']))."</div></td>
                        <td width=\"40%\"><div style=\"text-align: left;\">".$val['journal_voucher_description']."</div></td>
                        <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['journal_voucher_credit_amount'], 2)."</div></td>
                        <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['journal_voucher_debit_amount'], 2)."</div></td>
                    </tr>
                ";

                $totaldebit     += $val['journal_voucher_credit_amount'];
                $totalkredit    += $val['journal_voucher_debit_amount'];
                $sisasaldo      = ($opening_balance->opening_balance + $totaldebit) - $totalkredit;
                $no++;

                $tbl4 = "
                    <tr>
                        <td colspan =\"3\"><div style=\"font-size:10;text-align:right;font-style:italic\">Jumlah Mutasi</div></td>
                        <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totaldebit, 2)."</div></td>
                        <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalkredit, 2)."</div></td>
                        
                    </tr>
                    <tr>
                        <td colspan =\"3\"><div style=\"font-size:10;text-align:right;font-style:italic\">Saldo Akhir</div></td>
                        <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($sisasaldo, 2)."</div></td>
                        <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                        
                    </tr>
                                
                </table>";
            } else {
                $tbl3 .= "
                    <tr>
                        <td width=\"5%\"><div style=\"text-align: left;\">".$no."</div></td>
                        <td width=\"10%\"><div style=\"text-align: left;\">".date('d-m-Y', strtotime($val['journal_voucher_date']))."</div></td>
                        <td width=\"40%\"><div style=\"text-align: left;\">".$val['journal_voucher_description']."</div></td>
                        <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['journal_voucher_debit_amount'], 2)."</div></td>
                        <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['journal_voucher_credit_amount'], 2)."</div></td>
                    </tr>
                ";

                $totaldebit += $val['journal_voucher_debit_amount'];
                $totalkredit += $val['journal_voucher_credit_amount'];
                $sisasaldo = ($opening_balance + $totaldebit) - $totalkredit;
                $no++;

                $tbl4 = "
                    <tr>
                        <td colspan =\"3\"><div style=\"font-size:10;text-align:right;font-style:italic\">Jumlah Mutasi</div></td>
                        <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totaldebit, 2)."</div></td>
                        <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($totalkredit, 2)."</div></td>
                        
                    </tr>
                    <tr>
                        <td colspan =\"3\"><div style=\"font-size:10;text-align:right;font-style:italic\">Saldo Akhir</div></td>
                        <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\">".number_format($sisasaldo, 2)."</div></td>
                        <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:9;text-align:right\"></div></td>
                        
                    </tr>
                                
                </table>";
            }
        }

        $pdf::writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

        $filename = 'Kwitansi.pdf';
        $pdf::Output($filename, 'I');
    }

    public function getAccountCode($account_id)
    {
        $data = AcctAccount::where('account_id', $account_id)
        ->first();

        return $data->account_code;
    }

    public function getAccountName($account_id)
    {
        $data = AcctAccount::where('account_id', $account_id)
        ->first();

        return $data->account_name;
    }

    public function getJournalVoucherDescription($journal_voucher_id, $account_id)
    {
        $data = AcctJournalVoucherItem::where('journal_voucher_id', $journal_voucher_id)
        ->where('account_id', $account_id)
        ->first();

        return $data->journal_voucher_description;
    }
}
