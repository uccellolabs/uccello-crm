<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Uccello\Core\Database\Migrations\Migration;
use Uccello\Core\Models\Module;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Tab;
use Uccello\Core\Models\Block;
use Uccello\Core\Models\Field;
use Uccello\Core\Models\Filter;
use Uccello\Core\Models\RelatedList;
use Uccello\Core\Models\Link;

class CreateBusinessProviderModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createTable();
        $module = $this->createModule();
        $this->activateModuleOnDomains($module);
        $this->createTabsBlocksFields($module);
        $this->createFilters($module);
        $this->createRelatedLists($module);
        $this->createLinks($module);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop table
        Schema::dropIfExists($this->tablePrefix . 'business_providers');

        // Delete module
        Module::where('name', 'business-provider')->forceDelete();
    }

    protected function initTablePrefix()
    {
        $this->tablePrefix = 'crm_';

        return $this->tablePrefix;
    }

    protected function createTable()
    {
        Schema::create($this->tablePrefix . 'business_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('domain_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('domain_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'domains');
        });
    }

    protected function createModule()
    {
        $module = new Module([
            'name' => 'business-provider',
            'icon' => 'supervisor_account',
            'model_class' => 'Uccello\Crm\Models\BusinessProvider',
            'data' => [ 'package' => 'uccello/crm' ]
        ]);
        $module->save();
        return $module;
    }

    protected function activateModuleOnDomains($module)
    {
        $domains = Domain::all();
        foreach ($domains as $domain) {
            $domain->modules()->attach($module);
        }
    }

    protected function createTabsBlocksFields($module)
    {
        // Tab tab.main
        $tab = new Tab([
            'module_id' => $module->id,
            'label' => 'tab.main',
            'icon' => null,
            'sequence' => 0,
            'data' => null
        ]);
        $tab->save();

        // Block block.general
        $block = new Block([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.general',
            'icon' => 'info',
            'sequence' => 0,
            'data' => null
        ]);
        $block->save();

        // Field first_name
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'first_name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field last_name
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'last_name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field phone
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'phone',
            'uitype_id' => uitype('phone')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 2,
            'data' => null
        ]);
        $field->save();

        // Field email
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'email',
            'uitype_id' => uitype('email')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 3,
            'data' => null
        ]);
        $field->save();

        // Field description
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'description',
            'uitype_id' => uitype('textarea')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 4,
            'data' => null
        ]);
        $field->save();

    }

    protected function createFilters($module)
    {
        // Filter
        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.all',
            'type' => 'list',
            'columns' => [ 'first_name', 'last_name', 'phone', 'email', 'description' ],
            'conditions' => null,
            'order' => null,
            'is_default' => true,
            'is_public' => false,
            'data' => [ 'readonly' => true ]
        ]);
        $filter->save();

    }

    protected function createRelatedLists($module)
    {
    }

    protected function createLinks($module)
    {
    }
}