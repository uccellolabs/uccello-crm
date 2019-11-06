<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Uccello\Core\Database\Migrations\Migration;

class AlterOpportunitiesTable extends Migration
{
    protected function initTablePrefix()
    {
        $this->tablePrefix = 'crm_';

        return $this->tablePrefix;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablePrefix.'opportunities', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on($this->tablePrefix.'accounts');
            $table->foreign('business_provider_id')->references('id')->on($this->tablePrefix.'business_providers');
            $table->foreign('assigned_user_id')->references('id')->on('uccello_entities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablePrefix.'opportunities', function (Blueprint $table) {
            $table->dropForeign('opportunities_account_id_foreign');
            $table->dropForeign('opportunities_business_provider_id_foreign');
            $table->dropForeign('opportunities_assigned_user_id_foreign');
        });
    }
}
