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
use Uccello\Core\Models\Relatedlist;
use Uccello\Core\Models\Link;

class CreateProductFamilyModule extends Migration
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
        Schema::dropIfExists($this->tablePrefix . 'product_families');

        // Delete module
        Module::where('name', 'product-family')->forceDelete();
    }

    protected function initTablePrefix()
    {
        $this->tablePrefix = 'crm_';

        return $this->tablePrefix;
    }

    protected function createTable()
    {
        Schema::create($this->tablePrefix . 'product_families', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('color')->nullable();
            $table->string('path')->nullable();
            $table->integer('level')->default(0);
            $table->uuid('assigned_user_id')->nullable();
            $table->unsignedInteger('domain_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('domain_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'domains');
            $table->foreign('parent_id')->references('id')->on($this->tablePrefix . 'product_families');
            // $table->foreign('assigned_user_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'entities');
        });
    }

    protected function createModule()
    {
        $module = new Module([
            'name' => 'product-family',
            'icon' => 'folder',
            'model_class' => 'Uccello\Crm\Models\ProductFamily',
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
        $tab = Tab::create([
            'module_id' => $module->id,
            'label' => 'tab.main',
            'icon' => null,
            'sequence' => $module->tabs()->count(),
            'data' => null
        ]);

        // Block block.general
        $block = Block::create([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.general',
            'icon' => 'info',
            'sequence' => $tab->blocks()->count(),
            'data' => null
        ]);

        // Field name
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"rules":"required"}')
        ]);

        // Field parent
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'parent',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"module":"product-family"}')
        ]);

        // Field color
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'color',
            'uitype_id' => uitype('color')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field assigned_user
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'assigned_user',
            'uitype_id' => uitype('assigned_user')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"rules":"required"}')
        ]);

        // Field created_at
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'created_at',
            'uitype_id' => uitype('date')->id,
            'displaytype_id' => displaytype('detail')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field updated_at
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'updated_at',
            'uitype_id' => uitype('date')->id,
            'displaytype_id' => displaytype('detail')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);
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
            'columns' => ['name', 'parent', 'color'],
            'conditions' => null,
            'order' => null,
            'is_default' => true,
            'is_public' => false
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