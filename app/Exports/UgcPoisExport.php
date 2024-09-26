<?php

namespace App\Exports;

use App\Models\UgcPoi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UgcPoisExport implements FromCollection, WithHeadings, WithStyles
{
    protected $formId;

    public function __construct($formId)
    {
        $this->formId = $formId;
    }

    /**
     * Restituisce una collezione di dati da esportare.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        // Recupera i POI in base al form_id e calcola latitudine e longitudine usando PostGIS
        $pois = UgcPoi::where('form_id', $this->formId)
            ->select(
                '*',
                DB::raw('ST_Y(geometry) AS latitude'),
                DB::raw('ST_X(geometry) AS longitude')
            )
            ->get();

        // Mappa i POI per estrarre i campi necessari
        return $pois->map(function ($poi) {
            // Calcola la latitudine e la longitudine
            $lat = $poi->latitude ?? 'N/A';
            $lon = $poi->longitude ?? 'N/A';

            // Estrarre i campi comuni
            $commonFields = [
                'name' => $poi->user->name ?? 'N/A',
                'email' => $poi->user->email ?? 'N/A',
                'registered_at' => $poi->registered_at ?? 'N/A',
                'latitude' => $lat ?? 'N/A',
                'longitude' => $lon ?? 'N/A',
            ];

            // Estrarre i campi specifici usando la configurazione dal file config
            $specificFields = $this->extractSpecificFields($poi);

            // Restituisce l'array combinato dei campi comuni e specifici
            return array_merge($commonFields, $specificFields);
        });
    }

    /**
     * Definisce le intestazioni delle colonne.
     *
     * @return array
     */
    public function headings(): array
    {
        // Intestazioni comuni
        $commonHeaders = [
            'Nome utente',
            'Email utente',
            'Data di acquisizione',
            'Latitudine',
            'Longitudine',
        ];

        // Intestazioni specifiche recuperate dalla configurazione
        $specificHeaders = $this->getSpecificFieldNames();

        return array_merge($commonHeaders, $specificHeaders);
    }

    /**
     * Estrae i campi specifici dai dati raw del POI.
     *
     * @param \App\Models\UgcPoi $poi
     * @return array
     */
    private function extractSpecificFields($poi): array
    {
        // Recupera i campi specifici dalla configurazione in base al form_id
        $fieldsConfig = config("osm2cai.ugc_pois_forms.{$this->formId}.fields", []);

        $specificFields = [];
        foreach ($fieldsConfig as $field) {
            // Recupera il nome del campo
            $fieldName = $field['name'];
            // Ottieni il valore dal raw_data del POI
            $fieldValue = Arr::get($poi->raw_data, $fieldName, 'N/A');

            // Controlla se il campo ha dei valori con delle etichette
            if (isset($field['values']) && is_array($field['values'])) {
                // Cerca il valore corrispondente nei valori configurati
                $valueLabel = collect($field['values'])->firstWhere('value', $fieldValue);
                // Imposta l'etichetta corrispondente se esiste, altrimenti usa il valore originale
                $fieldValue = $valueLabel['label']['it'] ?? $fieldValue;
            }

            // Aggiungi il campo con l'etichetta corretta o il valore originale
            $specificFields[$field['label']['it'] ?? $fieldName] = $fieldValue ?? 'N/A';
        }

        return $specificFields;
    }

    /**
     * Recupera i nomi dei campi specifici per le intestazioni.
     *
     * @return array
     */
    private function getSpecificFieldNames(): array
    {
        // Recupera i nomi dei campi specifici per le intestazioni, basato sulla configurazione del form_id
        $fieldsConfig = config("osm2cai.ugc_pois_forms.{$this->formId}.fields", []);

        // Estrai i nomi (label) dei campi per le intestazioni, considerando solo la lingua italiana (o inglese se preferisci)
        return array_map(function ($field) {
            return $field['label']['it'] ?? $field['name'];
        }, $fieldsConfig);
    }

    /**
     * Applica stili al foglio di lavoro.
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Applica l'allineamento a sinistra per tutte le celle
        $sheet->getStyle($sheet->calculateWorksheetDimension())
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        return [];
    }
}
