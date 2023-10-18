<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\SystemMenu;
use App\Models\SystemUserGroup;
use App\DataTables\SystemUserDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\Configuration;

class UsersController extends Controller
{
    public function index(SystemUserDataTable $dataTable)
    {
        return $dataTable->render('content.SystemUser.List.index');
    }

    public function add()
    {
        $branchstatus = Configuration::BranchStatus();
        $config     = theme()->getOption('page', 'user');
        $usergroup  = SystemUserGroup::select('user_group_id', 'user_group_name', 'data_state')
        ->where('data_state', 0)
        ->where('user_group_level','!=','1')
        ->get();
        $corebranch = CoreBranch::select('branch_id', 'branch_name', 'data_state')
        ->where('data_state', 0)
        ->get();

        return view('content.SystemUser.Add.index', compact('usergroup', 'corebranch','branchstatus'));
    }

    public function processAdd(Request $request)
    {
        $fields = request()->validate([
            'username'      =>['required'],
            'password'      =>['required'],
            'user_group_id' =>['required'],
            'branch_id'     =>['required'],
            'branch_status'     =>['required'],
        ]);

        // * check if username exist
        if(User::where('username',$fields['username'])->first()){
            $message = array(
                'pesan' => 'User dengan username "'.$fields['username'].'" sudah ada. Harap gunakan username lain.',
                'alert' => 'error'
            );
        return redirect('user/add')->with($message);
        }
        // *
    return $request->all();

        $user = array(
            'username'      => $fields['username'],
            'password'      => Hash::make($fields['password']),
            'user_group_id' => $fields['user_group_id'],
            'branch_id'     => $fields['branch_id'],
            'branch_status'     => $fields['branch_status'],
        );

        if(User::create($user)){
            $message = array(
                'pesan' => 'User berhasil ditambah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'User gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('user')->with($message);
    }

    public function edit($id)
    {
        $config     = theme()->getOption('page', 'user');
        $user       = User::find($id);
        $usergroup  = SystemUserGroup::select('user_group_id', 'user_group_name', 'data_state')
        ->where('data_state', 0)
        ->where('user_group_level','!=','1')
        ->get();
        $corebranch = CoreBranch::select('branch_id', 'branch_name', 'data_state')
        ->where('data_state', 0)
        ->get();
        return view('content.SystemUser.Edit.index', compact('user', 'usergroup', 'corebranch'));
    }

    public function processEdit(Request $request)
    {

        $fields = request()->validate([
            'user_id'       =>['required'],
            'username'      =>['required'],
            'user_group_id' =>['required'],
            // 'branch_id'     =>['required'],
        ]);
        $user                   = User::findOrFail($fields['user_id']);
        $user->username         = $fields['username'];
        $user->user_group_id    = $fields['user_group_id'];
        $user->branch_id        = $fields['branch_id'];
        if($request->passIsChanged){
        $user->password = Hash::make($request->password);
        }
        if($user->save()){
            $message = array(
                'pesan' => 'User berhasil diubah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'User gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('user')->with($message);
    }

    public function delete($id)
    {
        $user               = User::findOrFail($id);
        $user->data_state   = 1;
        if($user->save()){
            $message = array(
                'pesan' => 'User berhasil dihapus',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'User gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('user')->with($message);
    }


    public function resetPassword($id)
    {
        $user               = User::findOrFail($id);
        $user->password     = Hash::make('123456');
        if($user->save()){
            $message = array(
                'pesan' => 'Password user berhasil direset',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Password user gagal direset',
                'alert' => 'error'
            );
        }

        return redirect('user')->with($message);
    }
}
