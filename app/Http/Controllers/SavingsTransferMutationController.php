<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\SavingsTransferMutation\SavingsTransferMutationDataTable;
use App\DataTables\SavingsTransferMutation\SavingsAccountFromDataTable;
use App\DataTables\SavingsTransferMutation\SavingsAccountToDataTable;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctMutation;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctSavingsTransferMutation;
use App\Models\AcctSavingsTransferMutationFrom;
use App\Models\AcctSavingsTransferMutationTo;
use App\Models\CoreMember;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;

class SavingsTransferMutationController extends Controller
{
    public function index(SavingsTransferMutationDataTable $dataTable)
    {
        session()->forget('session_savingsaccountfrom');
        session()->forget('session_savingsaccountto');
        session()->forget('session_savingstransfermutation');
        $sessiondata = session()->get('filter_savingstransfermutation');

        return $dataTable->render('content.SavingsTransferMutation.List.index', compact('sessiondata'));
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

        session()->put('filter_savingstransfermutation', $sessiondata);

        return redirect('savings-transfer-mutation');
    }

    public function resetFilter()
    {
        session()->forget('filter_savingstransfermutation');

        return redirect('savings-transfer-mutation');
    }

    public function add()
    {
        $savingsaccountfrom = session()->get('session_savingsaccountfrom');
        $savingsaccountto = session()->get('session_savingsaccountto');
        $session = session()->get('session_savingstransfermutation');
        $acctmutation = AcctMutation::select(DB::Raw('CONCAT(mutation_code, " - " ,mutation_name) AS mutation_name'),'mutation_id') 
        ->where('data_state', 0)
        ->where('mutation_module', 'TR')
        ->first();

        return view('content.SavingsTransferMutation.Add.index', compact('savingsaccountfrom','savingsaccountto','acctmutation','session'));
    }

    public function modalSavingsAccountFrom(SavingsAccountFromDataTable $dataTable)
    {
        return $dataTable->render('content.SavingsTransferMutation.Add.SavingsAccountFromModal.index');
    }

    public function modalSavingsAccountTo(SavingsAccountToDataTable $dataTable)
    {
        return $dataTable->render('content.SavingsTransferMutation.Add.SavingsAccountToModal.index');
    }

    public function selectSavingsAccountFrom($savings_account_id)
    {
        $acctsavingsaccount = AcctSavingsAccount::where('acct_savings_account.savings_account_id', $savings_account_id)
        ->join('acct_savings','acct_savings.savings_id','=','acct_savings_account.savings_id')
        ->join('core_member','core_member.member_id','=','acct_savings_account.member_id')
        ->join('core_city', 'core_member.city_id','=','core_city.city_id')
        ->join('core_kecamatan', 'core_member.kecamatan_id','=','core_kecamatan.kecamatan_id')
        ->first();

        $data = array(
            'savings_id'                    => $acctsavingsaccount->savings_id,
            'savings_account_id'            => $savings_account_id,
            'savings_account_no'            => $acctsavingsaccount->savings_account_no,
            'savings_name'                  => $acctsavingsaccount->savings_name,
            'member_name'                   => $acctsavingsaccount->member_name,
            'member_id'                     => $acctsavingsaccount->member_id,
            'member_address'                => $acctsavingsaccount->member_address,
            'city_name'                     => $acctsavingsaccount->city_name,
            'kecamatan_name'                => $acctsavingsaccount->kecamatan_name,
            'savings_account_last_balance'  => $acctsavingsaccount->savings_account_last_balance,
            'savings_account_pickup_date'   => $acctsavingsaccount->savings_account_pickup_date,
            'unblock_state'   => $acctsavingsaccount->unblock_state,
        );

      
        session()->put('session_savingsaccountfrom', $data);

        return redirect('savings-transfer-mutation/add');
    }

