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
            $table->string('reference')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
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
            $table->string('path')->nullable();
            $table->integer('level')->default(0);
            $table->uuid('assigned_user_id')->nullable();
            $table->unsignedInteger('domain_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('domain_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'domains');
            $table->foreign('family_id')->references('id')->on($this->tablePrefix . 'product_families');
            $table->foreign('parent_id')->references('id')->on($this->tablePrefix . 'products');
            $table->foreign('vendor_id')->references('id')->on($this->tablePrefix . 'accounts');
            // $table->foreign('assigned_user_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'entities');
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

        // Field reference
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'reference',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null,
        ]);

        // Field brand
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'brand',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null,
        ]);

        // Field model
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'model',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null,
        ]);

        // Field serial_number
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'serial_number',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field family
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'family',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"module":"product-family"}')
        ]);

        // Field parent
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'parent',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"module":"product"}')
        ]);

        // Field vendor
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'vendor',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"module":"account"}')
        ]);

        // Field vendor_reference
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'vendor_reference',
            'uitype_id' => uitype('text')->id,
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

        // Block block.business
        $block = Block::create([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.business',
            'icon' => 'attach_money',
            'sequence' => $tab->blocks()->count(),
            'data' => null
        ]);

        // Field selling_price
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'selling_price',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
        ]);

        // Field purchase_price
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'purchase_price',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
        ]);

        // Field margin
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'margin',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('detail')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"step":0.01,"precision":2}')
        ]);

        // Field delivery_costs
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'delivery_costs',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
        ]);

        // Field seller_commission
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'seller_commission',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
        ]);

        // Block block.stock
        $block = Block::create([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.stock',
            'icon' => 'all_inbox',
            'sequence' => $tab->blocks()->count(),
            'data' => null
        ]);

        // Field stock_quantity
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'stock_quantity',
            'uitype_id' => uitype('integer')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"min":0,"step":1,"precision":0}')
        ]);

        // Field unit
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'unit',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"choices":["unit.fixed_price","unit.day","unit.unit"]}')
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
        $productFamilyModule = Module::where('name', 'product-family')->first();
        Relatedlist::create([
            'module_id' => $productFamilyModule->id,
            'related_module_id' => $module->id,
            'related_field_id' => $module->fields()->where('name', 'product_family')->first()->id,
            'tab_id' => null,
            'label' => 'relatedlist.products',
            'type' => 'n-1',
            'method' => 'getDependentList',
            'sequence' => 0,
            'data' => [ 'actions' => [ 'add' ] ]
        ]);

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