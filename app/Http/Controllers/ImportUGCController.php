<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UgcPoi;
use App\Models\UgcMedia;
use App\Models\UgcTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class ImportUGCController extends Controller
{
    public function importUGCFromGeohub(Request $request)
    {
        try {
            $appId = $request->input('app_id');

            Artisan::call('osm2cai:sync-ugc', [
                'app_id' => $appId
            ]);

            $output = Artisan::output();

            // Parsing dell'output per estrarre i dati necessari per la view
            $createdElements = $this->parseCreatedElements($output);
            $updatedElements = $this->parseUpdatedElements($output);

            Log::channel('import-ugc')->info('Import process completed. Created elements: ' . json_encode($createdElements) . ', Updated elements: ' . json_encode($updatedElements));

            return view('importedUgc', array_merge($createdElements, ['updatedElements' => $updatedElements]));
        } catch (\Exception $e) {
            Log::channel('import-ugc')->error('Error occurred during import process: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in file ' . $e->getFile());
            return response()->json(['error' => 'An error occurred during the import process. Please try again later.'], 500);
        }
    }

    private function parseCreatedElements($output)
    {
        preg_match_all('/Creato nuovo (\w+) con id (\d+)/', $output, $matches);
        $createdElements = [
            'poi' => 0,
            'track' => 0,
            'media' => 0
        ];
        foreach ($matches[1] as $index => $type) {
            $createdElements[$type]++;
        }
        return $createdElements;
    }

    private function parseUpdatedElements($output)
    {
        preg_match_all('/Aggiornato (\w+) con geohub id (\d+)/', $output, $matches);
        $updatedElements = [];
        foreach ($matches[1] as $index => $type) {
            $updatedElements[] = ucfirst($type) . ' with id ' . $matches[2][$index] . ' updated';
        }
        return $updatedElements;
    }
}
