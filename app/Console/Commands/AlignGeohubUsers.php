<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User; // Il modello User di osm2cai
use Illuminate\Support\Facades\Hash;

class AlignGeohubUsers extends Command
{
    protected $signature = 'osm2cai:align-geohub-users';
    protected $description = 'Allinea gli utenti di Geohub con Osm2cai basandosi sulle email non registrate in Osm2cai ma con UGC_POIs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Inizio del processo di allineamento utenti...');

        // Percorso del file contenente le email degli utenti da allineare
        $filePath = storage_path('geohub_users_not_registered.txt'); // Assicurati di includere l'estensione del file

        // Controlliamo se il file esiste usando file_exists
        if (!file_exists($filePath)) {
            $this->error("Il file $filePath non esiste!");
            return 1;
        }

        // Usa file_get_contents per leggere il contenuto del file
        $fileContents = file_get_contents($filePath);
        $emails = explode("\n", $fileContents);

        // Filtriamo eventuali email vuote
        $emails = array_filter($emails);

        $this->info('Trovate ' . count($emails) . ' email da processare.');

        // Per ogni email, cerchiamo l'utente in geohub e lo importiamo in osm2cai
        foreach ($emails as $email) {
            $email = trim($email); // Rimuoviamo eventuali spazi vuoti

            // Verifichiamo se l'utente esiste già su osm2cai
            $userExistsInOsm2cai = User::where('email', $email)->exists();

            if (!$userExistsInOsm2cai) {
                // Se l'utente non esiste su osm2cai, cerchiamo nel database geohub
                $userFromGeohub = DB::connection('geohub')->table('users')->where('email', $email)->first();

                if ($userFromGeohub) {
                    // L'utente esiste su geohub, lo creiamo su osm2cai
                    $this->info("Creazione dell'utente per l'email: $email");

                    User::create([
                        'name' => $userFromGeohub->name . ' ' . $userFromGeohub->last_name,
                        'email' => $email,
                        'password' => $userFromGeohub->password,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // L'utente non esiste nemmeno su geohub
                    $this->error("L'utente con email $email non esiste su geohub.");
                }
            } else {
                // L'utente esiste già su osm2cai
                $this->info("L'utente con email $email esiste già su osm2cai.");
            }
        }

        $this->info('Allineamento completato!');

        //lancia il comando di riconciliazione user_no_match
        $this->info('Inizio della riconciliazione user_no_match...');
        $this->call('osm2cai:reconcile-users');

        $this->info('Riconciliazione completata!');
        return 0;
    }
}
