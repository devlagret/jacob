<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\SystemMenu;
use App\Models\SystemMenuMapping;
use App\Models\SystemUserGroup;
use App\DataTables\SystemUserGroupDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SystemUserGroupController extends Controller
{
    public function index(SystemUserGroupDataTable $dataTable)
    {
        return $dataTable->render('content.SystemUserGroup.List.index');
    }

    public function add()
    {
        $config     = theme()->getOption('page', 'user-group');
        $systemmenu = SystemMenu::get();

        return view('content.SystemUserGroup.Add.index', compact('systemmenu'));
    }

    public function processAdd(Request $request)
    {
        $fields = request()->validate([
            'user_group_name'   =>['required'],
        ]);

        $systemmenu = SystemMenu::get();
        $allrequest = $request->all();

        $usergroup  = array(
            'user_group_name'   => $fields['user_group_name'],
        );
        
        DB::beginTransaction();
        try{
            SystemUserGroup::create($usergroup);
            $sg = SystemUserGroup::orderBy('user_group_id','DESC')->first();
            $sg->user_group_level = $sg->user_group_id;
            $sg->save();
        DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            $message = array(
                'pesan' => 'User gagal ditambah',
                'alert' => 'error'
            );
        return redirect('user-group')->with($message);
        }
        DB::beginTransaction();

        try {
            foreach($systemmenu as $key => $val){
                if(isset($allrequest['checkbox_'.$val['id_menu']])){
                    $menumapping = array(
                        'user_group_level' => $sg->user_group_level,
                        'id_menu'          => $val['id_menu'],
                    );
                    SystemMenuMapping::create($menumapping);
                }
            }

            DB::commit();
            $message = array(
                'pesan' => 'User berhasil ditambah',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'User gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('user-group')->with($message);
    }

    public function edit($user_group_id)
    {
        $systemmenu     = SystemMenu::get();
        $usergroup      = SystemUserGroup::where('user_group_id',$user_group_id)
        ->first();
        $menumapping    = SystemMenuMapping::where('user_group_level', $usergroup['user_group_level'])
        ->get();

        return view('content.SystemUserGroup.Edit.index', compact('usergroup', 'systemmenu', 'menumapping'));
    }

    public function processEdit(Request $request)
    {
        $fields = $request->validate([
            'user_group_id'             => 'required',
            'user_group_name'           => 'required',
        ]);

        $systemmenu = SystemMenu::get();

        $allrequest = $request->all();

        $usergroup                   = SystemUserGroup::findOrFail($fields['user_group_id']);
        $usergroup->user_group_name  = $fields['user_group_name'];
        DB::beginTransaction();

        try {
            $usergroup->save();

            foreach($systemmenu as $key => $val){
                $menumapping_last = SystemMenuMapping::where('user_group_level', $usergroup->user_group_level)
                ->where('id_menu', $val['id_menu'])
                ->first();

                if($menumapping_last){
                    $menumapping_last->delete();
                }

                if(isset($allrequest['checkbox_'.$val['id_menu']])){
                    $menumapping = array(
                        'user_group_level' =>  $usergroup->user_group_level,
                        'id_menu'          => $val['id_menu'],
                    );
                    SystemMenuMapping::create($menumapping);
                }
            }

            DB::commit();
            $message = array(
                'pesan' => 'User berhasil diubah',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'User gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('user-group')->with($message);
    }

    public function delete($user_group_id)
    {
        $usergroup               = SystemUserGroup::findOrFail($user_group_id);
        $usergroup->data_state   = 1;
        if($usergroup->save()){
            $message = array(
                'pesan' => 'User Group berhasil dihapus',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'User Group gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('user-group')->with($message);
    }

    public static function getMenuMappingStatus($user_group_level, $id_menu){
        $menumapping =  SystemMenuMapping::where('user_group_level', $user_group_level)
        ->where('id_menu', $id_menu)
        ->count();

        return $menumapping;
    }
}
