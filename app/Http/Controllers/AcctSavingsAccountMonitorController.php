<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\AcctSavingsAccountMonitorDataTable;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsAccountDetail;
use App\Models\AcctSavingsSyncronizeLog;
use App\Models\PreferenceCompany;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Support\Facades\DB;

class AcctSavingsAccountMonitorController extends Controller
{
    public function index()
    {
        $acctsavingsaccount = session()->get('savingsaccountmonitor');
        $sessiondata = session()->get('filter_savingsaccountmonitor');
        $acctsavingsaccountdetail = AcctSavingsAccountDetail::select('acct_savings_account.savings_account_no','acct_savings.savings_name','acct_savings_account_detail.today_transaction_date','acct_mutation.mutation_code','acct_savings_account_detail.mutation_out','acct_savings_account_detail.mutation_in','acct_savings_account_detail.last_balance','acct_savings_account_detail.operated_name')
        ->join('acct_savings_account', 'acct_savings_account_detail.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('acct_savings', 'acct_savings_account_detail.savings_id', '=', 'acct_savings.savings_id')
        ->join('acct_mutation', 'acct_savings_account_detail.mutation_id', '=', 'acct_mutation.mutation_id')
        ->where('acct_savings_account_detail.today_transaction_date', '>=', empty($sessiondata) ? date('Y-m-d') : date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_savings_account_detail.today_transaction_date', '<=', empty($sessiondata) ? date('Y-m-d') : date('Y-m-d', strtotime($sessiondata['end_date'])));
        if (!empty($acctsavingsaccount)) {

            $acctsavingsaccountdetail= $acctsavingsaccountdetail->where('acct_savings_account_detail.savings_account_id', $acctsavingsaccount['savings_account_id']);
        }
        $acctsavingsaccountdetail= $acctsavingsaccountdetail->orderBy('acct_savings_account_detail.savings_account_detail_id', 'ASC')
        ->get();

        return view('content.AcctSavingsAccountMonitor.index',compact('acctsavingsaccount','sessiondata','acctsavingsaccountdetail'));
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
            'start_date' => $start_date,
            'end_date'  => $end_date,
        );

        session()->put('filter_savingsaccountmonitor', $sessiondata);

        return redirect('savings-account-monitor');
    }

    public function resetFilter()
    {
        session()->forget('savingsaccountmonitor');
        session()->forget('filter_savingsaccountmonitor');

        return redirect('savings-account-monitor');
    }

    public function selectSavingsAccount($savings_account_id)
    {
        $savingsaccount = AcctSavingsAccount::withoutGlobalScopes()
        ->where('acct_savings_account.savings_account_id',$savings_account_id)
        ->join('core_member','acct_savings_account.member_id', '=', 'core_member.member_id')
        ->first();

        $data = array(
            'savings_account_id'            =>  $savings_account_id,
            'savings_account_no'            =>  $savingsaccount['savings_account_no'],
            'member_name'                   =>  $savingsaccount['member_name'],
            'member_address'                =>  $savingsaccount['member_address'],
        );

        session()->put('savingsaccountmonitor', $data);

        return redirect('savings-account-monitor');
    }

    public function modalSavingsAccount(AcctSavingsAccountMonitorDataTable $dataTable)
    {
        return $dataTable->render('content.AcctSavingsAccountMonitor.AcctSavingsAccountModal.index');
    }

    public function processPrinting(Request $request)
    {
        $preferencecompany 	= PreferenceCompany::first();
        $path = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $acctsavingsaccount = session()->get('savingsaccountmonitor');
        $sessiondata = session()->get('filter_savingsaccountmonitor');

        $savings_account_id         = $request->savings_account_id;

        $acctsavingsaccountdetail	= AcctSavingsAccountDetail::select('acct_savings_account.savings_account_no','acct_savings.savings_name','acct_savings_account_detail.today_transaction_date','acct_mutation.mutation_code','acct_savings_account_detail.mutation_out','acct_savings_account_detail.mutation_in','acct_savings_account_detail.last_balance','acct_savings_account_detail.operated_name','acct_savings_account_detail.savings_account_detail_id','acct_savings_account_detail.savings_print_status','acct_savings_account_detail.savings_account_id')
        ->join('acct_savings_account', 'acct_savings_account_detail.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('acct_savings', 'acct_savings_account_detail.savings_id', '=', 'acct_savings.savings_id')
        ->join('acct_mutation', 'acct_savings_account_detail.mutation_id', '=', 'acct_mutation.mutation_id')
        ->where('acct_savings_account_detail.today_transaction_date', '>=', empty($sessiondata) ? date('Y-m-d') : date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_savings_account_detail.today_transaction_date', '<=', empty($sessiondata) ? date('Y-m-d') : date('Y-m-d', strtotime($sessiondata['end_date'])));
        if (!empty($acctsavingsaccount)) {

            $acctsavingsaccountdetail= $acctsavingsaccountdetail->where('acct_savings_account_detail.savings_account_id', $acctsavingsaccount['savings_account_id']);
        }
        $acctsavingsaccountdetail= $acctsavingsaccountdetail->orderBy('acct_savings_account_detail.savings_account_detail_id', 'ASC')
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

        $pdf::SetFont('helvetica', '', 9);

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td rowspan=\"2\" width=\"10%\"><img src=\"".$path."\" alt=\"\" width=\"700%\" height=\"300%\"/></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <br/>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"100%\"><div style=\"text-align: center; font-size:14px; font-weight:bold\">KARTU MONITOR TABUNGAN</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl, true, false, false, false, '');
        

        $tbl1 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"15%\"><div style=\"text-align: left; font-size:12px; font-weight:bold\">NO. REK</div></td>
                <td width=\"35%\"><div style=\"text-align: left; font-size:12px; font-weight:bold\">: ".$acctsavingsaccount['savings_account_no']."</div></td>
                <td width=\"15%\" rowspan=\"2\"><div style=\"text-align: left; font-size:12px; font-weight:bold\">Alamat</div></td>
                <td width=\"35%\" rowspan=\"2\"><div style=\"text-align: left; font-size:12px; font-weight:bold\">: ".$acctsavingsaccount['member_address']."</div></td>
            </tr>
            <tr>
                <td width=\"15%\"><div style=\"text-align: left; font-size:12px; font-weight:bold\">NAMA</div></td>
                <td width=\"35%\"><div style=\"text-align: left; font-size:12px; font-weight:bold\">: ".$acctsavingsaccount['member_name']."</div></td>
            </tr>
        </table>";

        $tbl2 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"3%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">No</div></td>
                <td width=\"10%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Tgl Mutasi</div></td>
                <td width=\"6%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Sandi</div></td>
                <td width=\"15%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Debit</div></td>
                <td width=\"15%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Kredit</div></td>
                <td width=\"20%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Saldo</div></td>
                <td width=\"20%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Keterangan</div></td>
                <td width=\"10%\"><div style=\"text-align: center;border-bottom: 1px solid black;border-top: 1px solid black\">Val</div></td>
            </tr>
        ";

        $no=1;
        $tbl3 = "";
        foreach ($acctsavingsaccountdetail as $key => $val) {
            $tbl3 .= "
                <tr>
                    <td width=\"3%\"><div style=\"text-align: left;\">$no</div></td>
                    <td width=\"10%\"><div style=\"text-align: center;\">".date('d-m-Y', strtotime($val['today_transaction_date']))."</div></td>
                    <td width=\"6%\"><div style=\"text-align: center;\">".$val['mutation_code']."</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['mutation_out'], 2)."</div></td>
                    <td width=\"15%\"><div style=\"text-align: right;\">".number_format($val['mutation_in'], 2)."</div></td>
                    <td width=\"20%\"><div style=\"text-align: right;\">".number_format($val['last_balance'], 2)."</div></td>
                    <td width=\"20%\"><div style=\"text-align: left;\">".$val['description']."</div></td>
                    <td width=\"10%\"><div style=\"text-align: center;\">".$val['operated_name']."</div></td>
                </tr>
            ";
            $no++;
        }

        $tbl4 = "</table>";

        $pdf::writeHTML($tbl1.$tbl2.$tbl3.$tbl4, true, false, false, false, '');

        $filename = 'Kwitansi.pdf';
        $pdf::Output($filename, 'I');
    }

    public function syncronizeData()
    {
        $sessiondata = session()->get('filter_savingsaccountmonitor');
        $acctsavingsaccount = session()->get('savingsaccountmonitor');

        $datalog = array (
            'savings_syncronize_log_date' 		=> date('Y-m-d'),
            'savings_syncronize_log_start_date'	=> date('Y-m-d', strtotime($sessiondata['start_date'])),
            'savings_syncronize_log_end_date'	=> date('Y-m-d', strtotime($sessiondata['end_date'])),
            'savings_account_id'				=> $acctsavingsaccount['savings_account_id'],
            'branch_id'							=> auth()->user()->branch_id,
            'created_id'						=> auth()->user()->user_id,
        );

        DB::beginTransaction();

        try {

            AcctSavingsSyncronizeLog::create($datalog);

            $opening_balance 			= AcctSavingsAccountDetail::where('savings_account_id', $datalog['savings_account_id'])
            ->where('today_transaction_date', $datalog['savings_syncronize_log_start_date'])
            ->orderBy('savings_account_detail_id', 'DESC')
            ->first();
            
            if(!is_array($opening_balance)){
                $opening_date 			= AcctSavingsAccountDetail::where('savings_account_id', $datalog['savings_account_id'])
                ->where('today_transaction_date','<',$datalog['savings_syncronize_log_start_date'])
                ->first()
                ->today_transaction_date;
                
                $opening_balance 		= AcctSavingsAccountDetail::where('savings_account_id',$datalog['savings_account_id'])
                ->where('today_transaction_date', $opening_date)
                ->orderBy('savings_account_detail_id', 'DESC')
                ->first();
            }

            $acctsavingsaccountdetail 	= AcctSavingsAccountDetail::select('mutation_out','mutation_in','savings_account_detail_id','savings_account_id')
            ->where('today_transaction_date', '>=', $datalog['savings_syncronize_log_start_date'])
            ->where('today_transaction_date', '<=', $datalog['savings_syncronize_log_end_date'])
            ->where('savings_account_id', $datalog['savings_account_id'])
            ->orderBy('savings_account_detail_id', 'ASC')
            ->get();
            
            foreach ($acctsavingsaccountdetail as $key => $val) {
                $last_balance = ($opening_balance->opening_balance + $val['mutation_in']) - $val['mutation_out'];

                $newdata = array (
                    'savings_account_detail_id'		=> $val['savings_account_detail_id'],
                    'savings_account_id'			=> $val['savings_account_id'],
                    'opening_balance'				=> $opening_balance->opening_balance,
                    'last_balance'					=> $last_balance,
                );

                $opening_balance->opening_balance = $last_balance;

                AcctSavingsAccount::withoutGlobalScopes()
                ->where('savings_account_id',$newdata['savings_account_id'])->update(['savings_account_last_balance' => $newdata['last_balance'], 'updated_id' => auth()->user()->user_id]);
                    
                AcctSavingsAccountDetail::where('savings_account_detail_id', $newdata['savings_account_detail_id'])
                ->update([
                    'opening_balance'   => $newdata['opening_balance'],
                    'last_balance'      => $newdata['last_balance'],
                    'updated_id'        => auth()->user()->user_id
                ]);

            }

            DB::commit();
            $message = array(
                'pesan' => 'Syncronize Data berhasil',
                'alert' => 'success',
            );
            return redirect('savings-account-monitor')->with($message);
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Syncronize Data gagal',
                'alert' => 'error'
            );
            return redirect('savings-account-monitor')->with($message);
        }
    }
}
