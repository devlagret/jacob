<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\SettingsEmailRequest;
use App\Http\Requests\Account\SettingsInfoRequest;
use App\Http\Requests\Account\SettingsPasswordRequest;
use App\Models\UserInfo;
use App\Models\User;
use App\Models\SystemUserGroup;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $info = auth()->user()->info;

        // get the default inner page
        return view('pages.account.settings.settings', compact('info'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SettingsInfoRequest $request)
    {
        $data_user = User::where('user_id', auth()->user()->user_id)->first();
        // dd($request->hasFile('avatar'));
        // save user name
        $fields = $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'username'      => 'required',
            'phone'         => '',
            'email'         => '',
            'avatar'        => ''
        ]);

        if ($request->hasFile('avatar')) {
            // Storage::put('public','example.txt', 'Contents');
            $request->file('avatar')->store('public/images');
            Storage::disk('local')->delete('public/images/'.$data_user['avatar']);
        }

        auth()->user()->update($fields);

        $table = User::findOrFail(auth()->user()->user_id);
        $table->first_name = $fields['first_name'];
        $table->last_name = $fields['last_name'];
        $table->username = $fields['username'];
        $table->phone = $fields['phone'];
        $table->email = $fields['email'];
        $table->avatar = $request['avatar'] == null ? $data_user['avatar'] : $request->file('avatar')->hashName();

        if ($table->save()) {
            $message = array(
                'pesan' => 'Data profil berhasil diubah',
                'alert' => 'success'
            );
        } else {
            $message = array(
                'pesan' => 'Data profil gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('user/settings')->with($message);
    }

    /**
     * Function for upload avatar image
     *
     * @param  string  $folder
     * @param  string  $key
     * @param  string  $validation
     *
     * @return false|string|null
     */
    public function upload($folder = 'images', $key = 'avatar', $validation = 'image|mimes:jpeg,png,jpg,gif,svg|max:2048|sometimes')
    {
        request()->validate([$key => $validation]);

        $file = null;
        if (request()->hasFile($key)) {
            $file = Storage::disk('public')->putFile($folder, request()->file($key));
        }

        return $file;
    }

    /**
     * Function to accept request for change email
     *
     * @param  SettingsEmailRequest  $request
     */
    public function changeEmail(SettingsEmailRequest $request)
    {
        // prevent change email for demo account
        if ($request->input('current_email') === 'demo@demo.com') {
            return redirect()->intended('user/settings');
        }

        auth()->user()->update(['email' => $request->input('email')]);

        if ($request->expectsJson()) {
            return response()->json($request->all());
        }

        return redirect()->intended('user/settings');
    }

    /**
     * Function to accept request for change password
     *
     * @param  SettingsPasswordRequest  $request
     */
    public function changePassword(SettingsPasswordRequest $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required',
        ]);
        
        
        if(Hash::check($request->current_password, auth()->user()->password))
        {
            auth()->user()->update(['password' => Hash::make($request->input('password'))]);
            User::find(auth()->user()->user_id)->update(['password'=> Hash::make($request->password),'password_date' => date('Y-m-d ')]);
            $message = array(
                'pesan' => 'Password berhasil diubah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Password Lama Tidak Cocok',
                'alert' => 'error'
            );
        }
        
        return redirect('user/settings')->with($message);
        
        
    }

    public static function getUserGroupName($user_group_id)
    {
        $data = SystemUserGroup::where('user_group_id', $user_group_id)
        ->first();

        return $data['user_group_name'];
    }
}
