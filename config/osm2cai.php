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
    ],

    'hiking_route_buffer' => 250,

    'osmTagsMapping' => [
        'amenity' => [
            'monastery' => 'monastery',
            'castle' => 'castle',
            'place_of_worship' => 'place_of_worship',
            'ruins' => 'ruins',
            'museum' => 'museum',
            'theatre' => 'theatre',
        ],
        'historic' => [
            'castle' => 'castle',
            'archeological_site' => 'archeological_site',
            'tower' => 'tower',
            'city_gate' => 'city_gate',
            'ruins' => 'ruins',
            'museum' => 'museum',
        ],
        'building' => [
            'castle' => 'castle',
            'monastery' => 'monastery',
            'ruins' => 'ruins',
            'tower' => 'tower',
            'museum' => 'museum',
        ],
        'religion' => [
            'christian' => 'christian',
        ],
        'man_made' => [
            'tower' => 'tower',
        ],
        'tourism' => [
            'museum' => 'museum',
            'theatre' => 'theatre',
        ],
        'natural' => [
            'cave_entrance' => 'cave_entrance',
            'peak' => 'peak',
            'saddle' => 'saddle',
            'volcano' => 'volcano',
            'cliff' => 'cliff',
            'water' => 'water',
            'hot_spring' => 'hot_spring',
            'spring' => 'spring',
        ],
        'water' => [
            'lake' => 'lake',
            'river' => 'river',
            'waterfall' => 'waterfall',
        ],
    ],

    'ugc_pois_forms' => [
        'water' => [
            'label' => ['it' => 'ACQUA SORGENTE', 'en' => 'SPRING WATER'],
            'fields' => [
                [
                    'name' => 'title',
                    'type' => 'text',
                    'required' => true,
                    'helper' => [
                        'it' => 'Inserisci un nome che ti ricorda la sorgente',
                        'en' => 'Enter a name that reminds you of the spring',
                    ],
                    'label' => [
                        'it' => 'Nome',
                        'en' => 'Name',
                    ],
                ],
                [
                    'name' => 'active',
                    'type' => 'select',
                    'required' => true,
                    'helper' => [
                        'it' => 'Se dalla sorgente fuoriesce acqua, allora è attiva',
                        'en' => 'If water comes out of the spring, then it is active',
                    ],
                    'label' => [
                        'it' => 'SORGENTE ATTIVA',
                        'en' => 'ACTIVE SPRING',
                    ],
                    'values' => [
                        [
                            'value' => 'yes',
                            'label' => [
                                'it' => 'Si',
                                'en' => 'Yes',
                            ],
                        ],
                        [
                            'value' => 'no',
                            'label' => [
                                'it' => 'No',
                                'en' => 'No',
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'range_volume',
                    'type' => 'text',
                    'required' => false,
                    'helper' => [
                        'it' => 'Inserisci il volume in litri del contenitore che usi per misurare la portata. [solo numeri]',
                        'en' => 'Enter the volume in litres of the container you use to measure the flow rate. [numbers only]',
                    ],
                    'label' => [
                        'it' => 'VOLUME DEL CONTENITORE IN LITRI',
                        'en' => 'CONTAINER VOLUME IN LITRES',
                    ],
                ],
                [
                    'name' => 'range_time',
                    'type' => 'text',
                    'required' => false,
                    'helper' => [
                        'it' => 'Inserisci il tempo di riempimento del contenitore in secondi [solo numeri, p.es. 2 minuti e 35 secondi inserisci 155]',
                        'en' => 'Enter the container filling time in seconds [numbers only, e.g. 2 minutes and 35 seconds enter 155]',
                    ],
                    'label' => [
                        'it' => 'TEMPO DI RIEPIMENTO IN SECONDI',
                        'en' => 'FILL TIME IN SECONDS',
                    ],
                ],
                [
                    'name' => 'conductivity',
                    'type' => 'text',
                    'required' => false,
                    'helper' => [
                        'it' => 'Se hai il conduttimetro inserisci il valore di conducibilità elettrica misurato [solo numeri]',
                        'en' => 'If you have the conductivity meter, enter the measured electrical conductivity value [numbers only]',
                    ],
                    'label' => [
                        'it' => 'CONDUCIBILITÀ ELETTRICA',
                        'en' => 'ELECTRIC CONDUCTIVITY',
                    ],
                ],
                [
                    'name' => 'temperature',
                    'type' => 'text',
                    'required' => false,
                    'helper' => [
                        'it' => 'Se hai il conduttimetro inserisci il valore della temperatura misurato [solo numeri]',
                        'en' => 'If you have the conductivity meter, enter the measured temperature value [numbers only]',
                    ],
                    'label' => [
                        'it' => 'TEMPERATURA',
                        'en' => 'TEMPERATURE',
                    ],
                ],
                [
                    'name' => 'info',
                    'type' => 'textarea',
                    'required' => false,
                    'helper' => [
                        'it' => 'Se vuoi, inserisci ulteriori informazioni sulla sorgente e sul rilievo',
                        'en' => 'If you want, enter additional information about the source and relief',
                    ],
                    'label' => [
                        'it' => 'NOTE',
                        'en' => 'NOTES',
                    ],
                ],
            ]
        ],
        'poi' => [
            'label' => ['it' => 'Punto di interesse', 'en' => 'Point of interest'],
            'fields' => [
                [
                    'name' => 'title',
                    'type' => 'text',
                    'placeholder' => ['it' => 'Inserisci un titolo', 'en' => 'Enter a title'],
                    'required' => true,
                    'label' => ['it' => 'Titolo', 'en' => 'Title'],
                ],
                [
                    'name' => 'waypointtype',
                    'type' => 'select',
                    'required' => true,
                    'label' => ['it' => 'Tipo di punto di interesse', 'en' => 'Point of interest type'],
                    'values' => [
                        [
                            'value' => 'landscape',
                            'label' => ['it' => 'Panorama', 'en' => 'Landscape'],
                        ],
                        [
                            'value' => 'place_to_eat',
                            'label' => ['it' => 'Luogo dove mangiare', 'en' => 'Place to eat'],
                        ],
                        [
                            'value' => 'place_to_sleep',
                            'label' => ['it' => 'Luogo dove dormire', 'en' => 'Place to sleep'],
                        ],
                        [
                            'value' => 'natural',
                            'label' => ['it' => 'Luogo di interesse naturalistico', 'en' => 'Place of naturalistic interest'],
                        ],
                        [
                            'value' => 'cultural',
                            'label' => ['it' => 'Luogo di interesse culturale', 'en' => 'Place of cultural interest'],
                        ],
                        [
                            'value' => 'other',
                            'label' => ['it' => 'Altri tipi di luoghi di interesse', 'en' => 'Other types of point of interest'],
                        ],
                    ]
                ],
                [
                    'name' => 'description',
                    'type' => 'textarea',
                    'placeholder' => ['it' => 'Se vuoi puoi aggiungere una descrizione', 'en' => 'You can add a description if you want'],
                    'required' => false,
                    'label' => ['it' => 'Descrizione', 'en' => 'Description'],
                ],
            ]
        ],
        'paths' => [
            'label' => ['it' => 'Sentieristica', 'en' => 'Path'],
            'helper' => [
                'it' => 'Helper per la sentieristica',
                'en' => 'Path Maintenance helper',
            ],
            'fields' => [
                [
                    'name' => 'title',
                    'type' => 'text',
                    'required' => true,
                    'label' => [
                        'it' => 'Titolo',
                        'en' => 'Title',
                    ],
                ],
                [
                    'name' => 'description',
                    'type' => 'textarea',
                    'required' => false,
                    'label' => [
                        'it' => 'Descrizione',
                        'en' => 'Description',
                    ],
                ],
                [
                    'name' => 'paths_poi_type',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Punto relativo alla sentieristica',
                        'en' => 'Point related to path',
                    ],
                    'values' => [
                        [
                            'value' => 'start',
                            'label' => [
                                'it' => 'Inizio',
                                'en' => 'Start',
                            ],
                        ],
                        [
                            'value' => 'end',
                            'label' => [
                                'it' => 'Fine',
                                'en' => 'End',
                            ],
                        ],
                        [
                            'value' => 'type_change',
                            'label' => [
                                'it' => 'Cambio tipo',
                                'en' => 'Type Change',
                            ],
                        ],
                        [
                            'value' => 'surface_change',
                            'label' => [
                                'it' => 'Cambio superficie',
                                'en' => 'Surface Change',
                            ],
                        ],
                        [
                            'value' => 'signs',
                            'label' => [
                                'it' => 'Segnaletica',
                                'en' => 'Signs',
                            ],
                        ],
                        [
                            'value' => 'other',
                            'label' => [
                                'it' => 'Altro',
                                'en' => 'Other',
                            ],
                        ],
                    ],
                ],
            ]
        ],
        'report' => [
            'label' => ['it' => 'Segnalazione', 'en' => 'Report'],
            'helper' => [
                'it' => 'Helper per la segnalazione di problematiche',
                'en' => 'Report helper',
            ],
            'fields' => [
                [
                    'name' => 'title',
                    'type' => 'text',
                    'required' => true,
                    'label' => [
                        'it' => 'Titolo segnalazione',
                        'en' => 'Report title',
                    ],
                ],
                [
                    'name' => 'description',
                    'type' => 'textarea',
                    'required' => false,
                    'label' => [
                        'it' => 'Descrizione',
                        'en' => 'Description',
                    ],
                ],
                [
                    'name' => 'report_type',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Tipo di segnalazione',
                        'en' => 'Report type',
                    ],
                    'values' => [
                        [
                            'value' => 'fallen_tree',
                            'label' => [
                                'it' => 'Albero Caduto',
                                'en' => 'Fallen Tree',
                            ],
                        ],
                        [
                            'value' => 'landslide',
                            'label' => [
                                'it' => 'Frana',
                                'en' => 'Landslide',
                            ],
                        ],
                        [
                            'value' => 'vegetation',
                            'label' => [
                                'it' => 'Vegetazione',
                                'en' => 'Vegetation',
                            ],
                        ],
                        [
                            'value' => 'missing_signs',
                            'label' => [
                                'it' => 'Segnaletica Mancante',
                                'en' => 'Missing Signs',
                            ],
                        ],
                        [
                            'value' => 'artifacts',
                            'label' => [
                                'it' => 'Artefatto',
                                'en' => 'Artifact',
                            ],
                        ],
                        [
                            'value' => 'other',
                            'label' => [
                                'it' => 'Altro',
                                'en' => 'Other',
                            ],
                        ],
                    ],
                ],
            ]
        ],
        'signs' => [
            'default' => false,
            'label' => ['it' => 'Segni dell\'uomo', 'en' => 'Signs of Man'],
            'helper' => [
                'it' => 'Helper per i segni dell\'uomo',
                'en' => 'Helper for signs of man',
            ],
            'fields' => [
                [
                    'name' => 'artifact_type',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Tipo di manufatto',
                        'en' => 'Type of artifact',
                    ],
                    'values' => [
                        [
                            'value' => 'ancient_paved_paths',
                            'label' => [
                                'it' => 'Antichi percorsi lastricati',
                                'en' => 'Ancient paved paths'
                            ]
                        ],
                        [
                            'value' => 'boundary_stones',
                            'label' => [
                                'it' => 'Cippi confinari',
                                'en' => 'Boundary stones'
                            ]
                        ],
                        [
                            'value' => 'high_altitude_shepherd_huts',
                            'label' => [
                                'it' => 'Capanne pastorali',
                                'en' => 'High altitude shepherd huts'
                            ]
                        ],
                        [
                            'value' => 'high_altitude_alpine_buildings',
                            'label' => [
                                'it' => 'Edifici di alpeggio e ricoveri',
                                'en' => 'High altitude alpine buildings and shelters'
                            ]
                        ],
                        [
                            'value' => 'chestnut_drying_sheds',
                            'label' => [
                                'it' => 'Essiccatoi per castagne',
                                'en' => 'Chestnut drying sheds'
                            ]
                        ],
                        [
                            'value' => 'tower_ruins',
                            'label' => [
                                'it' => 'Torri, punti di avvistamento e vedette',
                                'en' => 'Towers, lookout points, and watchtowers'
                            ]
                        ],
                        [
                            'value' => 'temporary_rural_buildings',
                            'label' => [
                                'it' => 'Insediamento temporaneo in quota',
                                'en' => 'Temporary rural buildings in high altitude settlements'
                            ]
                        ],
                        [
                            'value' => 'devotional_images',
                            'label' => [
                                'it' => 'Immagini e manufatti devozionali',
                                'en' => 'Devotional images and artifacts'
                            ]
                        ],
                        [
                            'value' => 'rock_engravings',
                            'label' => [
                                'it' => 'Incisioni o scritte su roccia',
                                'en' => 'Rock engravings or stone inscriptions'
                            ]
                        ],
                        [
                            'value' => 'shepherd_hut_ruins',
                            'label' => [
                                'it' => 'Basi di capanne pastorali',
                                'en' => 'Bases of shepherd huts'
                            ]
                        ],
                        [
                            'value' => 'dry_stone_piles',
                            'label' => [
                                'it' => 'Accumuli di pietre a secco',
                                'en' => 'Piles of dry stones'
                            ]
                        ],
                        [
                            'value' => 'trenches',
                            'label' => [
                                'it' => 'Trincee e punti difensivi',
                                'en' => 'Trenches and defensive arrangements'
                            ]
                        ],
                        [
                            'value' => 'rock_settlements',
                            'label' => [
                                'it' => 'Insediamenti o siti di culto ipogei',
                                'en' => 'Settlements and ancient hypogean cult sites'
                            ]
                        ]
                    ],
                ],
                [
                    'name' => 'location',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Dove si trova il manufatto?',
                        'en' => 'Where is the artifact located?'
                    ],
                    'values' => [
                        [
                            'value' => 'along_trail',
                            'label' => [
                                'it' => 'Lungo il sentiero',
                                'en' => 'Along the trail'
                            ]
                        ],
                        [
                            'value' => 'not_accessible_from_trail',
                            'label' => [
                                'it' => 'Non raggiungibile dal sentiero',
                                'en' => 'Not accessible from the trail'
                            ]
                        ]
                    ],
                ],
                [
                    'name' => 'conservation_status',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Stato di conservazione del manufatto',
                        'en' => 'Conservation Status of the Artifact'
                    ],
                    'values' => [
                        [
                            'value' => 'intact_accessible',
                            'label' => [
                                'it' => 'Integro visitabile/accessibile',
                                'en' => 'Intact, visitable/accessible'
                            ]
                        ],
                        [
                            'value' => 'intact_not_accessible',
                            'label' => [
                                'it' => 'Integro ma non visitabile/accessibile',
                                'en' => 'Intact but not visitable/accessible'
                            ]
                        ],
                        [
                            'value' => 'partially_ruined',
                            'label' => [
                                'it' => 'Parzialmente diroccato',
                                'en' => 'Partially ruined'
                            ]
                        ],
                        [
                            'value' => 'elevated_ruins',
                            'label' => [
                                'it' => 'Ruderi soprelevati',
                                'en' => 'Elevated ruins'
                            ]
                        ],
                        [
                            'value' => 'visible_only_in_plan',
                            'label' => [
                                'it' => 'Visibile solo in pianta',
                                'en' => 'Visible only in plan'
                            ]
                        ]
                    ],
                ],
                [
                    'name' => 'notes',
                    'type' => 'textarea',
                    'required' => false,
                    'label' => [
                        'it' => 'Note',
                        'en' => 'Notes'
                    ],
                    'helper' => [
                        'it' => 'Contatti per visita, nome locale, avvisi di prudenza ecc..',
                        'en' => 'Contacts for visits, local name, caution notices, etc.'
                    ],
                ]
            ]
        ],
        'archaeological_site' => [
            'default' => false,
            'label' => ['it' => 'Sito archeologico noto', 'en' => 'Archaeological Site'],
            'helper' => [
                'it' => 'Un sito archeologico preistorico già noto e valorizzato sul Sentiero',
                'en' => 'An archaeological site of prehistory already known and valued on the Path',
            ],
            'fields' => [
                [
                    'name' => 'title',
                    'type' => 'text',
                    'required' => true,
                    'label' => [
                        'it' => 'Nome del sito',
                        'en' => 'Site name',
                    ],
                ],
                [
                    'name' => 'location',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Dove si trova il sito?',
                        'en' => 'Where is the site?',
                    ],
                    'values' => [
                        [
                            'value' => 'on_trail',
                            'label' => [
                                'it' => 'Sul sentiero',
                                'en' => 'On the trail'
                            ]
                        ],
                        [
                            'value' => 'not_accessible_from_trail',
                            'label' => [
                                'it' => 'Non raggiungibile dal sentiero',
                                'en' => 'Not accessible from the trail'
                            ]
                        ]
                    ],
                ],
                [
                    'name' => 'condition',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'In che stato si trova il sito?',
                        'en' => 'What is the condition of the site?'
                    ],
                    'values' => [
                        [
                            'value' => 'intact_accessible',
                            'label' => [
                                'it' => 'Integro e visitabile/accessibile',
                                'en' => 'Intact and visitable/accessible'
                            ]
                        ],
                        [
                            'value' => 'intact_not_accessible',
                            'label' => [
                                'it' => 'Integro ma non visitabile/accessibile',
                                'en' => 'Intact but not visitable/accessible'
                            ]
                        ],
                        [
                            'value' => 'intact_at_risk',
                            'label' => [
                                'it' => 'Integro ma in area sottoposta a erosione o altro pericolo per la conservazione',
                                'en' => 'Intact but in an area subject to erosion or other conservation danger'
                            ]
                        ],
                        [
                            'value' => 'degraded',
                            'label' => [
                                'it' => 'In stato di degrado o abbandono',
                                'en' => 'In a state of degradation or abandonment'
                            ]
                        ],
                        [
                            'value' => 'not_visible',
                            'label' => [
                                'it' => 'Non visibile',
                                'en' => 'Not visible'
                            ]
                        ]
                    ],
                ],
                [
                    'name' => 'informational_supports',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'La pannellistica e gli altri supporti informativi sono adeguati?',
                        'en' => 'Are the panels and other informational supports adequate?'
                    ],
                    'values' => [
                        [
                            'value' => 'adequate',
                            'label' => [
                                'it' => 'Sì, permettono la fruizione del contesto',
                                'en' => 'Yes, they allow the use of the context'
                            ]
                        ],
                        [
                            'value' => 'insufficient',
                            'label' => [
                                'it' => 'No, mancano informazioni sufficientemente dettagliate',
                                'en' => 'No, there is a lack of sufficiently detailed information'
                            ]
                        ],
                        [
                            'value' => 'not_present',
                            'label' => [
                                'it' => 'Non presenti',
                                'en' => 'Not present'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'notes',
                    'type' => 'textarea',
                    'required' => false,
                    'label' => [
                        'it' => 'Note',
                        'en' => 'Notes'
                    ],
                    'helper' => [
                        'it' => 'Contatti per visita, nome locale, avvisi di prudenza, consigli di valorizzazione o protezione…ecc',
                        'en' => 'Contacts for visits, local name, caution notices, enhancement or protection advice, etc.'
                    ],
                ]
            ]
        ],
        'archaeological_area' => [
            'default' => false,
            'label' => [
                'it' => 'Area interesse archeologia',
                'en' => 'Archaeological Area'
            ],
            'helper' => [
                'it' => 'Un\'area interessante dal punto di vista archeologico',
                'en' => 'An area of archaeological interest'
            ],
            'fields' => [
                [
                    'name' => 'title',
                    'type' => 'text',
                    'required' => true,
                    'label' => [
                        'it' => 'Nome dell\'area',
                        'en' => 'Name of the area'
                    ]
                ],
                [
                    'name' => 'area_type',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Tipo di area',
                        'en' => 'Type of Area'
                    ],
                    'values' => [
                        [
                            'value' => 'cave',
                            'label' => [
                                'it' => 'Grotta o covolo',
                                'en' => 'Cave or cavern'
                            ]
                        ],
                        [
                            'value' => 'rock_shelter',
                            'label' => [
                                'it' => 'Riparo sottoroccia',
                                'en' => 'Rock shelter'
                            ]
                        ],
                        [
                            'value' => 'erratic_boulder',
                            'label' => [
                                'it' => 'Masso erratico',
                                'en' => 'Erratic boulder'
                            ]
                        ],
                        [
                            'value' => 'flat_area_near_water',
                            'label' => [
                                'it' => 'Area pianeggiante all\'aperto nelle vicinanze di un bacino o di un corso d\'acqua',
                                'en' => 'Flat open area near a basin or watercourse'
                            ]
                        ],
                        [
                            'value' => 'tumular_structures',
                            'label' => [
                                'it' => 'Area con presenza di strutture a carattere tumulare (specchie o cairn)',
                                'en' => 'Area with tumular structures (specchie or cairn)'
                            ]
                        ],
                        [
                            'value' => 'hypogean_chamber_access',
                            'label' => [
                                'it' => 'Accesso a camera ipogea sotterranea',
                                'en' => 'Access to underground hypogean chamber'
                            ]
                        ],
                        [
                            'value' => 'erosion_front',
                            'label' => [
                                'it' => 'Fronte erosivo di versante (frana, smottamento)',
                                'en' => 'Erosion front of slope (landslide, slip)'
                            ]
                        ],
                        [
                            'value' => 'sinkhole',
                            'label' => [
                                'it' => 'Dolina',
                                'en' => 'Sinkhole'
                            ]
                        ],
                        [
                            'value' => 'swallow_hole',
                            'label' => [
                                'it' => 'Inghiottitoio',
                                'en' => 'Swallow hole'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'location',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Dove si trova l\'area di interesse?',
                        'en' => 'Where is the area of interest located?'
                    ],
                    'values' => [
                        [
                            'value' => 'on_trail',
                            'label' => [
                                'it' => 'Sul sentiero',
                                'en' => 'On the trail'
                            ]
                        ],
                        [
                            'value' => 'not_accessible_from_trail',
                            'label' => [
                                'it' => 'Non raggiungibile dal sentiero',
                                'en' => 'Not accessible from the trail'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'notes',
                    'type' => 'textarea',
                    'required' => false,
                    'label' => [
                        'it' => 'Note',
                        'en' => 'Notes'
                    ],
                    'helper' => [
                        'it' => 'Contatti per visita, nome locale, avvisi di prudenza, motivazioni della segnalazione…',
                        'en' => 'Contacts for visit, local name, caution notices, reasons for reporting...'
                    ]
                ]
            ]
        ],
        'geological_site' => [
            'default' => false,
            'label' => [
                'it' => 'Sito Geologico',
                'en' => 'Geological Site'
            ],
            'helper' => [
                'it' => 'Un\'area interessante dal punto di vista geologico',
                'en' => 'An area of geological interest'
            ],
            'fields' => [
                [
                    'name' => 'title',
                    'type' => 'text',
                    'required' => true,
                    'label' => [
                        'it' => 'Nome del sito',
                        'en' => 'Site name'
                    ]
                ],
                [
                    'name' => 'site_type',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Tipo di sito',
                        'en' => 'Type of Site'
                    ],
                    'values' => [
                        [
                            'value' => 'cave_mines',
                            'label' => [
                                'it' => 'Cave e Miniere',
                                'en' => 'Cave and Mines'
                            ]
                        ],
                        [
                            'value' => 'applied_geology',
                            'label' => [
                                'it' => 'Geologia applicata',
                                'en' => 'Applied Geology'
                            ]
                        ],
                        [
                            'value' => 'stratigraphic_geology',
                            'label' => [
                                'it' => 'Geologia stratigrafica',
                                'en' => 'Stratigraphic Geology'
                            ]
                        ],
                        [
                            'value' => 'structural_geology',
                            'label' => [
                                'it' => 'Geologia strutturale',
                                'en' => 'Structural Geology'
                            ]
                        ],
                        [
                            'value' => 'hydrography_hydrogeology',
                            'label' => [
                                'it' => 'Idrografia e Idrogeologia',
                                'en' => 'Hydrography and Hydrogeology'
                            ]
                        ],
                        [
                            'value' => 'mineralogy',
                            'label' => [
                                'it' => 'Mineralogia',
                                'en' => 'Mineralogy'
                            ]
                        ],
                        [
                            'value' => 'karst_forms',
                            'label' => [
                                'it' => 'Forme carsiche',
                                'en' => 'Karst Forms'
                            ]
                        ],
                        [
                            'value' => 'fluvial_forms',
                            'label' => [
                                'it' => 'Forme fluviali',
                                'en' => 'Fluvial Forms'
                            ]
                        ],
                        [
                            'value' => 'glacial_forms',
                            'label' => [
                                'it' => 'Forme glaciali',
                                'en' => 'Glacial Forms'
                            ]
                        ],
                        [
                            'value' => 'volcanic_forms',
                            'label' => [
                                'it' => 'Forme vulcaniche',
                                'en' => 'Volcanic Forms'
                            ]
                        ],
                        [
                            'value' => 'coastal_forms',
                            'label' => [
                                'it' => 'Forme costiere',
                                'en' => 'Coastal Forms'
                            ]
                        ],
                        [
                            'value' => 'paleontology',
                            'label' => [
                                'it' => 'Paleontologia',
                                'en' => 'Paleontology'
                            ]
                        ],
                        [
                            'value' => 'sedimentary_rocks',
                            'label' => [
                                'it' => 'Rocce sedimentarie',
                                'en' => 'Sedimentary Rocks'
                            ]
                        ],
                        [
                            'value' => 'igneous_rocks',
                            'label' => [
                                'it' => 'Rocce magmatiche',
                                'en' => 'Igneous Rocks'
                            ]
                        ],
                        [
                            'value' => 'metamorphic_rocks',
                            'label' => [
                                'it' => 'Rocce metamorfiche',
                                'en' => 'Metamorphic Rocks'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'location',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Dove si trova il sito?',
                        'en' => 'Where is the site located?'
                    ],
                    'values' => [
                        [
                            'value' => 'along_trail',
                            'label' => [
                                'it' => 'Lungo il sentiero',
                                'en' => 'Along the trail'
                            ]
                        ],
                        [
                            'value' => 'visible_from_trail',
                            'label' => [
                                'it' => 'Visibile in lontananza dal sentiero',
                                'en' => 'Visible from afar on the trail'
                            ]
                        ],
                        [
                            'value' => 'reachable_deviation',
                            'label' => [
                                'it' => 'Raggiungibile con una deviazione di XXX metri dal sentiero',
                                'en' => 'Reachable with a deviation of XXX meters from the trail'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'vulnerability',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Vulnerabilità del sito',
                        'en' => 'Vulnerability of the site'
                    ],
                    'values' => [
                        [
                            'value' => 'high',
                            'label' => [
                                'it' => 'Alta',
                                'en' => 'High'
                            ]
                        ],
                        [
                            'value' => 'medium',
                            'label' => [
                                'it' => 'Media',
                                'en' => 'Medium'
                            ]
                        ],
                        [
                            'value' => 'low',
                            'label' => [
                                'it' => 'Bassa',
                                'en' => 'Low'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'vulnerability_reason',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'Per motivi',
                        'en' => 'For reasons'
                    ],
                    'values' => [
                        [
                            'value' => 'natural',
                            'label' => [
                                'it' => 'Naturali: (frane, dissesti, afforestazione)',
                                'en' => 'Natural: (landslides, instabilities, reforestation)'
                            ]
                        ],
                        [
                            'value' => 'anthropic',
                            'label' => [
                                'it' => 'Antropici',
                                'en' => 'Anthropic'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'ispra_geosite',
                    'type' => 'select',
                    'required' => true,
                    'label' => [
                        'it' => 'E\' un GEOSITO ISPRA?',
                        'en' => 'Is it an ISPRA GEOSITE?'
                    ],
                    'values' => [
                        [
                            'value' => 'yes',
                            'label' => [
                                'it' => 'SI',
                                'en' => 'YES'
                            ]
                        ],
                        [
                            'value' => 'no',
                            'label' => [
                                'it' => 'NO',
                                'en' => 'NO'
                            ]
                        ],
                        [
                            'value' => 'unknown',
                            'label' => [
                                'it' => 'Non so',
                                'en' => 'I don\'t know'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'notes',
                    'type' => 'textarea',
                    'required' => false,
                    'label' => [
                        'it' => 'Note',
                        'en' => 'Notes'
                    ],
                    'helper' => [
                        'it' => 'Contatti per visita, nome locale, avvisi di prudenza, motivazioni della segnalazione…ecc',
                        'en' => 'Contacts for visit, local name, caution notices, reasons for reporting, etc.'
                    ]
                ]
            ]
        ],
    ]

];
