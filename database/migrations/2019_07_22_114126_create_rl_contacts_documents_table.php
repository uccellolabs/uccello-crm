<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Uccello\Core\Database\Migrations\Migration;

class CreateRlContactsDocumentsTable extends Migration
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
        Schema::create('rl_contacts_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contact_id');
            $table->unsignedInteger('document_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('contact_id')
                    ->references('id')->on($this->tablePrefix.'contacts')
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
        Schema::dropIfExists('rl_contacts_documents');
    }
}
