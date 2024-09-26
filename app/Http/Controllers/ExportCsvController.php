<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\UgcPoisExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportCsvController extends Controller
{
    /**
     * Mostra il form per selezionare il form_id.
     *
     * @return \Illuminate\View\View
     */
    public function showForm()
    {
        // Definisci le opzioni di form_id in base alla configurazione
        $formOptions = [
            'paths' => 'Sentieristica',
            'report' => 'Segnalazione Problemi',
            'poi' => 'Punti di Interesse',
            'water' => 'Acqua Sorgente',
            'signs' => 'Segni dell\'uomo',
            'archaeological_area' => 'Aree Archeologiche',
            'archaeological_site' => 'Siti Archeologici',
            'geological_site' => 'Siti Geologici',
        ];

        return view('exports.form', compact('formOptions'));
    }

    /**
     * Gestisce l'esportazione del file CSV.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        // Validazione del form_id
        $request->validate([
            'form_id' => 'required|string|in:paths,report,poi,water,signs,archaeological_area,archaeological_site,geological_site',
        ]);

        $formId = $request->input('form_id');

        // Definisci il nome del file CSV
        $fileName = 'OSM2CAI_ugc_export_' . $formId . '_' . now()->format('Ymd') . '.csv';

        // Usa Excel::download per creare e inviare il file CSV al browser
        return Excel::download(new UgcPoisExport($formId), $fileName);
    }
}
