<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\MemberSavingsPayment\CoreMemberDataTable;
use App\Models\AcctAccount;
use App\Models\AcctSavingsMemberDetail;
use App\Models\AcctSavings;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctMutation;
use App\Models\CoreBranch;
use App\Models\CoreMember;
use App\Models\CoreProvince;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Helpers\Configuration;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;

class MemberSavingsPaymentController extends Controller
{
    public function index()
    {
        $membercharacter = array_filter(Configuration::MemberCharacter());
        $memberidentity = array_filter(Configuration::MemberIdentity());
        $membergender = array_filter(Configuration::MemberGender());
        $coreprovince = CoreProvince::select('province_id', 'province_name')
        ->where('data_state', 0)
        ->get();
        $acctmutation = AcctMutation::select('mutation_id', 'mutation_name','mutation_function')
        ->where('data_state', 0)
        ->where('mutation_module', 'TAB')
        ->get();
        $memberses = session()->get('memberses');
        $datases = session()->get('datases');
        return view('content.MemberSavingsPayment.Edit.index',compact('membercharacter','memberidentity','membergender','coreprovince','acctmutation','memberses','datases'));
    }

    public function modalMember(CoreMemberDataTable $dataTable)
    {
        return $dataTable->render('content.MemberSavingsPayment.Edit.CoreMemberModal.index');
    }

    public function selectMember($member_id)
    {
        $data_member = CoreMember::where('data_state', 0)
        ->where('member_id', $member_id)
        ->first();

        $memberses = array(
            'member_id'                                 => $data_member['member_id'],
            'member_no'                                 => $data_member['member_no'],
            'member_name'                               => $data_member['member_name'],
            'member_character'                          => $data_member['member_character'],
            'province_id'                               => $data_member['province_id'],
            'city_id'                                   => $data_member['city_id'],
            'kecamatan_id'                              => $data_member['kecamatan_id'],
            'kelurahan_id'                              => $data_member['kelurahan_id'],
            'member_address'                            => $data_member['member_address'],
            'member_principal_savings_last_balance'     => $data_member['member_principal_savings_last_balance'],
            'member_special_savings_last_balance'       => $data_member['member_special_savings_last_balance'],
            'member_mandatory_savings_last_balance'     => $data_member['member_mandatory_savings_last_balance'],
        );

        session()->put('memberses', $memberses);

        return redirect('member-savings-payment');
    }

