<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Uccello\Core\Models\Module;
use Uccello\Core\Models\Relatedlist;
use Uccello\Core\Models\Widget;

class ChangeAccountWidgets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $module = Module::where('name', 'account')->first();

        $calendarListWidget = Widget::where('label', 'widget.calendar_list')->first();
        // $tasksWidget = Widget::where('label', 'widget.tasks')->first();

        $relatedlistWidget = Widget::where('label', 'widget.relatedlist')->first();

        // $relatedlist = Relatedlist::where('module_id', $module->id)->where('related_module_id', Module::where('name', 'task')->first()->id)->first();
        // $module->widgets()->detach($tasksWidget->id, ['sequence' => 3, 'data' => json_encode(['id' => $relatedlist->id])]);
        // $module->widgets()->attach($relatedlistWidget->id, ['sequence' => 3, 'data' => json_encode(['id' => $relatedlist->id])]);

        $module->widgets()->attach($calendarListWidget->id, ['sequence' => 4, 'data' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $module = Module::where('name', 'account')->first();

        $calendarListWidget = Widget::where('label', 'widget.calendar_list')->first();
        $tasksWidget = Widget::where('label', 'widget.tasks')->first();

        $relatedlistWidget = Widget::where('label', 'widget.relatedlist')->first();

        $relatedlist = Relatedlist::where('module_id', $module->id)->where('related_module_id', Module::where('name', 'task')->first()->id)->first();
        $module->widgets()->where('id', $calendarListWidget->id)->detach();

        $module->widgets()->detach($relatedlistWidget->id, ['sequence' => 3, 'data' => json_encode(['id' => $relatedlist->id])]);
        $module->widgets()->attach($tasksWidget->id, ['sequence' => 3, 'data' => json_encode(['id' => $relatedlist->id])]);

    }
}
