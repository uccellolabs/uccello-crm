<?php

use Illuminate\Database\Migrations\Migration;
use Uccello\Core\Models\Field;
use Uccello\Core\Models\Module;

class AddCreationEditionFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Account
        $module = Module::where('name', 'account')->first();
        $block = $module->blocks()->where('label', 'block.general')->first();

        // Field created_at
        $field = Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'created_at',
            'uitype_id' => uitype('date')->id,
            'displaytype_id' => displaytype('detail')->id,
            'sequence' => 18,
            'data' => null
        ]);
        $field->save();

        // Field updated_at
        $field = Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'updated_at',
            'uitype_id' => uitype('date')->id,
            'displaytype_id' => displaytype('detail')->id,
            'sequence' => 19,
            'data' => null
        ]);
        $field->save();

        /////////////////////////////

        // Opportunity
        $module = Module::where('name', 'opportunity')->first();
        $block = $module->blocks()->where('label', 'block.general')->first();

        // Field updated_at
        $field = Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'updated_at',
            'uitype_id' => uitype('date')->id,
            'displaytype_id' => displaytype('detail')->id,
            'sequence' => 14,
            'data' => null
        ]);
        $field->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Module::where('name', 'account')->first()->fields()->whereIn('name', [ 'created_at', 'updated_at' ])->delete();
        Module::where('name', 'opportunity')->first()->fields()->whereIn('name', [ 'updated_at' ])->delete();
    }
}
