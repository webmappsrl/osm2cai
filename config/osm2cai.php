<?php

return [
    /**
     * Minimum distance from first and last point used to identify automatically roundtrip hiking routes
     */
    'roundtrip_thrashold' => env('OSM2CAI_ROUNDTRIP_THRASHOLD', 100),

    'region_istat_name' => [
        '1' => 'Piemonte',
        '2' => "Valle d'Aosta",
        '3' => 'Lombardia',
        '4' => 'Trentino Alto Adige',
        '5' => 'Veneto',
        '6' => 'Friuli Venezia Giulia',
        '7' => 'Liguria',
        '8' => 'Emilia Romagna',
        '9' => 'Toscana',
        '10' => 'Umbria',
        '11' => 'Marche',
        '12' => 'Lazio',
        '13' => 'Abruzzo',
        '14' => 'Molise',
        '15' => 'Campania',
        '16' => 'Puglia',
        '17' => 'Basilicata',
        '18' => 'Calabria',
        '19' => 'Sicilia',
        '20' => 'Sardegna'
    ]
];