    public function processEdit(Request $request)
    {
        $data = array(
            'member_id'								=> session()->get('memberses')['member_id'],
            'member_name'							=> session()->get('memberses')['member_name'],
            'member_address'						=> $request->member_address,
            'mutation_id'							=> $request->mutation_id,
            'province_id'						    => $request->province_id,
            'city_id'								=> $request->city_id,
            'kecamatan_id'							=> $request->kecamatan_id,
            'kelurahan_id'							=> $request->kelurahan_id,
            'member_character'						=> $request->member_character,
            'member_principal_savings'				=> $request->member_principal_savings,
            'member_special_savings'				=> $request->member_special_savings,
            'member_mandatory_savings'				=> $request->member_mandatory_savings,
            'member_principal_savings_last_balance'	=> $request->member_principal_savings_last_balance,
            'member_special_savings_last_balance'	=> $request->member_special_savings_last_balance,
            'member_mandatory_savings_last_balance'	=> $request->member_mandatory_savings_last_balance,
            'updated_id'                            => auth()->user()->user_id,
        );


        $data_session = array(
            'member_id'                                 => $data['member_id'],
            'member_no'                                 => session()->get('memberses')['member_no'],
            'member_name'                               => $data['member_name'],
            'member_character'                          => $data['member_character'],
            'province_id'                               => $data['province_id'],
            'city_id'                                   => $data['city_id'],
            'kecamatan_id'                              => $data['kecamatan_id'],
            'kelurahan_id'                              => $data['kelurahan_id'],
            'member_address'                            => $data['member_address'],
            'member_principal_savings_last_balance'     => $data['member_principal_savings_last_balance'],
            'member_special_savings_last_balance'       => $data['member_special_savings_last_balance'],
            'member_mandatory_savings_last_balance'     => $data['member_mandatory_savings_last_balance'],
        );

        session()->put('memberses', $data_session);

        $total_cash_amount = $data['member_principal_savings'] + $data['member_special_savings'] + $data['member_mandatory_savings'];

        DB::beginTransaction();

        try {
            CoreMember::where('member_id', $data['member_id'])
            ->update([
                'member_name'							=> $data['member_name'],
                'member_address'						=> $data['member_address'],
                'province_id'							=> $data['province_id'],
                'city_id'								=> $data['city_id'],
                'kecamatan_id'							=> $data['kecamatan_id'],
                'kelurahan_id'							=> $data['kelurahan_id'],
                'member_character'						=> $data['member_character'],
                'member_principal_savings'				=> $data['member_principal_savings'],
                'member_special_savings'				=> $data['member_special_savings'],
                'member_mandatory_savings'				=> $data['member_mandatory_savings'],
                'member_principal_savings_last_balance'	=> $data['member_principal_savings_last_balance'],
                'member_special_savings_last_balance'	=> $data['member_special_savings_last_balance'],
                'member_mandatory_savings_last_balance'	=> $data['member_mandatory_savings_last_balance'],
                'updated_id'                            => $data['updated_id'],
            ]);

            if($data['member_principal_savings'] <> 0 || $data['member_principal_savings'] <> '' || $data['member_mandatory_savings'] <> 0 || $data['member_mandatory_savings'] <> ''  || $data['member_special_savings'] <> 0 || $data['member_special_savings'] <> ''){

                $data_detail = array (
                    'branch_id'						=> auth()->user()->branch_id,
                    'member_id'						=> $data['member_id'],
                    'mutation_id'					=> $data['mutation_id'],
                    'transaction_date'				=> date('Y-m-d'),
                    'principal_savings_amount'		=> $data['member_principal_savings'],
                    'special_savings_amount'		=> $data['member_special_savings'],
                    'mandatory_savings_amount'		=> $data['member_mandatory_savings'],
                    'operated_name'					=> auth()->user()->username,
                    'created_id'                    => auth()->user()->user_id,
                );

                if(AcctSavingsMemberDetail::create($data_detail)){
                    $transaction_module_code 	= "AGT";

                    $transaction_module_id 		= PreferenceTransactionModule::where('transaction_module_code',$transaction_module_code)
                    ->first()
                    ->transaction_module_id;
                    $preferencecompany 			= PreferenceCompany::first();
                    $coremember 				= CoreMember::where('data_state', 0)
                    ->where('member_id', $data['member_id'])
                    ->first();

                    $journal_voucher_period 	= date("Ym", strtotime($coremember->member_register_date));

                    //-------------------------Jurnal Cabang----------------------------------------------------

                    $data_journal_cabang = array(
                        'branch_id'						=> auth()->user()->branch_id,
                        'journal_voucher_period' 		=> $journal_voucher_period,
                        'journal_voucher_date'			=> date('Y-m-d'),
                        'journal_voucher_title'			=> 'MUTASI ANGGOTA TUNAI '.$coremember->member_name,
                        'journal_voucher_description'	=> 'MUTASI ANGGOTA TUNAI '.$coremember->member_name,
                        'transaction_module_id'			=> $transaction_module_id,
                        'transaction_module_code'		=> $transaction_module_code,
                        'transaction_journal_id' 		=> $coremember->member_id,
                        'transaction_journal_no' 		=> $coremember->member_no,
                        'created_id' 					=> auth()->user()->user_id,
                    );

                    AcctJournalVoucher::create($data_journal_cabang);

                    $journal_voucher_id 			= AcctJournalVoucher::where('created_id',auth()->user()->user_id)
                    ->orderBy('journal_voucher_id', 'DESC')
                    ->first()
                    ->journal_voucher_id;

                    if($data_detail['mutation_id'] == $preferencecompany->cash_deposit_id){

                        $account_id_default_status 	= AcctAccount::where('account_id',$preferencecompany->account_cash_id)
                        ->where('data_state',0)
                        ->first()
                        ->account_default_status;

                        $data_debet = array (
                            'journal_voucher_id'			=> $journal_voucher_id,
                            'account_id'					=> $preferencecompany->account_cash_id,
                            'journal_voucher_description'	=> 'SETORAN TUNAI '.$coremember->member_name,
                            'journal_voucher_amount'		=> $total_cash_amount,
                            'journal_voucher_debit_amount'	=> $total_cash_amount,
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 0,
                            'created_id' 					=> auth()->user()->user_id,
                        );

                        AcctJournalVoucherItem::create($data_debet);

                        if($data['member_principal_savings'] <> 0 || $data['member_principal_savings'] <> ''){
                            $account_id = AcctSavings::where('savings_id',$preferencecompany->principal_savings_id)
                            ->first()
                            ->account_id;

                            $account_id_default_status = AcctAccount::where('account_id',$account_id)
                            ->first()
                            ->account_default_status;

                            $data_credit =array(
                                'journal_voucher_id'			=> $journal_voucher_id,
                                'account_id'					=> $account_id,
                                'journal_voucher_description'	=> 'SETORAN TUNAI '.$coremember->member_name,
                                'journal_voucher_amount'		=> $data['member_principal_savings'],
                                'journal_voucher_credit_amount'	=> $data['member_principal_savings'],
                                'account_id_default_status'		=> $account_id_default_status,
                                'account_id_status'				=> 1,
                                'created_id' 					=> auth()->user()->user_id,
                            );

                            AcctJournalVoucherItem::create($data_credit);
                        }

                        if($data['member_mandatory_savings'] <> 0 || $data['member_mandatory_savings'] <> ''){
                            $account_id = AcctSavings::where('savings_id',$preferencecompany->mandatory_savings_id)
                            ->first()
                            ->account_id;

                            $account_id_default_status = AcctAccount::where('account_id',$account_id)
                            ->first()
                            ->account_default_status;

                            $data_credit =array(
                                'journal_voucher_id'			=> $journal_voucher_id,
                                'account_id'					=> $account_id,
                                'journal_voucher_description'	=> 'SETORAN TUNAI '.$coremember->member_name,
                                'journal_voucher_amount'		=> $data['member_mandatory_savings'],
                                'journal_voucher_credit_amount'	=> $data['member_mandatory_savings'],
                                'account_id_default_status'		=> $account_id_default_status,
                                'account_id_status'				=> 1,
                                'created_id' 					=> auth()->user()->user_id,
                            );

                            AcctJournalVoucherItem::create($data_credit);
                        }

                        if($data['member_special_savings'] <> 0 || $data['member_special_savings'] <> ''){
                            $account_id =  AcctSavings::where('savings_id',$preferencecompany->special_savings_id)
                            ->first()
                            ->account_id;

                            $account_id_default_status = AcctAccount::where('account_id',$account_id)
                            ->first()
                            ->account_default_status;

                            $data_credit =array(
                                'journal_voucher_id'			=> $journal_voucher_id,
                                'account_id'					=> $account_id,
                                'journal_voucher_description'	=> 'SETORAN TUNAI '.$coremember->member_name,
                                'journal_voucher_amount'		=> $data['member_special_savings'],
                                'journal_voucher_credit_amount'	=> $data['member_special_savings'],
                                'account_id_default_status'		=> $account_id_default_status,
                                'account_id_status'				=> 1,
                                'created_id' 					=> auth()->user()->user_id,
                            );

                            AcctJournalVoucherItem::create($data_credit);
                        }
                    } else {
                        if($data['member_principal_savings'] <> 0 || $data['member_principal_savings'] <> ''){

                            $mutation_type = '';
                            if($data_detail['mutation_id'] == 2){
                                $mutation_type = 'PENARIKAN TUNAI';
                            }else if($data_detail['mutation_id'] == 3){
                                $mutation_type = 'KOREKSI KREDIT';
                            }else if($data_detail['mutation_id'] == 4){
                                $mutation_type = 'KOREKSI DEBET';
                            }else{
                                $mutation_type = 'TUTUP REKENING'; //masuk else
                            }

                            $account_id = AcctSavings::where('savings_id',$preferencecompany->principal_savings_id)
                            ->first()
                            ->account_id;

                            $account_id_default_status = AcctAccount::where('account_id',$account_id)
                            ->first()
                            ->account_default_status;

                            $data_debet =array(
                                'journal_voucher_id'			=> $journal_voucher_id,
                                'account_id'					=> $account_id,
                                'journal_voucher_description'	=> $mutation_type.' '.$coremember->member_name,
                                'journal_voucher_amount'		=> $data['member_principal_savings'],
                                'journal_voucher_debit_amount'	=> $data['member_principal_savings'],
                                'account_id_default_status'		=> $account_id_default_status,
                                'account_id_status'				=> 0,
                                'created_id' 					=> auth()->user()->user_id,
                            );

                            AcctJournalVoucherItem::create($data_debet);
                        }

                        if($data['member_mandatory_savings'] <> 0 || $data['member_mandatory_savings'] <> ''){
                            $account_id = AcctSavings::where('savings_id',$preferencecompany->mandatory_savings_id)
                            ->first()
                            ->account_id;

                            $account_id_default_status = AcctAccount::where('account_id',$account_id)
                            ->first()
                            ->account_default_status;

                            $data_debet =array(
                                'journal_voucher_id'			=> $journal_voucher_id,
                                'account_id'					=> $account_id,
                                'journal_voucher_description'	=> 'PENARIKAN TUNAI '.$coremember->member_name,
                                'journal_voucher_amount'		=> $data['member_mandatory_savings'],
                                'journal_voucher_debit_amount'	=> $data['member_mandatory_savings'],
                                'account_id_default_status'		=> $account_id_default_status,
                                'account_id_status'				=> 0,
                                'created_id' 					=> auth()->user()->user_id,
                            );

                            AcctJournalVoucherItem::create($data_debet);
                        }

                        if($data['member_special_savings'] <> 0 || $data['member_special_savings'] <> ''){
                            $account_id = AcctSavings::where('savings_id',$preferencecompany->special_savings_id)
                            ->first()
                            ->account_id;

                            $account_id_default_status = AcctAccount::where('account_id',$account_id)
                            ->first()
                            ->account_default_status;

                            $data_debet =array(
                                'journal_voucher_id'			=> $journal_voucher_id,
                                'account_id'					=> $account_id,
                                'journal_voucher_description'	=> 'PENARIKAN TUNAI '.$coremember->member_name,
                                'journal_voucher_amount'		=> $data['member_special_savings'],
                                'journal_voucher_debit_amount'	=> $data['member_special_savings'],
                                'account_id_default_status'		=> $account_id_default_status,
                                'account_id_status'				=> 0,
                                'created_id' 					=> auth()->user()->user_id,
                            );

                            AcctJournalVoucherItem::create($data_debet);
                        }

                        $account_id_default_status = AcctAccount::where('account_id',$preferencecompany->account_cash_id)
                        ->first()
                        ->account_default_status;

                        $data_credit = array (
                            'journal_voucher_id'			=> $journal_voucher_id,
                            'account_id'					=> $preferencecompany->account_cash_id,
                            'journal_voucher_description'	=> 'PENARIKAN TUNAI '.$coremember->member_name,
                            'journal_voucher_amount'		=> $total_cash_amount,
                            'journal_voucher_credit_amount'	=> $total_cash_amount,
                            'account_id_default_status'		=> $account_id_default_status,
                            'account_id_status'				=> 1,
                            'created_id' 					=> auth()->user()->user_id,
                        );

                        AcctJournalVoucherItem::create($data_credit);
                    }
                }
            }

            DB::commit();
            $message = array(
                'pesan' => 'Data Anggota berhasil diubah',
                'alert' => 'success',
                'member_id' => $data['member_id']
            );
            session()->forget('datases');
            session()->forget('memberses');
            session()->flash('message', $message);
            return redirect('member-savings-payment')->with($message);
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Data Anggota gagal diubah',
                'alert' => 'error'
            );
            return redirect('member-savings-payment')->with($message);
        }

    }

    public function elementsAdd(Request $request)
    {
        $datases = session()->get('datases');
        if(!$datases || $datases == ''){
            $datases['mutation_id']                 = '';
            $datases['member_principal_savings']    = '';
            $datases['member_special_savings']      = '';
            $datases['member_mandatory_savings']    = '';
        }
        $datases[$request->name] = $request->value;
        session()->put('datases', $datases);
    }

    public function resetElementsAdd()
    {
        session()->forget('datases');
        session()->forget('memberses');

        return redirect('member-savings-payment');
    }

    public function processPrinting($member_id)
    {
        $branch_id 				    = auth()->user()->branch_id;
        $preferencecompany 			= PreferenceCompany::first();
        $path                       = public_path('storage/'.$preferencecompany['logo_koperasi']);
        $acctsavingsmemberdetail	= AcctSavingsMemberDetail::select('acct_savings_member_detail.savings_member_detail_id','acct_savings_member_detail.member_id','core_member.member_no','core_member.member_name','core_member.member_address','acct_savings_member_detail.branch_id','acct_savings_member_detail.mutation_id','acct_mutation.mutation_code','acct_savings_member_detail.transaction_date','acct_savings_member_detail.principal_savings_amount','acct_savings_member_detail.special_savings_amount','acct_savings_member_detail.mandatory_savings_amount','acct_savings_member_detail.last_balance','acct_savings_member_detail.operated_name')
        ->join('core_member', 'acct_savings_member_detail.member_id','=','core_member.member_id')
        ->join('acct_mutation', 'acct_savings_member_detail.mutation_id','=','acct_mutation.mutation_id')
        ->where('acct_savings_member_detail.member_id', $member_id)
        ->orderBy('acct_savings_member_detail.savings_member_detail_id', 'DESC')
        ->first();


        if($acctsavingsmemberdetail->mutation_id == $preferencecompany->cash_deposit_id){
            $keperluan = 'SETORAN TUNAI';
            $keterangan = 'Telah diterima uang dari';
        } else if($acctsavingsmemberdetail->mutation_id == $preferencecompany->cash_withdrawal_id){
            $keperluan = 'PENARIKAN TUNAI';
            $keterangan = 'Telah diserahkan uang kepada';
        }

        $total = $acctsavingsmemberdetail->principal_savings_amount + $acctsavingsmemberdetail->mandatory_savings_amount + $acctsavingsmemberdetail->special_savings_amount;

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

        // $img = "<img src=\"".asset('images/'.$preferencecompany->logo_koperasi)."\" alt=\"\" width=\"700%\" height=\"300%\"/>";

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
            <tr>
                <td rowspan=\"2\" width=\"20%\"><img src=\"".$path."\" alt=\"\" width=\"700%\" height=\"300%\"/></td>
                <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">BUKTI ".$keperluan." ANGGOTA</div></td>
            </tr>
            <tr>
                <td width=\"40%\"><div style=\"text-align: left; font-size:14px\">Jam : ".date('H:i:s')."</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl, true, false, false, false, '');


        $tbl1 =
        $keterangan .":
        <br>
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Nama</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingsmemberdetail->member_name."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">No. Rekening</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingsmemberdetail->member_no."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Alamat</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$acctsavingsmemberdetail->member_address."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Keperluan</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".$keperluan."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Simp. Pokok</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctsavingsmemberdetail->principal_savings_amount, 2)."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Simp. Khusus</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($acctsavingsmemberdetail->special_savings_amount, 2)."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Simp. Wajib</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\"><u>: Rp. &nbsp;".number_format($acctsavingsmemberdetail->mandatory_savings_amount, 2)."</u></div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Jumlah</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: Rp. &nbsp;".number_format($total, 2)."</div></td>
            </tr>
            <tr>
                <td width=\"20%\"><div style=\"text-align: left;\">Terbilang</div></td>
                <td width=\"80%\"><div style=\"text-align: left;\">: ".Configuration::numtotxt($total)."</div></td>
            </tr>
        </table>";

        $tbl2 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">".$this->getBranchCity($branch_id).", ".date('d-m-Y')."</div></td>
            </tr>
            <tr>
                <td width=\"30%\"><div style=\"text-align: center;\">Penyetor</div></td>
                <td width=\"20%\"><div style=\"text-align: center;\"></div></td>
                <td width=\"30%\"><div style=\"text-align: center;\">Teller/Kasir</div></td>
            </tr>
        </table>";

        $pdf::writeHTML($tbl1.$tbl2, true, false, false, false, '');
        $filename = 'Kwitansi_Simpanan_Anggota_'.$acctsavingsmemberdetail->member_name.'.pdf';
        $pdf::Output($filename, 'I');
    }

    public function getBranchCity($branch_id)
    {
        $data = CoreBranch::where('branch_id', $branch_id)
        ->first();

        return $data['branch_city'];
    }
}
