<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SystemLogUser;
use App\Models\User;
use App\Models\PreferenceCompany;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *  
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // return view('auth.login');
        return view('auth.login-splash');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $this->logoutRequest();
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function logoutRequest()
    {
        $data = array(
            'user_id' => auth()->user()->user_id,
            'username' => auth()->user()->username,
            'id_previllage' => 1002,
            'log_stat' => 1,
            'class_name' => 'Application.validationprocess.logout',
            'pk' => auth()->user()->username,
            'remark' => 'Logout System',
            'log_time' => date('Y-m-d H:i:s'),
        );

        SystemLogUser::create($data);
    }

    static public function getLogo(){
        $logo = PreferenceCompany::select('logo_koperasi')
        ->first()
        ->logo_koperasi;

        return $logo;
    }

    static public function getLogoIcon(){
        $logo = PreferenceCompany::select('logo_koperasi_icon')
        ->first()
        ->logo_koperasi_icon;

        return $logo;
    }

    static public function getLogoIconGray(){
        $logo = PreferenceCompany::select('logo_koperasi_icon_gray')
        ->first()
        ->logo_koperasi_icon_gray;

        return $logo;
    }
}
