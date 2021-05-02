<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHikingRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // REMOVE ALL TABLE tracks and sector_track
        Schema::dropIfExists('sector_track');
        Schema::dropIfExists('tracks');

        // Creates new table Hiking routes
        Schema::create('hiking_routes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Connection with OSM (ID) and validation Workflow
            $table->bigInteger('relation_id');
            $table->integer('osm2cai_status')->default(0);
            $table->date('validation_date')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->foreign('user_id')->
                references('id')->on('users');

            // IDENTIFICATION
            $table->string('ref_osm')->nullable();
            $table->string('ref')->nullable();
            $table->string('old_ref_osm')->nullable();
            $table->string('old_ref')->nullable();
            $table->string('source_osm')->nullable();
            $table->string('source')->nullable();
            $table->string('source_ref_osm')->nullable();
            $table->string('source_ref')->nullable();
            $table->string('survey_date_osm')->nullable();
            $table->string('survey_date')->nullable();
            $table->string('name_osm')->nullable();
            $table->string('name')->nullable();
            $table->string('rwn_name_osm')->nullable();
            $table->string('rw_name')->nullable();
            $table->string('ref_REI_osm')->nullable();
            $table->string('ref_REI')->nullable();

            // TAGS
            $table->json('tags_osm')->nullable();
            $table->json('tags')->nullable();

            // GEOMETRY
            $table->multiLineString('geometry_osm')->nullable();
            $table->multiLineString('geometry')->nullable();
            $table->point('geometry_start_point')->nullable();

            // MAIN INFO
            $table->string('cai_scale_osm')->nullable();
            $table->string('cai_scale')->nullable();
            $table->string('from_osm')->nullable();
            $table->string('from')->nullable();
            $table->string('to_osm')->nullable();
            $table->string('to')->nullable();
            $table->string('osmc_symbol_osm')->nullable();
            $table->string('osmc_symbol')->nullable();
            $table->string('network_osm')->nullable();
            $table->string('network')->nullable();
            $table->string('roundtrip_osm')->nullable();
            $table->string('roundtrip')->nullable();
            $table->string('symbol_osm')->nullable();
            $table->string('symbol')->nullable();
            $table->string('symbol_it_osm')->nullable();
            $table->string('symbol_it')->nullable();

            // TECH INFO
            $table->string('ascent_osm')->nullable();
            $table->string('ascent')->nullable();
            $table->string('descent_osm')->nullable();
            $table->string('descent')->nullable();
            $table->string('distance')->nullable();
            $table->string('distance_osm')->nullable();
            $table->string('duration_forward')->nullable();
            $table->string('duration_forward_osm')->nullable();
            $table->string('duration_backward')->nullable();
            $table->string('duration_backward_osm')->nullable();


            // OTHER INFO
            $table->string('operator_osm')->nullable();
            $table->string('operator')->nullable();
            $table->string('state_osm')->nullable();
            $table->string('state')->nullable();
            $table->string('description_osm')->nullable();
            $table->string('description')->nullable();
            $table->string('description_it_osm')->nullable();
            $table->string('description_it')->nullable();
            $table->string('website_osm')->nullable();
            $table->string('website')->nullable();
            $table->string('wikimedia_commons_osm')->nullable();
            $table->string('wikimedia_commons')->nullable();
            $table->string('maintenance_osm')->nullable();
            $table->string('maintenance')->nullable();
            $table->string('maintenance_it_osm')->nullable();
            $table->string('maintenance_it')->nullable();
            $table->string('note_osm')->nullable();
            $table->string('note')->nullable();
            $table->string('note_it_osm')->nullable();
            $table->string('note_it')->nullable();
            $table->string('note_project_page_osm')->nullable();
            $table->string('note_project_page')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hiking_routes');
    }
}
