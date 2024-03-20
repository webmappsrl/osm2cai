<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UgcPoi;
use Illuminate\Console\Command;

class UserNoMatchUgcPois extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:user-no-match-ugc-pois';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will update the user_no_match field in the ugc_pois table with the email from geohub and check if the corresponding user exists.';

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
        $noUserUgcPois = UgcPoi::whereNull('user_id')->get();

        foreach ($noUserUgcPois as $ugcPoi) {
            $url = "https://geohub.webmapp.it/api/ugc/poi/geojson/{$ugcPoi->geohub_id}/osm2cai";
            $content = json_decode($this->get_content($url), true);

            if (!$content) {
                $this->error("Failed to fetch content from URL: $url");
                continue;
            }

            if (isset($content['properties']['user_email'])) {
                $ugcPoi->user_no_match = $content['properties']['user_email'];
                $ugcPoi->save();
                $this->info("Updated user_no_match for ugc poi with geohub_id: {$ugcPoi->geohub_id} with email: {$content['properties']['user_email']}");

                $this->info("Checking if user exists for email: {$content['properties']['user_email']}");
                $user = User::where('email', $content['properties']['user_email'])->first();
                if ($user) {
                    $ugcPoi->user_id = $user->id;
                    $ugcPoi->user_no_match = null;
                    $ugcPoi->save();
                    $this->info("User exists for email: {$content['properties']['user_email']}. Updated user_id for ugc poi with geohub_id: {$ugcPoi->geohub_id}");
                } else {
                    $this->error("User does not exist for email: {$content['properties']['user_email']}");
                }
            }
        }
    }

    private function get_content($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        $data = curl_exec($ch);
        if ($data === false) {
            throw new \Exception("Failed to fetch content from URL: $url");
        }
        curl_close($ch);
        return $data;
    }
}
