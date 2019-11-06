<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Uccello\Core\Database\Migrations\Migration;

class AlterContactsTable extends Migration
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
        Schema::table($this->tablePrefix.'contacts', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on($this->tablePrefix.'accounts');
            $table->foreign('assigned_user_id')->references('id')->on('uccello_entities');
            // $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablePrefix.'contacts', function (Blueprint $table) {
            $table->dropForeign('contacts_account_id_foreign');
            $table->dropForeign('contacts_assigned_user_id_foreign');
            // $table->dropForeign('contacts_country_id_foreign');
        });
    }
}
