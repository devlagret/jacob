<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\AcctSavingsAccountMutationDataTable;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsAccountDetail;
use App\Models\PreferenceCompany;
use Elibyy\TCPDF\Facades\TCPDF;

class AcctSavingsAccountMutationController extends Controller
{
    public function index()
    {
        $acctsavingsaccount = session()->get('savingsaccount');
        $sessiondata = session()->get('filter_savingsaccountmutation');
        $datases = session()->get('datases');
        $acctsavingsaccountdetail = AcctSavingsAccountDetail::select('acct_savings_account.savings_account_no','acct_savings.savings_name','acct_savings_account_detail.today_transaction_date','acct_mutation.mutation_code','acct_savings_account_detail.mutation_out','acct_savings_account_detail.mutation_in','acct_savings_account_detail.last_balance','acct_savings_account_detail.operated_name')
        ->join('acct_savings_account', 'acct_savings_account_detail.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('acct_savings', 'acct_savings_account_detail.savings_id', '=', 'acct_savings.savings_id')
        ->join('acct_mutation', 'acct_savings_account_detail.mutation_id', '=', 'acct_mutation.mutation_id')
        ->where('acct_savings_account_detail.today_transaction_date', '>=', empty($sessiondata) ? date('Y-m-d') : date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_savings_account_detail.today_transaction_date', '<=', empty($sessiondata) ? date('Y-m-d') : date('Y-m-d', strtotime($sessiondata['end_date'])));
        if (!empty($acctsavingsaccount)) {

            $acctsavingsaccountdetail= $acctsavingsaccountdetail->where('acct_savings_account_detail.savings_account_id', $acctsavingsaccount['savings_account_id']);
        }
        $acctsavingsaccountdetail= $acctsavingsaccountdetail->where('acct_savings_account_detail.savings_print_status', 0)
        ->get();

        return view('content.AcctSavingsAccountMutation.index', compact('acctsavingsaccount','sessiondata','acctsavingsaccountdetail','datases'));
    }

    public function elementsAdd(Request $request)
    {
        $datases = session()->get('datases');
        if(!$datases || $datases == ''){
            $datases['savings_account_last_number']  = '';
        }
        $datases[$request->name] = $request->value;
        session()->put('datases', $datases);
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

        session()->put('filter_savingsaccountmutation', $sessiondata);

        return redirect('savings-account-mutation');
    }

    public function resetFilter()
    {
        session()->forget('savingsaccount');
        session()->forget('datases');
        session()->forget('filter_savingsaccountmutation');

        return redirect('savings-account-mutation');
    }

    public function modalSavingsAccount(AcctSavingsAccountMutationDataTable $dataTable)
    {
        return $dataTable->render('content.AcctSavingsAccountMutation.AcctSavingsAccountModal.index');
    }

    public function selectSavingsAccount($savings_account_id)
    {
        $savingsaccount = AcctSavingsAccount::where('acct_savings_account.savings_account_id',$savings_account_id)
        ->join('core_member','acct_savings_account.member_id', '=', 'core_member.member_id')
        ->first();

        $data = array(
            'savings_account_id'            =>  $savings_account_id,
            'savings_account_no'            =>  $savingsaccount['savings_account_no'],
            'member_name'                   =>  $savingsaccount['member_name'],
            'member_address'                =>  $savingsaccount['member_address'],
            'savings_account_last_number'   =>  $savingsaccount['savings_account_last_number'],
        );

        session()->put('savingsaccount', $data);

        return redirect('savings-account-mutation');
    }

    public function processPrinting(Request $request)
    {
        $preferencecompany 	= PreferenceCompany::first();
        $acctsavingsaccount = session()->get('savingsaccount');
        $sessiondata = session()->get('filter_savingsaccountmutation');
        $path = public_path('storage/'.$preferencecompany['logo_koperasi']);

        $status 						= $request->view;
        $savings_account_id 			= $request->savings_account_id;
        $savings_account_last_number 	= $request->savings_account_last_number;

        $acctsavingsaccountdetail		= AcctSavingsAccountDetail::select('acct_savings_account.savings_account_no','acct_savings.savings_name','acct_savings_account_detail.today_transaction_date','acct_mutation.mutation_code','acct_savings_account_detail.mutation_out','acct_savings_account_detail.mutation_in','acct_savings_account_detail.last_balance','acct_savings_account_detail.operated_name','acct_savings_account_detail.savings_account_detail_id','acct_savings_account_detail.savings_print_status','acct_savings_account_detail.savings_account_id')
        ->join('acct_savings_account', 'acct_savings_account_detail.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('acct_savings', 'acct_savings_account_detail.savings_id', '=', 'acct_savings.savings_id')
        ->join('acct_mutation', 'acct_savings_account_detail.mutation_id', '=', 'acct_mutation.mutation_id')
        ->where('acct_savings_account_detail.today_transaction_date', '>=', empty($sessiondata) ? date('Y-m-d') : date('Y-m-d', strtotime($sessiondata['start_date'])))
        ->where('acct_savings_account_detail.today_transaction_date', '<=', empty($sessiondata) ? date('Y-m-d') : date('Y-m-d', strtotime($sessiondata['end_date'])));
        if (!empty($acctsavingsaccount)) {

            $acctsavingsaccountdetail= $acctsavingsaccountdetail->where('acct_savings_account_detail.savings_account_id', $acctsavingsaccount['savings_account_id']);
        }
        $acctsavingsaccountdetail= $acctsavingsaccountdetail->where('acct_savings_account_detail.savings_print_status', 0)
        ->get();

        if(empty($savings_account_last_number) || $savings_account_last_number == 0){
            $no = 1;
        } else {
            $no = $savings_account_last_number + 1;
        }

        foreach ($acctsavingsaccountdetail as $key => $val) {
            if($no == 14 ){
                $no = 1;
            } else {
                $no = $no;
            }

            if($val['mutation_in'] == 0){
                $mutation_in 	= '';
                $mutation_out 	= number_format($val['mutation_out'], 2);
            }

            if($val['mutation_out'] == 0){
                $mutation_in 	= number_format($val['mutation_in'], 2);
                $mutation_out 	= '';
            }


            $data[] = array (
                'no'						=> $no,
                'savings_account_detail_id' => $val['savings_account_detail_id'],
                'savings_account_id'		=> $val['savings_account_id'],
                'transaction_date'			=> $val['today_transaction_date'],
                'transaction_code'			=> $val['mutation_code'],
                'transaction_in'			=> $mutation_in,
                'transaction_out'			=> $mutation_out,
                'last_balance'				=> $val['last_balance'],
                'operated_name'				=> $val['operated_name'],	
                'status'					=> $val['savings_print_status'],
            );
            
            $no++;
            
            session()->put('data_mutation', $data);
        }


        $data_mutation = session()->get('data_mutation');

        if($status == 'print'){
            if(!empty($data_mutation)) {
                foreach ($data_mutation as $k => $v) {
                    $update_data = array(
                        'savings_account_detail_id'		=> $v['savings_account_detail_id'],
                        'savings_account_id'			=> $v['savings_account_id'],
                        'savings_print_status'			=> 1,
                        'savings_account_last_number'	=> $v['no'],
                    );
    
                    AcctSavingsAccountDetail::where('savings_account_detail_id', $update_data['savings_account_detail_id'])
                    ->update(['savings_print_status'=> 1, 'updated_id' => auth()->user()->user_id]);
    
                    AcctSavingsAccount::where('savings_account_id', $update_data['savings_account_id'])
                    ->update(['savings_account_last_number' => $update_data['savings_account_last_number'], 'updated_id' => auth()->user()->user_id]);
                }
            }
        }


        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(5, 24, 7, 7);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $resolution= array(180, 170);
        
        $page = $pdf::AddPage('P', $resolution);

        $pdf::SetFont('helvetica', '', 9);
        $tbl1 = "";
        $tbl0 = "
        <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td rowspan=\"2\" width=\"10%\"><img src=\"".$path."\" alt=\"\" width=\"700%\" height=\"300%\"/></td>
            </tr>
            <tr>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <br/>";


        $tbl = "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
        if($savings_account_last_number > 0){
            for ($i=1; $i <= $savings_account_last_number ; $i++) { 
                if($i == 7){
                    $tbl1 .= "
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                    ";

                } else {
                    $tbl1 .= "
                    <tr>
                        <td></td>
                    </tr>";
                }
                
            }
        } 


        if (empty($data_mutation)){
                $tbl1 .= "";
        } else {
            foreach ($data_mutation as $key => $val) {
                $tbl1 .= "
                        <tr>
                            <td width=\"4%\"><div style=\"text-align: left;\">".$val['no'].".</div></td>
                            <td width=\"11%\"><div style=\"text-align: center;\">".date('d-m-y',strtotime(($val['transaction_date'])))."</div></td>
                            <td width=\"9%\"><div style=\"text-align: center;\">".$val['transaction_code']."</div></td>
                            <td width=\"18%\"><div style=\"text-align: right;\">".$val['transaction_out']." &nbsp;</div></td>
                            <td width=\"20%\"><div style=\"text-align: right;\">".$val['transaction_in']." &nbsp;</div></td>
                            <td width=\"23%\"><div style=\"text-align: right;\">".number_format($val['last_balance'], 2)." &nbsp;</div></td>
                            <td width=\"20%\"><div style=\"text-align: center;\">".substr($val['operated_name'],0,5)."</div></td>
                        </tr>
                    ";
    
                    if($val['no'] == 7){
                        $tbl1 .= "
                            <tr>
                                <td></td>
                            </tr>
                        <tr>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                        </tr>
                        ";
                    }
    
                    if($val['no'] == 14){
                        $tbl1 .= "
                            <tr>
                                <td></td>
                            </tr>
    
                        ";
                    }
            }
        }

        $tbl2 = "</table>";

        $pdf::writeHTML($page.$tbl.$tbl1.$tbl2, true, false, false, false, '');

        $filename = 'Cetak Mutasi.pdf';

        $pdf::Output($filename, 'I');

    }
}
