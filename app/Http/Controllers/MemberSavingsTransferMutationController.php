<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\MemberSavingsTransferMutation\MemberSavingsTransferMutationDataTable;
use App\DataTables\MemberSavingsTransferMutation\CoreMemberDataTable;
use App\DataTables\MemberSavingsTransferMutation\SavingsAccountDataTable;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\AcctMutation;
use App\Models\CoreBranch;
use App\Models\CoreMember;
use App\Models\CoreMemberTransferMutation;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Helpers\Configuration;

class MemberSavingsTransferMutationController extends Controller
{
    public function index(MemberSavingsTransferMutationDataTable $dataTable)
    {
        session()->forget('session_member');
        session()->forget('datases_transfermutation');
        session()->forget('session_savingsaccount');
        $sessiondata = session()->get('filter_membersavingstransfermutation');
        $coremember = CoreMember::select('member_id','member_name','member_no')
        ->where('data_state',0)
        ->get();

        return $dataTable->render('content.MemberSavingsTransferMutation.List.index',compact('sessiondata','coremember'));
    }

    public function modalMember(CoreMemberDataTable $dataTable)
    {
        return $dataTable->render('content.MemberSavingsTransferMutation.Add.CoreMemberModal.index');
    }   

    public function modalSavingsAccount(SavingsAccountDataTable $dataTable)
    {
        return $dataTable->render('content.MemberSavingsTransferMutation.Add.SavingsAccountModal.index');
    }

    public function filter(Request $request){
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
            'member_id'  => $request->member_id
        );

        session()->put('filter_membersavingstransfermutation', $sessiondata);

