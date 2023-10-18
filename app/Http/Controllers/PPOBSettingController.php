<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctAccount;
use App\Models\PPOBSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PPOBSettingController extends Controller
{
    public function index()
    {
        $config         = theme()->getOption('page', 'ppob-setting');

        $ppobsetting    = PPOBSetting::select('*')
        ->first();

        $acctaccount    = AcctAccount::select('user_group_id', 'user_group_name', 'data_state')
        ->where('data_state', 0)
        ->get();

        return view('content.PPOBSetting.index', compact('ppobsetting', 'acctaccount'));
    }

    public function processAdd(Request $request)
    {
        $fields = request()->validate([
            'user_id'       =>['required'],
            'username'      =>['required'],
            'user_group_id' =>['required'],
            'branch_id'     =>['required'],
        ]);

        $user                   = PPOBSetting::findOrFail($fields['user_id']);
        $user->username         = $fields['username'];
        $user->user_group_id    = $fields['user_group_id'];
        $user->branch_id        = $fields['branch_id'];
        if($user->save()){
            $message = array(
                'pesan' => 'Setting PPOB berhasil diubah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Setting PPOB gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('ppob-setting')->with($message);
    }
}
