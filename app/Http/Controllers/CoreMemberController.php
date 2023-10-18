<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctDepositoAccount;
use App\Models\AcctCreditsAccount;
use App\Models\AcctSavingsAccount;
use App\Models\CoreBranch;
use App\Models\CoreCity;
use App\Models\CoreKecamatan;
use App\Models\CoreKelurahan;
use App\Models\CoreProvince;
use App\Models\CoreMember;
use App\Models\CoreMemberWorking;
use App\Models\PreferenceCompany;
use App\Models\SystemMenu;
use App\Models\SystemMenuMapping;
use App\Models\SystemUserGroup;
use App\DataTables\CoreMemberDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CoreMemberController extends Controller
{
    public function index(CoreMemberDataTable $dataTable)
    {
        session()->forget('dadssta_memberadd');

        return $dataTable->render('content.CoreMember.List.index');
    }

    public function add()
    {
        $config                 = theme()->getOption('page', 'view');
        $sessiondata            = session()->get('data_memberadd');
        $homestatus		= Configuration::HomeStatus();
        $lasteducation = Configuration::LastEducation();
        $membergender           = array_filter(Configuration::MemberGender());
        $maritalstatus          = array_filter(Configuration::MaritalStatus());
        $workingtype            = array_filter(Configuration::WorkingType());
        $businessscale          = array_filter(Configuration::BusinessScale());
        $businessowner          = array_filter(Configuration::BusinessOwner());
        $familyrelationship     = array_filter(Configuration::FamilyRelationship());
        $coreprovince           = CoreProvince::select('province_name', 'province_id')
        ->where('data_state', 0)
        ->get();

        return view('content.CoreMember.Add.index', compact('membergender', 'maritalstatus', 'workingtype', 'businessscale',
         'businessowner', 'familyrelationship', 'coreprovince', 'sessiondata','homestatus','lasteducation'));
    }

    public function elementsAdd(Request $request)
    {
        $sessiondata = session()->get('data_memberadd');
        if(!$sessiondata || $sessiondata == ""){
            $sessiondata['member_name']                   = '';
            $sessiondata['member_gender']                 = '';
            $sessiondata['member_place_of_birth']         = '';
            $sessiondata['member_date_of_birth']          = '';
            $sessiondata['member_address']                = '';
            $sessiondata['member_address_now']            = '';
            $sessiondata['province_id']                   = '';
            $sessiondata['city_id']                       = '';
            $sessiondata['kecamatan_id']                  = '';
            $sessiondata['kelurahan_id']                  = '';
            $sessiondata['member_nick_name']              = '';
            $sessiondata['member_postal_code']            = '';
            $sessiondata['member_marital_status']         = '';
            $sessiondata['member_identity_no']            = '';
            $sessiondata['member_partner_identity_no']    = '';
            $sessiondata['member_home_status']            = '';
            $sessiondata['member_long_stay']              = '';
            $sessiondata['member_last_education']         = '';
            $sessiondata['member_phone']                  = '';
            $sessiondata['member_partner_name']           = '';
            $sessiondata['member_partner_place_of_birth'] = '';
            $sessiondata['member_partner_date_of_birth']  = '';
            $sessiondata['member_email']                  = '';
            $sessiondata['member_dependent']              = '';
            $sessiondata['member_home_status']            = '';
            $sessiondata['member_heir']                   = '';
            $sessiondata['member_heir_relationship']      = '';
            $sessiondata['member_heir_mobile_phone']      = '';
            $sessiondata['member_heir_address']           = '';
            $sessiondata['member_working_type']           = '';
            $sessiondata['member_company_name']           = '';
            $sessiondata['member_company_specialities']   = '';
            $sessiondata['member_company_address']        = '';
            $sessiondata['member_company_phone']          = '';
            $sessiondata['member_business_scale']         = '';
            $sessiondata['member_business_owner']         = '';
            $sessiondata['member_monthly_income']         = '';
            $sessiondata['partner_working_type']          = '';
            $sessiondata['partner_business_scale']        = '';
            $sessiondata['partner_business_owner']        = '';
        }
        $sessiondata[$request->name] = $request->value;
        session()->put('data_memberadd', $sessiondata);
    }

    public function processAdd(Request $request)
    {
        $fields = request()->validate([
            'member_name'                     =>['required'],
            'member_gender'                   =>['required'],
            'member_place_of_birth'           =>['required'],
            'member_date_of_birth'            =>['required'],
            'member_address'                  =>['required'],
            'member_address_now'              =>['required'],
            'member_mother'                   =>['required'],
            'province_id'                     =>['required'],
            'city_id'                         =>['required'],
            'kecamatan_id'                    =>['required'],
            'kelurahan_id'                    =>['required'],
        ]);
        
        DB::beginTransaction();
        
        try {
            $member  = array(
                'kelurahan_id'                => $fields['kelurahan_id'],
                'province_id'                 => $fields['province_id'],
                'branch_id'                   => auth()->user()->branch_id,
                'city_id'                     => $fields['city_id'],
                'kecamatan_id'                => $fields['kecamatan_id'],
                'member_name'                 => $fields['member_name'],
                'member_nick_name'            => $request->member_nick_name,
                'member_gender'               => $fields['member_gender'],
                'member_place_of_birth'       => $fields['member_place_of_birth'],
                'member_date_of_birth'        => date('Y-m-d', strtotime($fields['member_date_of_birth'])),
                'member_address'              => $fields['member_address'],
                'member_address_now'          => $fields['member_address_now'],
                'member_postal_code'          => $request->member_postal_code,
                'member_home_phone'           => $request->member_home_phone,
                'member_phone'                => $request->member_phone,
                'member_marital_status'       => $request->member_marital_status,
                'member_dependent'            => $request->member_dependent,
                'member_home_status'          => $request->member_home_status,
                'member_long_stay'            => $request->member_long_stay,
                'member_last_education'       => $request->member_last_education,
                'member_partner_name'         => $request->member_partner_name,
                'member_partner_place_of_birth'=> $request->member_partner_place_of_birth,
                'member_partner_date_of_birth'=> date('Y-m-d', strtotime($request->member_partner_date_of_birth)),
                'member_email'                => $request->member_email,
                'member_identity_no'          => $request->member_identity_no,
                'member_partner_identity_no'  => $request->member_partner_identity_no,
                'member_mother'               => $fields['member_mother'],
                'member_heir'                 => $request->member_heir,
                'member_heir_relationship'    => $request->member_heir_relationship,
                'member_heir_mobile_phone'    => $request->member_heir_mobile_phone,
                'member_heir_address'         => $request->member_heir_address,
                'created_id'                  => auth()->user()->user_id,
            );
            CoreMember::create($member);

            $member_id = CoreMember::select('member_id')
            ->where('created_id', auth()->user()->user_id)
            ->orderBy('member_id', 'DESC')
            ->first()
            ->member_id;
            if($request->member_working_type == 3){
                $member_company_name             = '';
                $member_company_specialities     = '';
                $member_company_job_title        = '';
                $member_company_period           = '';
                $member_company_address          = '';
                $member_company_city             = '';
                $member_company_postal_code      = '';
                $member_company_phone            = '';
                $member_business_name            = $request->member_business_name;
                $member_business_scale           = $request->member_business_scale;
                $member_business_period          = $request->member_business_period;
                $member_business_owner           = $request->member_business_owner;
                $member_business_address         = $request->member_business_address;
                $member_business_city            = $request->member_business_city;
                $member_business_postal_code     = $request->member_business_postal_code;
                $member_business_phone           = $request->member_business_phone;
            }else{
                $member_company_name             = $request->member_company_name;
                $member_company_specialities     = $request->member_company_specialities;
                $member_company_job_title        = $request->member_company_job_title;
                $member_company_period           = $request->member_company_period;
                $member_company_address          = $request->member_company_address;
                $member_company_city             = $request->member_company_city;
                $member_company_postal_code      = $request->member_company_postal_code;
                $member_company_phone            = $request->member_company_phone;
                $member_business_name            = '';
                $member_business_scale           = '0';
                $member_business_period          = '';
                $member_business_owner           = '0';
                $member_business_address         = '';
                $member_business_city            = '';
                $member_business_postal_code     = '';
                $member_business_phone           = '';
            }
            if($request->partner_working_type == 3){
                $partner_company_name            = '';
                $partner_company_specialities    = '';
                $partner_company_job_title       = '';
                $partner_company_address         = '';
                $partner_company_phone           = '';
                $partner_business_name           = $request->partner_business_name;
                $partner_business_scale          = $request->partner_business_scale;
                $partner_business_period         = $request->partner_business_period;
                $partner_business_owner          = $request->partner_business_owner;
            }else{
                $partner_company_name            = $request->partner_company_name;
                $partner_company_specialities    = $request->partner_company_specialities;
                $partner_company_job_title       = $request->partner_company_job_title;
                $partner_company_address         = $request->partner_company_address;
                $partner_company_phone           = $request->partner_company_phone;
                $partner_business_name           = '';
                $partner_business_scale          = '0';
                $partner_business_period         = '';
                $partner_business_owner          = '0';
            }
            $memberworking  = array(
                'member_id'                   => $member_id,
                'member_working_type'         => $request->member_working_type,
                'member_company_name'         => $member_company_name,
                'member_company_specialities' => $member_company_specialities,
                'member_company_job_title'    => $member_company_job_title,
                'member_company_period'       => $member_company_period,
                'member_company_address'      => $member_company_address,
                'member_company_city'         => $member_company_city,
                'member_company_postal_code'  => $member_company_postal_code,
                'member_company_phone'        => $member_company_phone,
                'member_business_name'        => $member_business_name,
                'member_business_scale'       => $member_business_scale,
                'member_business_period'      => $member_business_period,
                'member_business_owner'       => $member_business_owner,
                'member_business_address'     => $member_business_address,
                'member_business_city'        => $member_business_city,
                'member_business_postal_code' => $member_business_postal_code,
                'member_business_phone'       => $member_business_phone,
                'member_monthly_income'       => $request->member_monthly_income,
                'partner_working_type'        => $request->partner_working_type,
                'partner_company_name'        => $partner_company_name,
                'partner_company_specialities'=> $partner_company_specialities,
                'partner_company_job_title'   => $partner_company_job_title,
                'partner_company_address'     => $partner_company_address,
                'partner_company_phone'       => $partner_company_phone,
                'partner_business_name'       => $partner_business_name,
                'partner_business_scale'      => $partner_business_scale,
                'partner_business_period'     => $partner_business_period,
                'partner_business_owner'      => $partner_business_owner,

            );
            CoreMemberWorking::create($memberworking);

            DB::commit();
            $message = array(
                'pesan' => 'Anggota berhasil ditambah',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Anggota gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('member')->with($message);
    }

    public function edit($member_id)
    {
        $membercharacter		= Configuration::MemberCharacter();
        $homestatus		= Configuration::HomeStatus();
        $lasteducation = Configuration::LastEducation();
        $config                 = theme()->getOption('page', 'member-edit');
        $member                 = CoreMember::findOrFail($member_id);
        $memberworking          = CoreMemberWorking::where('member_id', $member_id)->first();
        $membergender           = array_filter(Configuration::MemberGender());
        $maritalstatus          = array_filter(Configuration::MaritalStatus());
        $workingtype            = array_filter(Configuration::WorkingType());
        $businessscale          = array_filter(Configuration::BusinessScale());
        $businessowner          = array_filter(Configuration::BusinessOwner());
        $familyrelationship     = array_filter(Configuration::FamilyRelationship());
        $paymentpreference      = array_filter(Configuration::PaymentPreference());
        $coreprovince           = CoreProvince::select('province_name', 'province_id')
        ->where('data_state', 0)
        ->get();
        $corecity               = CoreCity::select('city_name', 'city_id')
        ->where('province_id', $member['province_id'])
        ->where('data_state', 0)
        ->get();
        $corekecamatan          = CoreKecamatan::select('kecamatan_name', 'kecamatan_id')
        ->where('city_id', $member['city_id'])
        ->where('data_state', 0)
        ->get();
        $corekelurahan          = CoreKelurahan::select('kelurahan_name', 'kelurahan_id')
        ->where('kecamatan_id', $member['kecamatan_id'])
        ->where('data_state', 0)
        ->get();
        $acctsavingsaccount     = AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no')
        ->join('acct_savings', 'acct_savings_account.savings_id','=','acct_savings.savings_id')
        ->where('acct_savings_account.member_id', $member_id)
        ->where('acct_savings.savings_status', 0)
        ->where('acct_savings_account.data_state', 0)
        ->get();

        return view('content.CoreMember.Edit.index', compact('member', 'memberworking', 'membergender', 'maritalstatus',
         'workingtype', 'businessscale', 'businessowner', 'familyrelationship', 'coreprovince', 'corecity', 'corekecamatan',
          'corekelurahan','paymentpreference','acctsavingsaccount','membercharacter','homestatus','lasteducation'));
    }

    public function processEdit(Request $request)
    {

        $fields = request()->validate([
            'member_id'                       =>['required'],
            'member_name'                     =>['required'],
            'member_gender'                   =>['required'],
            'member_place_of_birth'           =>['required'],
            'member_date_of_birth'            =>['required'],
            'member_address'                  =>['required'],
            'member_address_now'              =>['required'],
            'member_mother'                   =>['required'],
            'province_id'                     =>['required'],
            'city_id'                         =>['required'],
            'kecamatan_id'                    =>['required'],
            'kelurahan_id'                    =>['required'],
        ]);

        DB::beginTransaction();
        try {
            $member = CoreMember::findOrFail($fields['member_id']);
            $member->kelurahan_id                       = $fields['kelurahan_id'];
            $member->province_id                        = $fields['province_id'];
            $member->branch_id                          = $request->user()->branch_id;
            $member->city_id                            = $fields['city_id'];
            $member->kecamatan_id                       = $fields['kecamatan_id'];
            $member->member_name                        = $fields['member_name'];
            $member->member_nick_name                   = $request->member_nick_name;
            $member->member_gender                      = $fields['member_gender'];
            $member->member_place_of_birth              = $fields['member_place_of_birth'];
            $member->member_date_of_birth               = date('Y-m-d', strtotime($fields['member_date_of_birth']));
            $member->member_address                     = $fields['member_address'];
            $member->member_address_now                 = $fields['member_address_now'];
            $member->member_postal_code                 = $request->member_postal_code;
            $member->member_home_phone                  = $request->member_home_phone;
            $member->member_phone                       = $request->member_phone;
            $member->member_marital_status              = $request->member_marital_status;
            $member->member_dependent                   = $request->member_dependent;
            $member->member_home_status                 = $request->member_home_status;
            $member->member_long_stay                   = $request->member_long_stay;
            $member->member_last_education              = $request->member_last_education;
            $member->member_partner_name                = $request->member_partner_name;
            $member->member_partner_place_of_birth      = $request->member_partner_place_of_birth;
            $member->member_partner_date_of_birth       = date('Y-m-d', strtotime($request->member_partner_date_of_birth));
            $member->member_email                       = $request->member_email;
            $member->member_identity_no                 = $request->member_identity_no;
            $member->member_partner_identity_no         = $request->member_partner_identity_no;
            $member->member_character                   = $request->member_character;
            $member->member_mother                      = $fields['member_mother'];
            $member->member_heir                        = $request->member_heir;
            $member->member_heir_relationship           = $request->member_heir_relationship;
            $member->member_heir_mobile_phone           = $request->member_heir_mobile_phone;
            $member->member_heir_address                = $request->member_heir_address;
            $member->updated_id                         = auth()->user()->user_id;
            $member->save();

            if($request->member_working_type == 3){
                $member_company_name             = '';
                $member_company_specialities     = '';
                $member_company_job_title        = '';
                $member_company_period           = '';
                $member_company_address          = '';
                $member_company_city             = '';
                $member_company_postal_code      = '';
                $member_company_phone            = '';
                $member_business_name            = $request->member_business_name;
                $member_business_scale           = $request->member_business_scale;
                $member_business_period          = $request->member_business_period;
                $member_business_owner           = $request->member_business_owner;
                $member_business_address         = $request->member_business_address;
                $member_business_city            = $request->member_business_city;
                $member_business_postal_code     = $request->member_business_postal_code;
                $member_business_phone           = $request->member_business_phone;
            }else{
                $member_company_name             = $request->member_company_name;
                $member_company_specialities     = $request->member_company_specialities;
                $member_company_job_title        = $request->member_company_job_title;
                $member_company_period           = $request->member_company_period;
                $member_company_address          = $request->member_company_address;
                $member_company_city             = $request->member_company_city;
                $member_company_postal_code      = $request->member_company_postal_code;
                $member_company_phone            = $request->member_company_phone;
                $member_business_name            = '';
                $member_business_scale           = '0';
                $member_business_period          = '';
                $member_business_owner           = '0';
                $member_business_address         = '';
                $member_business_city            = '';
                $member_business_postal_code     = '';
                $member_business_phone           = '';
            }
            if($request->partner_working_type == 3){
                $partner_company_name            = '';
                $partner_company_specialities    = '';
                $partner_company_job_title       = '';
                $partner_company_address         = '';
                $partner_company_phone           = '';
                $partner_business_name           = $request->partner_business_name;
                $partner_business_scale          = $request->partner_business_scale;
                $partner_business_period         = $request->partner_business_period;
                $partner_business_owner          = $request->partner_business_owner;
            }else{
                $partner_company_name            = $request->partner_company_name;
                $partner_company_specialities    = $request->partner_company_specialities;
                $partner_company_job_title       = $request->partner_company_job_title;
                $partner_company_address         = $request->partner_company_address;
                $partner_company_phone           = $request->partner_company_phone;
                $partner_business_name           = '';
                $partner_business_scale          = '0';
                $partner_business_period         = '';
                $partner_business_owner          = '0';
            }
            $memberworking = CoreMemberWorking::where('member_id', $fields['member_id'])->first();
            $memberworking->member_working_type             = $request->member_working_type;
            $memberworking->member_company_name             = $member_company_name;
            $memberworking->member_company_specialities     = $member_company_specialities;
            $memberworking->member_company_job_title        = $member_company_job_title;
            $memberworking->member_company_period           = $member_company_period;
            $memberworking->member_company_address          = $member_company_address;
            $memberworking->member_company_city             = $member_company_city;
            $memberworking->member_company_postal_code      = $member_company_postal_code;
            $memberworking->member_company_phone            = $member_company_phone;
            $memberworking->member_business_name            = $member_business_name;
            $memberworking->member_business_scale           = $member_business_scale;
            $memberworking->member_business_period          = $member_business_period;
            $memberworking->member_business_owner           = $member_business_owner;
            $memberworking->member_business_address         = $member_business_address;
            $memberworking->member_business_city            = $member_business_city;
            $memberworking->member_business_postal_code     = $member_business_postal_code;
            $memberworking->member_business_phone           = $member_business_phone;
            $memberworking->member_monthly_income           = $request->member_monthly_income;
            $memberworking->partner_working_type            = $request->partner_working_type;
            $memberworking->partner_company_name            = $partner_company_name;
            $memberworking->partner_company_specialities    = $partner_company_specialities;
            $memberworking->partner_company_job_title       = $partner_company_job_title;
            $memberworking->partner_company_address         = $partner_company_address;
            $memberworking->partner_company_phone           = $partner_company_phone;
            $memberworking->partner_business_name           = $partner_business_name;
            $memberworking->partner_business_scale          = $partner_business_scale;
            $memberworking->partner_business_period         = $partner_business_period;
            $memberworking->partner_business_owner          = $partner_business_owner;



            $memberworking->save();

            DB::commit();
            $message = array(
                'pesan' => 'Anggota berhasil diubah',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Anggota gagal diubah',
                'alert' => 'error'
            );
            return dump($e);
        }

        return redirect('member')->with($message);
    }

    public function detail($member_id)
    {
        $config                 = theme()->getOption('page', 'member-detail');
        $memberidentity			= Configuration::MemberIdentity();
        $membergender			= Configuration::MemberGender();
        $membercharacter		= Configuration::MemberCharacter();

        $coremember				= CoreMember::select('core_member.*', 'core_branch.branch_name', 'core_province.province_name', 'core_city.city_name', 'core_kecamatan.kecamatan_name', 'core_member_working.member_company_job_title', 'core_member_working.member_company_name')
        ->join('core_member_working','core_member.member_id', '=', 'core_member_working.member_id')
        ->join('core_province', 'core_member.province_id', '=', 'core_province.province_id')
        ->join('core_city', 'core_member.city_id', '=', 'core_city.city_id')
        ->join('core_kecamatan', 'core_member.kecamatan_id', '=', 'core_kecamatan.kecamatan_id')
        ->join('core_branch', 'core_member.branch_id', '=', 'core_branch.branch_id')
        ->where('core_member.member_id', $member_id)
        ->where('core_member.data_state', 0)
        ->first();

        $acctsavingsaccount		= AcctSavingsAccount::select('acct_savings_account.savings_account_id', 'acct_savings_account.savings_account_no', 'acct_savings.savings_name')
        ->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
        ->where('acct_savings_account.member_id', $member_id)
        ->where('acct_savings.savings_status', 0)
        ->where('acct_savings_account.data_state', 0)
        ->get();

        $acctcreditsaccount		= AcctCreditsAccount::select('acct_credits_account.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits.credits_name')
        ->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
        ->where('acct_credits_account.member_id', $member_id)
        ->where('acct_credits_account.data_state', 0)
        ->get();

        return view('content.CoreMember.Detail.index', compact('coremember', 'acctsavingsaccount', 'acctcreditsaccount', 'memberidentity', 'membergender', 'membercharacter'));
    }

    public function delete($member_id)
    {
        $member               = CoreMember::findOrFail($member_id);
        $member->data_state   = 1;
        if($member->save()){
            $message = array(
                'pesan' => 'Anggota berhasil dihapus',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Anggota gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('member')->with($message);
    }

    public function activate($member_id)
    {
        $member                         = CoreMember::findOrFail($member_id);
        $member->member_active_status   = 0;
        if($member->save()){
            $message = array(
                'pesan' => 'Anggota berhasil diaktifasi',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Anggota gagal diaktifasi',
                'alert' => 'error'
            );
        }

        return redirect('member')->with($message);
    }

    public function nonActivate($member_id)
    {
        $savingsaccount 	= AcctSavingsAccount::where('member_id', $member_id)
        ->where('data_state', 0)
        ->get();
        $depositoaccount 	= AcctDepositoAccount::where('member_id', $member_id)
        ->where('data_state', 0)
        ->get();
        $creditsaccount 	= AcctCreditsAccount::where('member_id', $member_id)
        ->where('data_state', 0)
        ->get();

        foreach($savingsaccount as $item){
            if($item['savings_account_last_balance'] > 0){
                $message = array(
                    'pesan' => 'Anggota Masih Memiliki Saldo Tabungan!',
                    'alert' => 'warning'
                );
                return redirect('member')->with($message);
            }
        }
        foreach($depositoaccount as $item){
            if($item['deposito_account_status'] == 0 || $item['deposito_account_closed_date'] == null){
                $message = array(
                    'pesan' => 'Anggota Masih Memiliki Simpanan Berjangka!',
                    'alert' => 'warning'
                );
                return redirect('member')->with($message);
            }
        }
        foreach($creditsaccount as $item){
            if($item['credits_account_status'] == 0){
                $message = array(
                    'pesan' => 'Anggota Masih Memiliki Pinjaman!',
                    'alert' => 'warning'
                );
                return redirect('member')->with($message);
            }
        }
        $member                         = CoreMember::findOrFail($member_id);
        $member->member_active_status   = 1;
        if($member->save()){
            $message = array(
                'pesan' => 'Anggota berhasil dinon-aktifasi',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Anggota gagal dinon-aktifasi',
                'alert' => 'error'
            );
        }

        return redirect('member')->with($message);
    }

    public static function getMenuMapping($id){
        $id_menu = SystemMenu::select('id_menu')
        ->where('id', $id)
        ->first()
        ->id_menu;

        $user_group_level = SystemUserGroup::select('user_group_level')
        ->where('user_group_id', auth()->user()->user_group_id)
        ->first()
        ->user_group_level;

        $menumapping = SystemMenuMapping::select('*')
        ->where('id_menu', $id_menu)
        ->where('user_group_level', $user_group_level)
        ->first();

        if($menumapping){
            return 1;
        }else{
            return 0;
        }
    }

    public function getCity(Request $request){
        $data = '';

        $corecity = CoreCity::select('city_name', 'city_id')
        ->where('province_id', $request->province_id)
        ->where('data_state', 0)
        ->get();

        foreach ($corecity as $val){
            $data .= "<option value='$val[city_id]' ". ($request->last_city_id==$val['city_id'] ?'selected':$request->last_city_id).">$val[city_name]</option>\n";
        }

        return $data;
    }

    public function getKecamatan(Request $request){
        $data = '';

        $corekecamatan = CoreKecamatan::select('kecamatan_name', 'kecamatan_id')
        ->where('city_id', $request->city_id)
        ->where('data_state', 0)
        ->get();

        foreach ($corekecamatan as $val){
            $data .= "<option value='$val[kecamatan_id]' ".($request->last_kecamatan_id==$val['kecamatan_id']?'selected':'').">$val[kecamatan_name]</option>\n";
        }

        return $data;
    }

    public function getKelurahan(Request $request){
        $data = '';

        $corekelurahan = CoreKelurahan::select('kelurahan_name', 'kelurahan_id')
        ->where('kecamatan_id', $request->kecamatan_id)
        ->where('data_state', 0)
        ->get();

        foreach ($corekelurahan as $val){
            $data .= "<option value='$val[kelurahan_id]' ".($request->last_kelurahan_id==$val['kelurahan_id']?'selected':'').">$val[kelurahan_name]</option>\n";
        }

        return $data;
    }

    public function export(){
        $branch_id          = auth()->user()->branch_id;
        $memberstatus		= Configuration::MemberStatus();
        $memberstatusaktif	= Configuration::MemberStatusAktif();
        $membergender		= Configuration::MemberGender();
        $membercharacter	= Configuration::MemberCharacter();
        $memberidentity 	= Configuration::MemberIdentity();
        $preferencecompany	= PreferenceCompany::select('company_name')->first();
        $spreadsheet        = new Spreadsheet();

        $coremember         = CoreMember::select('core_member.member_id', 'core_member.branch_id', 'core_branch.branch_name', 'core_member.member_no', 'core_member.member_name',
        'core_member.member_active_status', 'core_member.member_gender', 'core_member.member_place_of_birth', 'core_member.member_date_of_birth', 'core_member.member_address',
        'core_member.province_id', 'core_province.province_name', 'core_member.city_id', 'core_city.city_name', 'core_member.kecamatan_id', 'core_kecamatan.kecamatan_name',
        'core_member.member_phone', 'core_member.member_job', 'core_member.member_identity', 'core_member.member_identity_no', 'core_member.member_postal_code', 'core_member.member_mother',
        'core_member.member_heir', 'core_member.member_family_relationship', 'core_member.member_status', 'core_member.member_register_date', 'core_member.member_principal_savings',
        'core_member.member_special_savings', 'core_member.member_mandatory_savings', 'core_member.member_character', 'core_member.member_token', 'core_member.member_principal_savings_last_balance',
        'core_member.member_special_savings_last_balance', 'core_member.member_mandatory_savings_last_balance', 'core_member.company_id', 'core_member_working.partner_working_type',
        'core_member_working.member_company_name')
        ->join('core_member_working', 'core_member.member_id', '=', 'core_member_working.member_id')
        ->join('core_province', 'core_member.province_id', '=', 'core_province.province_id')
        ->join('core_city', 'core_member.city_id', '=', 'core_city.city_id')
        ->join('core_kecamatan', 'core_member.kecamatan_id', '=', 'core_kecamatan.kecamatan_id')
        ->join('core_branch', 'core_member.branch_id', '=', 'core_branch.branch_id')
        ->where('core_member.data_state', 0)
        ->where('core_member.branch_id', $branch_id)
        ->orderBy('core_member.member_no', 'ASC')
        ->get();

        if(count($coremember)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Master Data Anggota")
                                            ->setSubject("")
                                            ->setDescription("Master Data Anggota")
                                            ->setKeywords("Master, Data, Anggota")
                                            ->setCategory("Master Data Anggota");

            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Master Data Anggota");

            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(10);
            $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('X')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('Z')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('AA')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('AB')->setWidth(15);

            $spreadsheet->getActiveSheet()->mergeCells("B1:P1");
            $spreadsheet->getActiveSheet()->mergeCells("V1:X1");
            $spreadsheet->getActiveSheet()->mergeCells("Z1:AB1");

            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:P3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:P3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:P3')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('V1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('V1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('V3:X3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('V3:X3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('V3:X3')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('Z1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('Z1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('Z3:AB3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('Z3:AB3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('Z3:AB3')->getFont()->setBold(true);


            $spreadsheet->getActiveSheet()->setCellValue('B1',"Master Data Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('V1',"Data Anggota Berdasar Gender");
            $spreadsheet->getActiveSheet()->setCellValue('Z1',"Data Anggota Berdasar Status");
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"No Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Alamat");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Tempat Lahir");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Tanggal Lahir");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Jenis Kelamin");
            $spreadsheet->getActiveSheet()->setCellValue('I3',"Status");
            $spreadsheet->getActiveSheet()->setCellValue('J3',"Status Aktif");
            $spreadsheet->getActiveSheet()->setCellValue('K3',"No. Telp");
            $spreadsheet->getActiveSheet()->setCellValue('L3',"Tipe Pekerjaan");
            $spreadsheet->getActiveSheet()->setCellValue('M3',"Perusahaan");
            $spreadsheet->getActiveSheet()->setCellValue('N3',"Simpanan Pokok");
            $spreadsheet->getActiveSheet()->setCellValue('O3',"Simpanan Khusus");
            $spreadsheet->getActiveSheet()->setCellValue('P3',"Simpanan Wajib");
            $spreadsheet->getActiveSheet()->setCellValue('V3', "No");
            $spreadsheet->getActiveSheet()->setCellValue('V4', "1");
            $spreadsheet->getActiveSheet()->setCellValue('V5', "2");
            $spreadsheet->getActiveSheet()->setCellValue('W3', "Jenis Kelamin");
            $spreadsheet->getActiveSheet()->setCellValue('W4', "Laki - Laki");
            $spreadsheet->getActiveSheet()->setCellValue('W5', "Perempuan");
            $spreadsheet->getActiveSheet()->setCellValue('X3', "Jumlah Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('Z3', "No");
            $spreadsheet->getActiveSheet()->setCellValue('Z4', "1");
            $spreadsheet->getActiveSheet()->setCellValue('Z5', "2");
            $spreadsheet->getActiveSheet()->setCellValue('AA3', "Status Aktif");
            $spreadsheet->getActiveSheet()->setCellValue('AA4', "Aktif");
            $spreadsheet->getActiveSheet()->setCellValue('AA5', "Tidak Aktif");
            $spreadsheet->getActiveSheet()->setCellValue('AB3', "Jumlah Anggota");

            $j                      = 4;
            $no                     = 0;
            $count_member_entership = 0;
            $count_member_male 		= 0;
            $count_member_female 	= 0;
            $count_member_active 	= 0;
            $count_member_nonactive = 0;
            foreach($coremember as $key=>$val){
                $no++;

                $spreadsheet->getActiveSheet()->getStyle('B'.$j.':P'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('L'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('M'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('N'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('O'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('P'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
                $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['member_no']);
                $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['member_name']);
                $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $val['member_address']);
                $spreadsheet->getActiveSheet()->setCellValue('F'.$j, $val['member_place_of_birth']);
                $spreadsheet->getActiveSheet()->setCellValue('G'.$j, $val['member_date_of_birth']);
                $spreadsheet->getActiveSheet()->setCellValue('H'.$j, $membergender[$val['member_gender']]);
                $spreadsheet->getActiveSheet()->setCellValue('I'.$j, $memberstatus[$val['member_status']]);
                $spreadsheet->getActiveSheet()->setCellValue('J'.$j, $memberstatusaktif[$val['member_active_status']]);
                $spreadsheet->getActiveSheet()->setCellValue('K'.$j, $val['member_phone']);

                if($val['partner_working_type'] == 1){
                    $partner_working_type = 'Karyawan';
                }elseif($val['partner_working_type'] == 2){
                    $partner_working_type = 'Profesional';
                }elseif($val['partner_working_type'] == 3){
                    $partner_working_type = 'Non Karyawan';
                }else{
                    $partner_working_type = '-';
                }

                $spreadsheet->getActiveSheet()->setCellValue('L'.$j, $partner_working_type);
                $spreadsheet->getActiveSheet()->setCellValue('M'.$j, $val['member_company_name']);
                $spreadsheet->getActiveSheet()->setCellValue('N'.$j, number_format($val['member_principal_savings'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('O'.$j, number_format($val['member_special_savings'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('P'.$j, number_format($val['member_mandatory_savings'], 2));

                if($val['company_id'] == 0){
                    $count_member_entership = $count_member_entership + 1;
                }

                if($val['member_gender'] == 0){
                    $count_member_female += 1;
                }else{
                    $count_member_male += 1;
                }

                if($val['member_active_status'] == 0){
                    $count_member_active += 1;
                }else{
                    $count_member_nonactive += 1;
                }

                $j++;
            }

            $spreadsheet->getActiveSheet()->getStyle('V3:X5')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('V3:V5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('W3:W5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('X3:X5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle('Z3:AB5')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('Z3:Z5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('W3:W5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('AB3:AB5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $spreadsheet->getActiveSheet()->setCellValue('X4', $count_member_male);
            $spreadsheet->getActiveSheet()->setCellValue('X5', $count_member_female);
            $spreadsheet->getActiveSheet()->setCellValue('AB4', $count_member_active);
            $spreadsheet->getActiveSheet()->setCellValue('AB5', $count_member_nonactive);

            ob_clean();
            $filename='Master Data Anggota.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }else{
            echo "Maaf data yang di eksport tidak ada !";
        }
    }
}
