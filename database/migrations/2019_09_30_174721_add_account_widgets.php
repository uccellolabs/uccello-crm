<?php

use Uccello\Core\Database\Migrations\Migration;
use Uccello\Core\Models\Widget;
use Uccello\Core\Models\Relatedlist;
use Uccello\Core\Models\Module;

class AddAccountWidgets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $module = Module::where('name', 'account')->first();

        // Main fields
        $fieldsWidget = Widget::where('label', 'widget.main_fields')->first();
        $module->widgets()->attach($fieldsWidget->id, ['sequence' => 0, 'data' => json_encode(['fields' => ['name', 'type', 'email', 'phone', 'assigned_user']])]);

        $relatedlistWidget = Widget::where('label', 'widget.relatedlist')->first();

        // Opportunities
        $relatedlist = Relatedlist::where('module_id', $module->id)->where('related_module_id', Module::where('name', 'opportunity')->first()->id)->first();
        $module->widgets()->attach($relatedlistWidget->id, ['sequence' => 1, 'data' => json_encode(['id' => $relatedlist->id])]);

        // Contacts
        $relatedlist = Relatedlist::where('module_id', $module->id)->where('related_module_id', Module::where('name', 'contact')->first()->id)->first();
        $module->widgets()->attach($relatedlistWidget->id, ['sequence' => 2, 'data' => json_encode(['id' => $relatedlist->id])]);

        // Calendar
        // $calendarListWidget = Widget::where('label', 'widget.calendar_list')->first();
        // $module->widgets()->attach($calendarListWidget->id, ['sequence' => 3, 'data' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $module = Module::where('name', 'account')->first();

        // Main fields
        $fieldsWidget = Widget::where('label', 'widget.main_fields')->first();
        $module->widgets()->detach($fieldsWidget->id);

        // Opportunities
        $opportunitiesWidget = Widget::where('label', 'widget.opportunities')->first();
        $module->widgets()->detach($opportunitiesWidget->id);

        // Contacts
        $contactsWidget = Widget::where('label', 'widget.contacts')->first();
        $module->widgets()->detach($contactsWidget->id);
    }
}
