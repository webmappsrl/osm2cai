<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function mockGetHRRelation($type){
        if($type=='simple')
            return [
              "from_osm" => "Pian dei Bianchi",
              "from" => "Pian dei Bianchi",
              "network_osm" => "lwn",
              "network" => "lwn",
              "osmc_symbol_osm" => "red:red:white_stripe:446A:black",
              "osmc_symbol" => "red:red:white_stripe:446A:black",
              "ref_osm" => "446A",
              "ref" => "446A",
              "ref_REI_osm" => "LPON446A",
              "ref_REI" => "LPON446A",
              "rwn_name_osm" => "Rete Escursionistica Toscana (RET)",
              "rwn_name" => "Rete Escursionistica Toscana (RET)",
              "source_ref_osm" => "9226008",
              "source_ref" => "9226008",
              "to_osm" => "Il Corso",
              "to" => "Il Corso",
              "relation_id" => 9744403
            ];
        else {
            return [
                "ref_osm" => "446A",
                "ref" => "446A",
                "old_ref_osm" => "44AS",
                "old_ref" => "44AS",
                "source_ref_osm" => "9226008",
                "source_ref" => "9226008",
                'survey_date_osm'=>"2022-11-03",
                'survey_date'=>"2022-11-03",
                'name_osm'=>'XXX',
                'name'=>'XXX',
                "rwn_name_osm" => "Rete Escursionistica Toscana (RET)",
                "rwn_name" => "Rete Escursionistica Toscana (RET)",
                "ref_REI_osm" => "LPON446A",
                "ref_REI" => "LPON446A",
                "from_osm" => "Pian dei Bianchi",
                "from" => "Pian dei Bianchi",
                "to_osm" => "Il Corso",
                "to" => "Il Corso",
                "osmc_symbol_osm" => "red:red:white_stripe:446A:black",
                "osmc_symbol" => "red:red:white_stripe:446A:black",
                "network_osm" => "lwn",
                "network" => "lwn",
                'roundtrip_osm'=>'AAA',
                'roundtrip'=>'AAA',
                'symbol_osm'=>'red:red:white_stripe:446A:black',
                'symbol'=>'red:red:white_stripe:446A:black',
                'symbol_it_osm'=>'red:red:white_stripe:446A:black',
                'symbol_it'=>'red:red:white_stripe:446A:black',
                'ascent_osm'=>0.1,
                'ascent'=>0.1,
                'descent_osm'=>0.2,
                'descent'=>0.2,
                'distance_osm'=>"10",
                'distance'=>"10",
                'duration_forward_osm'=>"5",
                'duration_forward'=>"5",
                'duration_backward_osm'=>"12",
                'duration_backward'=>"12",
                'operator_osm'=>'XXX',
                'operator'=>'XXX',
                'state_osm'=>'ZZZ',
                'state'=>'ZZZ',
                'description_osm'=>'lorem ipsum',
                'description'=>'lorem ipsum',
                'description_it_osm'=>'Prova Prova',
                'description_it'=>'Prova Prova',
                'website_osm'=>'www.openstreetmap.com',
                'website'=>'www.openstreetmap.com',
                'wikimedia_commons_osm'=>'NNN',
                'wikimedia_commons'=>'NNN',
                'maintenance_osm'=>'AAAA',
                'maintenance'=>'AAAA',
                'maintenance_it_osm'=>'AAAA',
                'maintenance_it'=>'AAAA',
                'note_osm'=>'AAAA',
                'note'=>'AAAA',
                'note_it_osm'=>'AAAA',
                'note_it'=>'AAAA',
                'note_project_page_osm'=>'test',
                'note_project_page'=>'test',
                "relation_id" => 9744403
            ];
        }
    }
}
