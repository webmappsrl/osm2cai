<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;

class EmulateUserController extends Controller
{
    public function restore()
    {
        session(['emulate_user_id' => null]);
        return redirect('/');
    }
}
