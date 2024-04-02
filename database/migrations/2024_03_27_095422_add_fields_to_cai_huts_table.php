<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCaiHutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cai_huts', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->string('type_custodial')->nullable();
            $table->string('company_management_property')->nullable();
            $table->string('addr_street')->nullable();
            $table->string('addr_housenumber')->nullable();
            $table->string('addr_postcode')->nullable();
            $table->string('addr_city')->nullable();
            $table->string('ref_vatin')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('email_pec')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook_contact')->nullable();
            $table->string('municipality_geo')->nullable();
            $table->string('province_geo')->nullable();
            $table->string('site_geo')->nullable();
            $table->string('opening')->nullable();
            $table->string('acqua_in_rifugio_serviced')->nullable();
            $table->string('acqua_calda_service')->nullable();
            $table->string('acqua_esterno_service')->nullable();
            $table->string('posti_letto_invernali_service')->nullable();
            $table->string('posti_totali_service')->nullable();
            $table->string('ristorante_service')->nullable();
            $table->string('activities')->nullable();
            $table->string('necessary_equipment')->nullable();
            $table->string('rates')->nullable();
            $table->string('payment_credit_cards')->nullable();
            $table->string('accessibilitá_ai_disabili_service')->nullable();
            $table->string('gallery')->nullable();
            $table->string('rule')->nullable();
            $table->string('map')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cai_huts', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('type_custodial');
            $table->dropColumn('company_management_property');
            $table->dropColumn('addr_street');
            $table->dropColumn('addr_housenumber');
            $table->dropColumn('addr_postcode');
            $table->dropColumn('addr_city');
            $table->dropColumn('ref_vatin');
            $table->dropColumn('phone');
            $table->dropColumn('fax');
            $table->dropColumn('email');
            $table->dropColumn('email_pec');
            $table->dropColumn('website');
            $table->dropColumn('facebook_contact');
            $table->dropColumn('municipality_geo');
            $table->dropColumn('province_geo');
            $table->dropColumn('site_geo');
            $table->dropColumn('opening');
            $table->dropColumn('acqua_in_rifugio_serviced');
            $table->dropColumn('acqua_calda_service');
            $table->dropColumn('acqua_esterno_service');
            $table->dropColumn('posti_letto_invernali_service');
            $table->dropColumn('posti_totali_service');
            $table->dropColumn('ristorante_service');
            $table->dropColumn('activities');
            $table->dropColumn('necessary_equipment');
            $table->dropColumn('rates');
            $table->dropColumn('payment_credit_cards');
            $table->dropColumn('accessibilitá_ai_disabili_service');
            $table->dropColumn('gallery');
            $table->dropColumn('rule');
            $table->dropColumn('map');
        });
    }
}