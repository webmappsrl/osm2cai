<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MigrationNoticeController extends Controller
{
    /**
     * Show the migration notice page.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return view('migration-notice');
    }

    /**
     * Continue to the application after acknowledging the notice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function continue(Request $request)
    {
        // Mark the notice as seen
        Session::put('migration_notice_seen', true);

        Session::save();

        // Redirect to the previous route or to Nova's home
        $redirectTo = Session::get('previous_route', 'dashboards/main');

        return redirect($redirectTo);
    }
}