        return redirect('member-savings-transfer-mutation');
    }

    public function filterReset(){
        session()->forget('filter_membersavingstransfermutation');

        return redirect('member-savings-transfer-mutation');
    }

    public function add()
    {
        $memberses = session()->get('session_member');
        $savingsaccount = session()->get('session_savingsaccount');
        $datases = session()->get('datases_transfermutation');
        $acctmutation = AcctMutation::select(DB::Raw('CONCAT(mutation_code, " - " ,mutation_name) AS mutation_name'), 'mutation_id')
        ->where('data_state', 0)
        ->where('mutation_module', 'WJB')
        ->first();

        return view('content.MemberSavingsTransferMutation.Add.index', compact('memberses','savingsaccount','acctmutation','datases'));
    }

    public function processAdd(Request $request)
    {
        $memberses = session()->get('session_member');
        $savingsaccount = session()->get('session_savingsaccount');

        $data = array(
            'branch_id'										=> auth()->user()->branch_id,
            'member_id'										=> $memberses['member_id'],
            'savings_id'									=> $savingsaccount['savings_id'],
            'savings_account_id'							=> $savingsaccount['savings_account_id'],
            'mutation_id'									=> $request->mutation_id,
            'member_transfer_mutation_date'					=> date('Y-m-d',strtotime($request->member_transfer_mutation_date)),
            'member_mandatory_savings_opening_balance'		=> $request->member_mandatory_savings_last_balance,
            'member_mandatory_savings'						=>$request->member_mandatory_savings,
            'member_mandatory_savings_last_balance'			=> $request->member_mandatory_savings_last_balance + $request->member_mandatory_savings,
            'operated_name'									=> auth()->user()->username,
            'created_id'									=> auth()->user()->user_id,
        );

        $transaction_module_code = "AGTTR";
        $transaction_module_id 	= PreferenceTransactionModule::where('transaction_module_code',$transaction_module_code)
        ->first()
        ->transaction_module_id;

        DB::beginTransaction();

        try {

            CoreMemberTransferMutation::create($data);

            $membertransfer_last 	= CoreMemberTransferMutation::select('core_member_transfer_mutation.member_transfer_mutation_id', 'core_member_transfer_mutation.member_id', 'core_member.member_name','core_member.member_no')
            ->join('core_member','core_member_transfer_mutation.member_id', '=', 'core_member.member_id')
            ->orderBy('core_member_transfer_mutation.member_transfer_mutation_id','DESC')
            ->where('core_member_transfer_mutation.created_id',$data['created_id'])
            ->first();
            $journal_voucher_period = date("Ym", strtotime($data['member_transfer_mutation_date']));
            
            $data_journal = array(
                'branch_id'						=> auth()->user()->branch_id,
                'journal_voucher_period' 		=> $journal_voucher_period,
                'journal_voucher_date'			=> $data['member_transfer_mutation_date'],
                'journal_voucher_title'			=> 'SETOR SIMPANAN WAJIB NON TUNAI '.$membertransfer_last->member_name,
                'journal_voucher_description'	=> 'SETOR SIMPANAN WAJIB NON TUNAI '.$membertransfer_last->member_name,
                'transaction_module_id'			=> $transaction_module_id,
                'transaction_module_code'		=> $transaction_module_code,
                'transaction_journal_id' 		=> $membertransfer_last->member_transfer_mutation_id,
                'transaction_journal_no' 		=> $membertransfer_last->member_no,
                'created_id' 					=> $data['created_id'],
            );

            AcctJournalVoucher::create($data_journal);

            $journal_voucher_id = AcctJournalVoucher::where('created_id',$data['created_id'])
            ->orderBy('journal_voucher_id', 'DESC')
            ->first()
            ->journal_voucher_id;
            $preferencecompany 	= PreferenceCompany::first();
            $account_id 		= AcctSavings::where('savings_id',$data['savings_id'])
            ->first()
            ->account_id;
            $account_id_default_status = AcctAccount::where('account_id',$account_id)
            ->where('data_state',0)
            ->first()
            ->account_default_status;

            $data_debet = array(
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $account_id,
                'journal_voucher_description'	=> 'SETOR SIMPANAN WAJIB NON TUNAI '.$memberses['member_name'],
                'journal_voucher_amount'		=> $data['member_mandatory_savings'],
                'journal_voucher_debit_amount'	=> $data['member_mandatory_savings'],
                'account_id_status'				=> 0,
                'created_id'					=> auth()->user()->user_id,
                'account_id_default_status'		=> $account_id_default_status,
            );

            AcctJournalVoucherItem::create($data_debet);

            $account_id = AcctSavings::where('savings_id', $preferencecompany['mandatory_savings_id'])
            ->first()
            ->account_id;
            $account_id_default_status = AcctAccount::where('account_id',$account_id)
            ->where('data_state',0)
            ->first()
            ->account_default_status;

            $data_credit =array(
                'journal_voucher_id'			=> $journal_voucher_id,
                'account_id'					=> $account_id,
                'journal_voucher_description'	=> 'SETOR SIMPANAN WAJIB NON TUNAI '.$memberses['member_name'],
                'journal_voucher_amount'		=> $data['member_mandatory_savings'],
                'journal_voucher_credit_amount'	=> $data['member_mandatory_savings'],
                'account_id_status'				=> 1,
                'created_id'					=> auth()->user()->user_id,
                'account_id_default_status'		=> $account_id_default_status,
            );

            AcctJournalVoucherItem::create($data_credit);

            DB::commit();
            $message = array(
                'pesan' => 'Data Debit Simpanan Wajib berhasil ditambah',
                'alert' => 'success'
            );
            return redirect('member-savings-transfer-mutation')->with($message);
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Data Debit Simpanan Wajib gagal ditambah',
                'alert' => 'error'
            );
            return redirect('member-savings-transfer-mutation/add')->with($message);
        }
    }

    public function selectMember($member_id)
    {
        $coremember = CoreMember::where('member_id', $member_id)
        ->join('core_city','core_city.city_id','=','core_member.city_id')
        ->join('core_kecamatan','core_kecamatan.kecamatan_id','=','core_member.kecamatan_id')
        ->first();

        $data = array(
            'member_id'                             => $coremember->member_id,
            'member_no'                             => $coremember->member_no,
            'member_name'                           => $coremember->member_name,
            'member_address'                        => $coremember->member_address,
            'city_name'                             => $coremember->city_name,
            'kecamatan_name'                        => $coremember->kecamatan_name,
            'member_mandatory_savings_last_balance' => $coremember->member_mandatory_savings_last_balance,
        );

        session()->put('session_member', $data);

        return redirect('member-savings-transfer-mutation/add');
    }

    public function selectSavingsAccount($savings_account_id)
    {
        $acctsavingsaccount = AcctSavingsAccount::where('acct_savings_account.savings_account_id', $savings_account_id)
        ->join('acct_savings','acct_savings.savings_id','=','acct_savings_account.savings_id')
        ->join('core_member','core_member.member_id','=','acct_savings_account.member_id')
        ->first();

        $data = array(
            'savings_id'                    => $acctsavingsaccount->savings_id,
            'savings_account_id'            => $savings_account_id,
            'savings_account_no'            => $acctsavingsaccount->savings_account_no,
            'savings_name'                  => $acctsavingsaccount->savings_name,
            'member_name'                   => $acctsavingsaccount->member_name,
            'savings_account_last_balance'  => $acctsavingsaccount->savings_account_last_balance,
        );

        session()->put('session_savingsaccount', $data);

        return redirect('member-savings-transfer-mutation/add');
    }

    public function elementsAdd(Request $request)
    {
        $datases = session()->get('datases_transfermutation');
        if(!$datases || $datases == ''){
            $datases['member_mandatory_savings'] = '';
        }
        $datases[$request->name] = $request->value;
        session()->put('datases_transfermutation', $datases);
    }

    public function resetElementsAdd()
    {
        session()->forget('session_member');
        session()->forget('session_savingsaccount');
        session()->forget('datases_transfermutation');

        return redirect('member-savings-transfer-mutation/add');
    }

    public function printMutation($member_transfer_mutation_id)
    {
        $preferencecompany 	= PreferenceCompany::first();
        $path = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $coremembertransfermutation	= CoreMemberTransferMutation::select('core_member_transfer_mutation.member_transfer_mutation_id', 'core_member_transfer_mutation.member_transfer_mutation_date', 'core_member_transfer_mutation.member_mandatory_savings', 'core_member_transfer_mutation.validation', 'core_member_transfer_mutation.validation_id', 'core_member_transfer_mutation.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member_transfer_mutation.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id as member_id_savings')
        ->join('acct_savings_account', 'core_member_transfer_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('core_member', 'core_member_transfer_mutation.member_id', '=', 'core_member.member_id')
        ->where('core_member_transfer_mutation.member_transfer_mutation_id', $member_transfer_mutation_id)
        ->first();


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

        $pdf::SetFont('helvetica', '', 12);

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td rowspan=\"2\" width=\"20%\"><img src=\"".$path."\" alt=\"\" width=\"700%\" height=\"300%\"/></td>
                <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">BUKTI TRANSFER SIMPANAN WAJIB</div></td>
            </tr>
            <tr>
                <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl, true, false, false, false, '');
        

        $tbl1 = "
        Telah diterima uang dari :
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$this->getMemberName($coremembertransfermutation->member_id_savings)."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">No. Rekening</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$coremembertransfermutation->savings_account_no."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$this->getMemberAddress($coremembertransfermutation->member_id_savings)."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".Configuration::numtotxt($coremembertransfermutation->member_mandatory_savings)."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: TRANSFER SIMPANAN WAJIB A.N. ".$coremembertransfermutation->member_name." (".$coremembertransfermutation->member_no.")</div></td>
            </tr>
                <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($coremembertransfermutation->member_mandatory_savings, 2)."</div></td>
            </tr>				
        </table>";

        $tbl2 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">".$this->getBranchCity(auth()->user()->branch_id).", ".date('d-m-Y')."</div></td>
            </tr>
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\">Penyetor</div></td>
                <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
            </tr>				
        </table>";

        $pdf::writeHTML($tbl1.$tbl2, true, false, false, false, '');

        $filename = 'Kwitansi.pdf';

        $pdf::Output($filename, 'I');
    }

    public function validation($member_transfer_mutation_id)
    {
        $data                   = CoreMemberTransferMutation::findOrFail($member_transfer_mutation_id);
        $data->validation       = 1;
        $data->validation_id    = auth()->user()->user_id;
        $data->validation_at    = date('Y-m-d H:i:s');

        if ($data->save()) {
            $message = array(
                'pesan' => 'Validasi Debit Simpanan Wajib berhasil',
                'alert' => 'success',
                'data'  => $member_transfer_mutation_id,
            );
            session()->flash('message', $message);
            return redirect('member-savings-transfer-mutation')->with($message);
        } else {
            $message = array(
                'pesan' => 'Validasi Debit Simpanan Wajib gagal',
                'alert' => 'error'
            );
            return redirect('member-savings-transfer-mutation')->with($message);
        }
    }
    
    public function printvalidation($member_transfer_mutation_id)
    {
        $preferencecompany 	= PreferenceCompany::first();
        $path = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $coremembertransfermutation	= CoreMemberTransferMutation::select('core_member_transfer_mutation.member_transfer_mutation_id', 'core_member_transfer_mutation.member_transfer_mutation_date', 'core_member_transfer_mutation.member_mandatory_savings', 'core_member_transfer_mutation.validation', 'core_member_transfer_mutation.validation_id', 'core_member_transfer_mutation.member_id', 'core_member.member_name', 'core_member.member_no', 'core_member_transfer_mutation.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings_account.member_id as member_id_savings')
        ->join('acct_savings_account', 'core_member_transfer_mutation.savings_account_id', '=', 'acct_savings_account.savings_account_id')
        ->join('core_member', 'core_member_transfer_mutation.member_id', '=', 'core_member.member_id')
        ->where('core_member_transfer_mutation.member_transfer_mutation_id', $member_transfer_mutation_id)
        ->first();

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

        $pdf::SetFont('helvetica', '', 12);

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">
            <tr>
                <td rowspan=\"2\" width=\"10%\"><img src=\"".$path."\" alt=\"\" width=\"1200%\" height=\"520%\"/></td>
            </tr>
        </table>
        <br/>
        <br/>
        <br/>
        <br/>";

        $pdf::writeHTML($tbl, true, false, false, false, '');

        $tbl1 = "
        <br><br><br><br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td width=\"55%\"><div style=\"text-align: right; font-size:14px\">".$coremembertransfermutation->savings_account_no."</div></td>
                <td width=\"45%\"><div style=\"text-align: right; font-size:14px\">".$this->getMemberName($coremembertransfermutation->member_id_savings)."</div></td>
            </tr>
            <tr>
                <td width=\"52%\"><div style=\"text-align: right; font-size:14px\">".$coremembertransfermutation->validation_at."</div></td>
                <td width=\"18%\"><div style=\"text-align: right; font-size:14px\">".$this->getUsername($coremembertransfermutation->validation_id)."</div></td>
                <td width=\"30%\"><div style=\"text-align: right; font-size:14px\"> IDR &nbsp; ".number_format($coremembertransfermutation->member_mandatory_savings, 2)."</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl1, true, false, false, false, '');
        
        $filename = 'Validasi.pdf';
        $pdf::Output($filename, 'I');
    }

    public function getBranchCity($branch_id)
    {
        $data = CoreBranch::where('branch_id', $branch_id)
        ->first();

        return $data['branch_city'];
    }

    public function getMemberName($member_id)
    {
        $data = CoreMember::where('member_id', $member_id)
        ->first();

        return $data->member_name;
    }

    public function getMemberAddress($member_id)
    {
        $data = CoreMember::where('member_id', $member_id)
        ->first();

        return $data->member_address;
    }

    public function getUsername($user_id)
    {
        $data = User::where('user_id', $user_id)
        ->first();

        return $data->username;
    }
}
