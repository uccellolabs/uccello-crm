<?php

use Illuminate\Support\Facades\Schema;
use Uccello\Core\Database\Migrations\Migration;
use Uccello\Core\Models\Relatedlist;
use Uccello\Core\Models\Widget;

class AddWidgets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Widget::create([
            'label' => 'widget.contacts',
            'type' => 'summary',
            'class' => 'Uccello\Crm\Widgets\Contacts'
        ]);

        Widget::create([
            'label' => 'widget.opportunities',
            'type' => 'summary',
            'class' => 'Uccello\Crm\Widgets\Opportunities'
        ]);

        Widget::create([
            'label' => 'widget.status',
            'type' => 'summary',
            'class' => 'Uccello\Crm\Widgets\Status'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Widget::where('label', 'widget.contacts')
            ->where('type', 'summary')
            ->where('class', 'Uccello\Crm\Widgets\Contacts')
            ->delete();

        Widget::where('label', 'widget.opportunities')
            ->where('type', 'summary')
            ->where('class', 'Uccello\Crm\Widgets\Opportunities')
            ->delete();

        Widget::where('label', 'widget.status')
            ->where('type', 'summary')
            ->where('class', 'Uccello\Crm\Widgets\Status')
            ->delete();
    }
}