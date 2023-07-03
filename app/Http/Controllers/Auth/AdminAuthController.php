<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;

class AdminAuthController extends Controller
{    /**
     * Display admin login view
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {

        return view('auth.login');
//        if (Auth::guard('web')->check()) {
//            return redirect()->route('admin.dashboard');
//        } else {
//
//        }
    }

    /**
     * Handle an incoming admin authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
//return $request;
//        $this->validate($request, [
//            'email' => 'required|email',
//            'password' => 'required',
//        ]);
//        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {


//        }
//
//
        if(auth()->guard('web')->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {

//            dd("loginasmaa");
//            $user = auth()->guard('web')->user();
//            $admin = Admin::find($user->id);
//            $admin->update([
//                'last_login_at' => Carbon::now()->toDateTimeString(),
//                'last_login_ip' => $request->getClientIp()
//            ]);
            return redirect()->intended(url('/admin/dashboard'));
        } else {


            return redirect()->back()->withError('Credentials doesn\'t match.');
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
//        Auth::guard('admin')->logout();
//
//        $request->session()->invalidate();
//
//        $request->session()->regenerateToken();
//
//        return redirect('/');
    }
}
