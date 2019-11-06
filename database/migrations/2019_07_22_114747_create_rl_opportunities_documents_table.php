<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Uccello\Core\Database\Migrations\Migration;

class CreateRlOpportunitiesDocumentsTable extends Migration
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
        Schema::create('rl_opportunities_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('opportunity_id');
            $table->unsignedInteger('document_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('opportunity_id')
                    ->references('id')->on($this->tablePrefix.'opportunities')
                    ->onDelete('cascade');

            $table->foreign('document_id')
                    ->references('id')->on($this->tablePrefix.'documents')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rl_opportunities_documents');
    }
}
