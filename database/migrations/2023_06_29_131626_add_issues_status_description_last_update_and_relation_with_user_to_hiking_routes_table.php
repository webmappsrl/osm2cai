<?php

use App\Enums\IssueStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIssuesStatusDescriptionLastUpdateAndRelationWithUserToHikingRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hiking_routes', function (Blueprint $table) {
            $table->string('issues_status')->default(IssueStatus::Unknown);
            $table->text('issues_description')->nullable();
            $table->date('issues_last_update')->nullable();
            $table->unsignedBigInteger('issues_user_id')->nullable();
            $table->foreign('issues_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hiking_routes', function (Blueprint $table) {
            $table->dropColumn('issues_status');
            $table->dropColumn('issues_description');
            $table->dropColumn('issues_last_update');
            $table->dropForeign(['issues_user_id']);
            $table->dropColumn('issues_user_id');
        });
    }
}