    public function selectSavingsAccountTo($savings_account_id)
    {
        $acctsavingsaccount = AcctSavingsAccount::where('acct_savings_account.savings_account_id', $savings_account_id)
        ->join('acct_savings','acct_savings.savings_id','=','acct_savings_account.savings_id')
        ->join('core_member','core_member.member_id','=','acct_savings_account.member_id')
        ->join('core_city', 'core_member.city_id','=','core_city.city_id')
        ->join('core_kecamatan', 'core_member.kecamatan_id','=','core_kecamatan.kecamatan_id')
        ->first();

        $data = array(
            'savings_id'                    => $acctsavingsaccount->savings_id,
            'savings_account_id'            => $savings_account_id,
            'savings_account_no'            => $acctsavingsaccount->savings_account_no,
            'savings_name'                  => $acctsavingsaccount->savings_name,
            'member_name'                   => $acctsavingsaccount->member_name,
            'member_id'                     => $acctsavingsaccount->member_id,
            'member_address'                => $acctsavingsaccount->member_address,
            'city_name'                     => $acctsavingsaccount->city_name,
            'kecamatan_name'                => $acctsavingsaccount->kecamatan_name,
            'savings_account_last_balance'  => $acctsavingsaccount->savings_account_last_balance,
        );

        session()->put('session_savingsaccountto', $data);

        return redirect('savings-transfer-mutation/add');
    }
    
    public function elementsAdd(Request $request)   
    {
        $datases = session()->get('session_savingstransfermutation');
        if(!$datases || $datases == ''){
            $datases['savings_transfer_mutation_amount']  = '';
        }
        $datases[$request->name] = $request->value;
        session()->put('session_savingstransfermutation', $datases);
    }

    public function resetElementsAdd()
    {
        session()->forget('session_savingsaccountfrom');
        session()->forget('session_savingsaccountto');
        session()->forget('session_savingstransfermutation');

        return redirect('savings-transfer-mutation/add');
    }

