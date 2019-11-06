<?php

use Illuminate\Database\Schema\Blueprint;
use Uccello\Core\Database\Migrations\Migration;
use Uccello\Core\Models\Field;
use Uccello\Core\Models\Module;

class AddAccountField extends Migration
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
            $table->string('account_name')->nullable()->after('description');
        });

        // Account
        $module = Module::where('name', 'opportunity')->first();
        $block = $module->blocks()->where('label', 'block.general')->first();

        // Field account_name
        $field = Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'account_name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('hidden')->id,
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
        Module::where('name', 'opportunity')->first()->fields()->whereIn('name', [ 'account' ])->delete();

        Schema::table($this->tablePrefix.'opportunities', function (Blueprint $table) {
            $table->dropColumn('account_name');
        });
    }
}
