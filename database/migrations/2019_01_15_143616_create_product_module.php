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

class CreateProductModule extends Migration
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
        Schema::dropIfExists($this->tablePrefix . 'products');

        // Delete module
        Module::where('name', 'product')->forceDelete();
    }

    protected function initTablePrefix()
    {
        $this->tablePrefix = 'crm_';

        return $this->tablePrefix;
    }

    protected function createTable()
    {
        Schema::create($this->tablePrefix . 'products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('serial_number')->nullable();
            $table->unsignedInteger('family_id')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('vendor_id')->nullable();
            $table->string('vendor_reference')->nullable();
            $table->decimal('selling_price', 13, 2)->nullable();
            $table->decimal('purchase_price', 13, 2)->nullable();
            $table->decimal('margin', 13, 2)->nullable();
            $table->decimal('delivery_costs', 13, 2)->nullable();
            $table->decimal('seller_commission', 13, 2)->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->string('unit')->nullable();
            $table->unsignedInteger('domain_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('domain_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'domains');
        });
    }

    protected function createModule()
    {
        $module = new Module([
            'name' => 'product',
            'icon' => 'list_alt',
            'model_class' => 'Uccello\Crm\Models\Product',
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

        // Field brand
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'brand',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field name
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field serial_number
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'serial_number',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 2,
            'data' => null
        ]);
        $field->save();

        // Field family
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'family',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 3,
            'data' => json_decode('{"module":"product-family"}')
        ]);
        $field->save();

        // Field parent
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'parent',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 4,
            'data' => json_decode('{"module":"product","field":"name"}')
        ]);
        $field->save();

        // Field vendor
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'vendor',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 5,
            'data' => json_decode('{"module":"vendor"}')
        ]);
        $field->save();

        // Field vendor_reference
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'vendor_reference',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 6,
            'data' => null
        ]);
        $field->save();

        // Block block.business
        $block = new Block([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.business',
            'icon' => 'attach_money',
            'sequence' => 1,
            'data' => null
        ]);
        $block->save();

        // Field selling_price
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'selling_price',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
        ]);
        $field->save();

        // Field purchase_price
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'purchase_price',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
        ]);
        $field->save();

        // Field margin
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'margin',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('detail')->id,
            'sequence' => 2,
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
        ]);
        $field->save();

        // Field delivery_costs
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'delivery_costs',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 3,
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
        ]);
        $field->save();

        // Field seller_commission
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'seller_commission',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 4,
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
        ]);
        $field->save();

        // Block block.stock
        $block = new Block([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.stock',
            'icon' => 'all_inbox',
            'sequence' => 2,
            'data' => null
        ]);
        $block->save();

        // Field stock_quantity
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'stock_quantity',
            'uitype_id' => uitype('integer')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
            'data' => json_decode('{"min":0,"step":1,"precision":0}')
        ]);
        $field->save();

        // Field unit
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'unit',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => json_decode('{"choices":["option1","option2"]}')
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
            'columns' => ['name', 'brand', 'family', 'vendor', 'stock_quantity', 'selling_price', 'margin'],
            'conditions' => null,
            'order' => null,
            'is_default' => true,
            'is_public' => false
        ]);
        $filter->save();

    }

    protected function createRelatedLists($module)
    {
        $documentModule = Module::where('name', 'document')->first();

        Relatedlist::create([
            'module_id' => $module->id,
            'related_module_id' => $documentModule->id,
            'tab_id' => null,
            'label' => 'relatedlist.documents',
            'type' => 'n-n',
            'method' => 'getRelatedList',
            'sequence' => 0,
            'data' => [ 'actions' => [ 'add', 'select' ] ]
        ]);
    }

    protected function createLinks($module)
    {
    }
}