<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Province;
use App\Models\Region;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProductionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $regions = config('geometry_mapping.regions');

        foreach ($regions as $key => $name) {
            $regions[$key] = Region::where('code', '=', $key)->first()->id;
        }

        $users = [
            [
                'name' => 'Webmapp Team',
                'email' => 'team@webmapp.it',
                'email_verified_at' => now(),
                'password' => bcrypt('webmapp'),
                'is_administrator' => true,
                'is_national_referent' => true
            ],
            [
                'name' => 'Fabrizio Savini',
                'email' => 'sosecguser@cai.it',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => true,
                'is_national_referent' => true
            ],
            [
                'name' => 'Alessio Piccioli',
                'email' => 'alessiopiccioli@webmapp.it',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => true
            ],
            [
                'name' => 'Andrea Del Sarto',
                'email' => 'andreadel84@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => true
            ],
            [
                'name' => 'Marco Barbieri',
                'email' => 'marcobarbieri@webmapp.it',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => true
            ],
            [
                'name' => 'Luca De Lucchi',
                'email' => 'luca.delucchi@fmach.it ',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => true
            ],
            [
                'name' => 'Alessandro Geri',
                'email' => 'aldogeri@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => true
            ],
            [
                'name' => 'Alfredo Gattai',
                'email' => 'alfredo.gattai@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => true
            ],
            [
                'name' => 'Renato Boschi',
                'email' => 'boschirenato@tiscali.it',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => true
            ],
            [
                'name' => 'Enrico Sala',
                'email' => 'enrico.sala@unimi.it',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => true
            ],
            [
                'name' => 'Luca Grimaldi',
                'email' => 'lucagri@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => true
            ],
            [
                'name' => 'Luciano Turriani',
                'email' => 'turluc47@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => false,
                'region_id' => $regions["L"]
            ],
            [
                'name' => 'Giancarlo Tellini',
                'email' => 'giancarlo.tellini@caitoscana.it',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => false,
                'region_id' => $regions["L"]
            ],
            [
                'name' => 'Simone Bufalini',
                'email' => 'bufalini.simone@cemes-spa.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => false
            ],
            [
                'name' => 'Aldo Mancini',
                'email' => 'aldo2346@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => false,
                'region_id' => $regions["O"]
            ],
            [
                'name' => 'Gianbattista Condorelli',
                'email' => 'giambattista.condorelli@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => false,
                'region_id' => $regions["V"]
            ],
            [
                'name' => 'Vincenzo Agliata',
                'email' => 'v.agliata@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => false
            ],
            [
                'name' => 'Danilo Baggini',
                'email' => 'danilo.baggini@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => false
            ],
            [
                'name' => 'Carlo Prosperi',
                'email' => 'carlopr54@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('osm2cai'),
                'is_administrator' => false,
                'is_national_referent' => false
            ]
        ];

        Log::info("Creating users...");
        foreach ($users as $user) {
            if (is_null(User::where('email', '=', $user['email'])->first()))
                User::factory(1)->create($user);
            else
                Log::info("User " . $user['email'] . " already exists. Insertion skipped");
        }
        Log::info("Users created successfully");

        Log::info("Adding LPIO1, LLUO1, LLUA1, LMSA sectors to bufalini.simone@cemes-spa.com...");
        $user = User::where('email', '=', 'bufalini.simone@cemes-spa.com')->first();
        $sectorCodes = ["LPIO1", "LLUO1", "LLUA1", "LMSA1"];
        $sectorIds = [];
        foreach ($sectorCodes as $code) {
            $sector = Sector::where("full_code", "=", $code)->first();
            if (!is_null($sector) && isset($sector->id))
                $sectorIds[] = $sector->id;
        }
        $user->sectors()->sync($sectorIds);
        Log::info(count($user->sectors) . " sectors added");

        Log::info("Adding VCT, VME provinces to v.agliata@gmail.com...");
        $user = User::where('email', '=', 'v.agliata@gmail.com')->first();
        $provinceCodes = ["VCT", "VME"];
        $provinceIds = [];
        foreach ($provinceCodes as $code) {
            $province = Province::where("full_code", "=", $code)->first();
            if (!is_null($province) && isset($province->id))
                $provinceIds[] = $province->id;
        }
        $user->provinces()->sync($provinceIds);
        Log::info(count($user->provinces) . " provinces added");

        Log::info("Adding EVBA, ETON areas to danilo.baggini@gmail.com...");
        $user = User::where('email', '=', 'danilo.baggini@gmail.com')->first();
        $areaCodes = ["EVBA", "ETON"];
        $areaIds = [];
        foreach ($areaCodes as $code) {
            $area = Area::where("full_code", "=", $code)->first();
            if (!is_null($area) && isset($area->id))
                $areaIds[] = $area->id;
        }
        $user->areas()->sync($areaIds);
        Log::info(count($user->areas) . " areas added");


        Log::info("Adding HPR province, HMOC area and HPCO9 sector to carlopr54@gmail.com...");
        $user = User::where('email', '=', 'carlopr54@gmail.com')->first();
        $sectorCodes = ["HPCO9"];
        $areaCodes = ["HMOC"];
        $provinceCodes = ["HPR"];
        $ids = [];
        foreach ($provinceCodes as $code) {
            $province = Province::where("full_code", "=", $code)->first();
            if (!is_null($province) && isset($province->id))
                $ids[] = $province->id;
        }
        $user->provinces()->sync($ids);
        $ids = [];
        Log::info(count($user->provinces) . " provinces added");
        foreach ($areaCodes as $code) {
            $area = Area::where("full_code", "=", $code)->first();
            if (!is_null($area) && isset($area->id))
                $ids[] = $area->id;
        }
        $user->areas()->sync($ids);
        $ids = [];
        Log::info(count($user->areas) . " areas added");
        foreach ($sectorCodes as $code) {
            $sector = Sector::where("full_code", "=", $code)->first();
            if (!is_null($sector) && isset($sector->id))
                $ids[] = $sector->id;
        }
        $user->sectors()->sync($ids);
        Log::info(count($user->sectors) . " sectors added");

    }
}
