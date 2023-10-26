<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\CoreCity;
use App\Models\CoreKecamatan;
use App\Models\CoreKelurahan;
use App\Models\CoreProvince;
use App\Models\CoreMember;
use App\Models\CoreMemberWorking;
use App\Models\PreferenceCompany;
use App\DataTables\CoreMemberStatusDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;

class CoreMemberStatusController extends Controller
{
    public function index(CoreMemberStatusDataTable $dataTable)
    {
        return $dataTable->render('content.CoreMemberStatus.List.index');
    }

    public function updateStatus($member_id)
    {
        $member                 = CoreMember::findOrFail($member_id);

        if($member->member_principal_savings_last_balance > 0){
            $member->member_status  = 1;
            if($member->save()){
                $message = array(
                    'pesan' => 'Status Anggota berhasil diubah',
                    'alert' => 'success'
                );
            }else{
                $message = array(
                    'pesan' => 'Status Anggota gagal diubah',
                    'alert' => 'error'
                );
            }
        }else{
            $message = array(
                'pesan' => 'Status Anggota gagal diubah karena belum memiliki simpanan',
                'alert' => 'error'
            );
        }

        return redirect('member-status')->with($message);
    }
}