    public function processAdd(Request $request)
    {
        $savingsaccountfrom = session()->get('session_savingsaccountfrom');
        $savingsaccountto = session()->get('session_savingsaccountto');
        $data = array(
            'branch_id'								=> auth()->user()->branch_id,
            'savings_transfer_mutation_date'		=> date('Y-m-d'),
            'savings_transfer_mutation_amount'		=> $request->savings_transfer_mutation_amount,
            'operated_name'							=> auth()->user()->username,
            'created_id'							=> auth()->user()->user_id,
        );

        DB::beginTransaction();

        try {

            AcctSavingsTransferMutation::create($data);

            $transaction_module_code = "TRTAB";
            $transaction_module_id 	= PreferenceTransactionModule::where('transaction_module_code',$transaction_module_code)
            ->first()
            ->transaction_module_id;
            $acctsavingstr_last 	= AcctSavingsTransferMutation::select('acct_savings_transfer_mutation.savings_transfer_mutation_id', 'acct_savings_transfer_mutation_from.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_transfer_mutation_from.member_id', 'core_member.member_name')
            ->join('acct_savings_transfer_mutation_from','acct_savings_transfer_mutation.savings_transfer_mutation_id','=','acct_savings_transfer_mutation_from.savings_transfer_mutation_id')
            ->join('acct_savings_account','acct_savings_transfer_mutation_from.savings_account_id','=','acct_savings_account.savings_account_id')
            ->join('core_member','acct_savings_transfer_mutation_from.member_id','=','core_member.member_id')
            ->where('acct_savings_transfer_mutation.created_id', $data['created_id'])
            ->orderBy('acct_savings_transfer_mutation.savings_transfer_mutation_id','DESC')
            ->first();

                
            $journal_voucher_period = date("Ym", strtotime($data['savings_transfer_mutation_date']));
            
            $data_journal = array(
                'branch_id'						=> auth()->user()->branch_id,
                'journal_voucher_period' 		=> $journal_voucher_period,
                'journal_voucher_date'			=> date('Y-m-d'),
                'journal_voucher_title'			=> 'TRANSFER ANTAR REKENING '.$acctsavingstr_last->member_name,
                'journal_voucher_description'	=> 'TRANSFER ANTAR REKENING '.$acctsavingstr_last->member_name,
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $acctsavingstr_last->savings_transfer_mutation_id,
                'transaction_journal_no' 		=> $acctsavingstr_last->savings_account_no,
                'created_id' 					=> $data['created_id'],
            );

            AcctJournalVoucher::create($data_journal);

            $journal_voucher_id = AcctJournalVoucher::where('created_id', $data['created_id'])
            ->orderBy('journal_voucher_id', 'DESC')
            ->first()
            ->journal_voucher_id;

            $savings_transfer_mutation_id = AcctSavingsTransferMutation::where('created_id', $data['created_id'])
            ->orderBy('savings_transfer_mutation_id','DESC')
            ->first()
            ->savings_transfer_mutation_id;

            $preferencecompany = PreferenceCompany::first();

            $datafrom = array (
                'savings_transfer_mutation_id'				=> $savings_transfer_mutation_id,
                'savings_account_id'						=> $savingsaccountfrom['savings_account_id'],
                'savings_id'								=> $savingsaccountfrom['savings_id'],
                'member_id'									=> $savingsaccountfrom['member_id'],
                'branch_id'									=> auth()->user()->branch_id,
                'mutation_id'								=> $preferencecompany['account_savings_transfer_from_id'],
                'savings_account_opening_balance'			=> $request->savings_account_from_opening_balance,
                'savings_transfer_mutation_from_amount'		=> $request->savings_transfer_mutation_amount,
                'savings_account_last_balance'				=> $request->savings_account_from_last_balance,
            );

            $member_name = CoreMember::where('member_id', $datafrom['member_id'])
            ->first()
            ->member_name;

            if(AcctSavingsTransferMutationFrom::create($datafrom)){
                $account_id = AcctSavings::where('savings_id',$datafrom['savings_id'])
                ->first()
                ->account_id;

                $account_id_default_status = AcctAccount::where('account_id',$account_id)
                ->where('data_state',0)
                ->first()
                ->account_default_status;

                $data_debet =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_id,
                    'journal_voucher_description'	=> 'NOTA DEBET '.$member_name,
                    'journal_voucher_amount'		=> $data['savings_transfer_mutation_amount'],
                    'journal_voucher_debit_amount'	=> $data['savings_transfer_mutation_amount'],
                    'account_id_status'				=> 1,
                    'created_id'					=> $data['created_id'],
                );

                AcctJournalVoucherItem::create($data_debet);
            }

            $datato = array (
                'savings_transfer_mutation_id'				=> $savings_transfer_mutation_id,
                'savings_account_id'						=> $savingsaccountto['savings_account_id'],
                'savings_id'								=> $savingsaccountto['savings_id'],
                'member_id'									=> $savingsaccountto['member_id'],
                'branch_id'									=> auth()->user()->branch_id,
                'mutation_id'								=> $preferencecompany['account_savings_transfer_to_id'],
                'savings_account_opening_balance'			=> $request->savings_account_to_opening_balance,
                'savings_transfer_mutation_to_amount'		=> $request->savings_transfer_mutation_amount,
                'savings_account_last_balance'				=> $request->savings_account_to_last_balance,
            );

            $member_name = CoreMember::where('member_id', $datato['member_id'])
            ->first()
            ->member_name;

            if(AcctSavingsTransferMutationTo::create($datato)){
                $account_id = AcctSavings::where('savings_id',$datato['savings_id'])
                ->first()
                ->account_id;

                $account_id_default_status = AcctAccount::where('account_id',$account_id)
                ->first()
                ->account_default_status;

                $data_credit =array(
                    'journal_voucher_id'			=> $journal_voucher_id,
                    'account_id'					=> $account_id,
                    'journal_voucher_description'	=> 'NOTA KREDIT '.$member_name,
                    'journal_voucher_amount'		=> $data['savings_transfer_mutation_amount'],
                    'journal_voucher_credit_amount'	=> $data['savings_transfer_mutation_amount'],
                    'account_id_status'				=> 0,
                    'created_id'					=> $data['created_id'],
                );

                AcctJournalVoucherItem::create($data_credit);
            }
            
            DB::commit();
            $message = array(
                'pesan' => 'Transfer Antar Rekening berhasil ditambah',
                'alert' => 'success',
            );
            return redirect('savings-transfer-mutation')->with($message);
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Transfer Antar Rekening gagal ditambah',
                'alert' => 'error'
            );
            return redirect('savings-transfer-mutation')->with($message);
        }
    }

    public function validation($savings_transfer_mutation_id)
    {
        $table                  = AcctSavingsTransferMutation::findOrFail($savings_transfer_mutation_id);
        $table->validation      = 1;
        $table->validation_id   = auth()->user()->user_id;
        $table->updated_id      = auth()->user()->user_id;

        if ($table->save()) {
            $message = array(
                'pesan'                         => 'Validasi Transfer Antar Rekening berhasil',
                'alert'                         => 'success',
                'savings_transfer_mutation_id'  => $savings_transfer_mutation_id,
            );
            session()->flash('message', $message);
            return redirect('savings-transfer-mutation')->with($message);
        } else {
            $message = array(
                'pesan' => 'Validasi Transfer Antar Rekening gagal',
                'alert' => 'error'
            );
            return redirect('savings-transfer-mutation')->with($message);
        }
    }

    public function printValidation($savings_transfer_mutation_id)
    {
        $acctsavingstransfermutation	= AcctSavingsTransferMutation::select('updated_at','validation_id','savings_transfer_mutation_amount')
        ->where('savings_transfer_mutation_id', $savings_transfer_mutation_id)
        ->where('data_state',0)
        ->first();

        $acctsavingstransfermutationfrom = AcctSavingsTransferMutationFrom::select('savings_account_id','member_id')
        ->where('savings_transfer_mutation_id', $savings_transfer_mutation_id)
        ->first();

        $preferencecompany				= PreferenceCompany::first();
        $path                           = public_path('storage/'.$preferencecompany['logo_koperasi']);

        $pdf = new tcpdf('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

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

        $pdf::SetFont('helveticaI', '', 7);

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
                <td width=\"55%\"><div style=\"text-align: right; font-size:14px\">".$this->getSavingsAccountNo($acctsavingstransfermutationfrom['savings_account_id'])."</div></td>
                <td width=\"45%\"><div style=\"text-align: right; font-size:14px\">".$this->getMemberName($acctsavingstransfermutationfrom['member_id'])."</div></td>
            </tr>
            <tr>
                <td width=\"52%\"><div style=\"text-align: right; font-size:14px\">".$acctsavingstransfermutation['validation_on']."</div></td>
                <td width=\"18%\"><div style=\"text-align: right; font-size:14px\">".$this->getUsername($acctsavingstransfermutation['validation_id'])."</div></td>
                <td width=\"30%\"><div style=\"text-align: right; font-size:14px\"> IDR &nbsp; ".number_format($acctsavingstransfermutation['savings_transfer_mutation_amount'], 2)."</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl, true, false, false, false, '');
        
        $filename = 'Validasi.pdf';
        $pdf::Output($filename, 'I');
    }

    public function getSavingsAccountNo($savings_account_id)
    {
        $data = AcctSavingsAccount::where('savings_account_id', $savings_account_id)
        ->where('data_state',0)
        ->first();

        return $data->savings_account_no;
    }

    public function getMemberName($member_id)
    {
        $data = CoreMember::where('member_id', $member_id)
        ->where('data_state',0)
        ->first();

        return $data->member_name;
    }

    public function getUsername($user_id)
    {
        $data = User::where('user_id', $user_id)
        ->where('data_state',0)
        ->first();

        return $data->username;
    }
}

