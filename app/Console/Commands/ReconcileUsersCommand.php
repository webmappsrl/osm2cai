<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UgcPoi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcileUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:reconcile-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command reconcile users using user_no_match saved email.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //take all the ugcPois with user_id = null
        //for each of them check the user_no_match column
        //with the mail saved in user_no_match column search for the corresponding user in osm2cai platform
        //associate the ugcpoi with the founded user (if found). else log the mail for the user not found in the platform.

        $logger = Log::channel('reconciledUsers');

        $ugcPois = UgcPoi::whereNull('user_id')->wherenotnull('user_no_match')->get(['id', 'user_no_match', 'user_id']);

        if (count($ugcPois) == 0) {
            $logger->info("Ugc pois are already reconciled");
            $this->info("Ugc pois are already reconciled");
            return;
        }

        foreach ($ugcPois as $ugcPoi) {
            $userMail = $ugcPoi->user_no_match;
            $user = User::where('email', $userMail)->first();
            if ($user) {
                $ugcPoi->user_id = $user->id;
                $ugcPoi->user_no_match = null;
                $ugcPoi->save();
                $logger->info("Ugc poi {$ugcPoi->id} reconciled with user {$userMail}");
                $this->info("Ugc poi {$ugcPoi->id} reconciled with user {$userMail}");
            } else {
                $logger->info("No user found with email: {$userMail}");
                $this->info("No user found with email: {$userMail}");
            }
        }

        $logger->info("Reconciled all ugc pois");
        $this->info("Reconciled all ugc pois");
    }
}
