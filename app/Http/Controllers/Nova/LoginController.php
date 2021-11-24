<?php
namespace App\Http\Controllers\Nova;

use Illuminate\Http\Request;
use \Laravel\Nova\Http\Controllers\LoginController as NovaLoginController;

/**
 * Extends NovaLoginController
 * - handle the logout of user (first nova/laravel logout, then cas logout)
 * - show a custom login form (copied and edited from nova default)
 */
class LoginController extends NovaLoginController
{

  /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        if ( cas()->checkAuthentication() )
        {
          /**
           * @var \Illuminate\Http\Request
           */
          return redirect ( '/nova/cas-logout' );
        }


        return redirect($this->redirectPath());
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('nova.auth_login');
    }

}
