<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\MemberSavingsDebetPrincipal\CoreMemberDataTable;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use App\Models\AcctMutation;
use App\Models\AcctSavings;
use App\Models\AcctSavingsMemberDetail;
use App\Models\CoreProvince;
use App\Models\CoreMember;
use App\Models\PreferenceCompany;
use App\Models\PreferenceTransactionModule;
use App\Helpers\Configuration;
use Illuminate\Support\Facades\DB;
use Elibyy\TCPDF\Facades\TCPDF;

class MemberSavingsDebetPrincipalController extends Controller
{
    public function index()
    {
        $membercharacter = array_filter(Configuration::MemberCharacter());
        $memberidentity = array_filter(Configuration::MemberIdentity());
        $membergender = array_filter(Configuration::MemberGender());
        $debetsource = array_filter(Configuration::DebetSource());
        $coreprovince = CoreProvince::select('province_id', 'province_name')
        ->where('data_state', 0)
        ->get();
        $memberses = session()->get('memberses');
        $datases = session()->get('datases');

        return view('content.MemberSavingsDebetPrincipal.Edit.index', compact('membercharacter','memberidentity','membergender','debetsource','coreprovince','memberses','datases'));
    }

    public function modalMember(CoreMemberDataTable $dataTable)
    {
        return $dataTable->render('content.MemberSavingsDebetPrincipal.Edit.CoreMemberModal.index');
    }

    public function elementsAdd(Request $request)
    {
        $datases = session()->get('datases');
        if(!$datases || $datases == ''){
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

        return redirect('member-savings-debet-principal');
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

        return redirect('member-savings-debet-principal');
    }

    public function processEdit(Request $request)
    {
        $data = array(
            'member_id'								=> session()->get('memberses')['member_id'],
            'member_principal_savings_last_balance'	=> session()->get('memberses')['member_principal_savings_last_balance'],
            'member_special_savings_last_balance'	=> session()->get('memberses')['member_special_savings_last_balance'],
            'member_mandatory_savings_last_balance'	=> session()->get('memberses')['member_mandatory_savings_last_balance'],
            'updated_id'                            => auth()->user()->user_id,
        );

        $mandatory_amount 		= $request->member_mandatory_savings;
        $special_amount			= $request->member_special_savings;
        $principal_amount		=($mandatory_amount + $special_amount) * -1;

        DB::beginTransaction();

        try {
            CoreMember::where('member_id', $data['member_id'])
            ->update([
                'member_principal_savings_last_balance'	=> $data['member_principal_savings_last_balance'],
                'member_special_savings_last_balance'	=> $data['member_special_savings_last_balance'],
                'member_mandatory_savings_last_balance'	=> $data['member_mandatory_savings_last_balance'],
                'updated_id'                            => $data['updated_id'],
            ]);

            $data_detail = array (
                'branch_id'						=> auth()->user()->branch_id,
                'member_id'						=> $data['member_id'],
                'mutation_id'					=> $request->mutation_id,
                'transaction_date'				=> date('Y-m-d'),
                'principal_savings_amount'		=> $principal_amount,
                'special_savings_amount'		=> $mandatory_amount,
                'mandatory_savings_amount'		=> $special_amount,
                'operated_name'					=> auth()->user()->username,
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
                
                $data_journal = array(
                    'branch_id'						=> auth()->user()->branch_id,
                    'journal_voucher_period' 		=> $journal_voucher_period,
                    'journal_voucher_date'			=> date('Y-m-d'),
                    'journal_voucher_title'			=> 'MUTASI ANGGOTA DEBET '.$coremember->member_name,
                    'journal_voucher_description'	=> 'MUTASI ANGGOTA DEBET '.$coremember->member_name,
                    'transaction_module_id'			=> $transaction_module_id,
                    'transaction_module_code'		=> $transaction_module_code,
                    'transaction_journal_id' 		=> $coremember->member_id,
                    'transaction_journal_no' 		=> $coremember->member_no,
                    'created_id' 					=> auth()->user()->user_id,
                    'created_on' 					=> date('Y-m-d H:i:s'),
                );
                
                AcctJournalVoucher::create($data_journal);

                $journal_voucher_id 			= AcctJournalVoucher::where('created_id',auth()->user()->user_id)
                ->orderBy('journal_voucher_id', 'DESC')
                ->first()
                ->journal_voucher_id;

                if($principal_amount <> 0 || $principal_amount <> ''){
                    $account_id = AcctSavings::where('savings_id',$preferencecompany->principal_savings_id)
                    ->first()
                    ->account_id;

                    $account_id_default_status = AcctAccount::where('account_id',$account_id)
                    ->first()
                    ->account_default_status;

                    $data_debet =array(
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $account_id,
                        'journal_voucher_description'	=> 'SETORAN DEBET '.$coremember->member_name,
                        'journal_voucher_amount'		=> $principal_amount * -1,
                        'journal_voucher_debit_amount'	=> $principal_amount * -1,
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 0,
                        'created_id' 					=> auth()->user()->user_id,
                    );

                    AcctJournalVoucherItem::create($data_debet);	
                }

                if($mandatory_amount <> 0 || $mandatory_amount <> ''){
                    $account_id = AcctSavings::where('savings_id',$preferencecompany->mandatory_savings_id)
                    ->first()
                    ->account_id;

                    $account_id_default_status = AcctAccount::where('account_id',$account_id)
                    ->first()
                    ->account_default_status;

                    $data_credit =array(
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $account_id,
                        'journal_voucher_description'	=> 'SETORAN DEBET '.$coremember->member_name,
                        'journal_voucher_amount'		=> $mandatory_amount,
                        'journal_voucher_credit_amount'	=> $mandatory_amount,
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 1,
                        'created_id' 					=> auth()->user()->user_id,
                    );

                    AcctJournalVoucherItem::create($data_credit);	
                }
                

                if($special_amount <> 0 || $special_amount <> ''){
                    $account_id = AcctSavings::where('savings_id',$preferencecompany->special_savings_id)
                    ->first()
                    ->account_id;

                    $account_id_default_status = AcctAccount::where('account_id',$account_id)
                    ->first()
                    ->account_default_status;

                    $data_credit =array(
                        'journal_voucher_id'			=> $journal_voucher_id,
                        'account_id'					=> $account_id,
                        'journal_voucher_description'	=> 'SETORAN DEBET '.$coremember->member_name,
                        'journal_voucher_amount'		=> $special_amount,
                        'journal_voucher_credit_amount'	=> $special_amount,
                        'account_id_default_status'		=> $account_id_default_status,
                        'account_id_status'				=> 1,
                        'created_id' 					=> auth()->user()->user_id,
                    );

                    AcctJournalVoucherItem::create($data_credit);	
                }
            }

            DB::commit();
            $message = array(
                'pesan' => 'Data Debit Simpanan Pokok berhasil diubah',
                'alert' => 'success',
            );
            session()->forget('datases');
            session()->forget('memberses');
            return redirect('member-savings-debet-principal')->with($message);
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Data Debit Simpanan Pokok gagal diubah',
                'alert' => 'error'
            );
            return redirect('member-savings-debet-principal')->with($message);
        }
    }
}
