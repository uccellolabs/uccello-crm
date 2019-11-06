<?php

use Uccello\Core\Database\Migrations\Migration;
use Uccello\Core\Models\Widget;
use Uccello\Core\Models\Module;

class AddOpportunityWidgets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $module = Module::where('name', 'opportunity')->first();

        // Main fields
        $fieldsWidget = Widget::where('label', 'widget.main_fields')->first();
        $module->widgets()->attach($fieldsWidget->id, ['sequence' => 0, 'data' => json_encode(['fields' => ['name', 'account', 'type', 'origin', 'phase', 'product', 'amount', 'contract_end_date', 'closing_date', 'description', 'assigned_user']])]);

        // Status
            // $statusWidget = Widget::where('label', 'widget.status')->first();
            // $module->widgets()->attach($statusWidget->id, ['sequence' => 1, 'data' => json_encode(['module' => 'opportunity', 'field' => 'step', 'route' => 'opportunity.step.update', 'label' => 'opportunity.widget.opportunity_step'])]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $module = Module::where('name', 'opportunity')->first();

        // Main fields
        $fieldsWidget = Widget::where('label', 'widget.main_fields')->first();
        $module->widgets()->detach($fieldsWidget->id);

        // Status
        $statusWidget = Widget::where('label', 'widget.status')->first();
        $module->widgets()->detach($statusWidget->id);
    }
}