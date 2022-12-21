<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;

class UserController extends Controller {


    public function csv()
    {

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users.csv"',
        ];
        $file = Storage::get('users.csv');
        Storage::delete('users.csv');
        return response($file, 200, $headers);

    }
}
