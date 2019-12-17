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

class CreateAddressModule extends Migration
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
        Schema::dropIfExists($this->tablePrefix . 'addresses');

        // Delete module
        Module::where('name', 'address')->forceDelete();
    }

    protected function createTable()
    {
        Schema::create($this->tablePrefix . 'addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label')->nullable();
            $table->unsignedInteger('account_id')->nullable();
            $table->string('type')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('address_3')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->string('gln_code')->nullable();
            $table->uuid('assigned_user_id')->nullable();
            $table->unsignedInteger('domain_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on($this->tablePrefix . 'accounts');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('domain_id')->references('id')->on('uccello_domains');
            // $table->foreign('assigned_user_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'entities');
        });
    }

    protected function createModule()
    {
        $module = Module::create([
            'name' => 'address',
            'icon' => 'near_me',
            'model_class' => 'Uccello\Crm\Models\Address',
            'data' => [ 'package' => 'uccello/crm' ]
        ]);

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

        // Field label
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'label',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"rules":"required"}')
        ]);

        // Field account
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'account',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"rules":"required","module":"account"}')
        ]);

        // Field type
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'type',
            'uitype_id' => uitype('choice')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [
                "multiple" => true,
                "choices" => [
                    "type.billing",
                    "type.shipping",
                    "type.other"
                ],
            ]
        ]);

        // Field address_1
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'address_1',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field address_2
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'address_2',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field address_3
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'address_3',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field postal_code
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'postal_code',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field city
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'city',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field country
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'country',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"module":"country"}')
        ]);

        // Field gln_code
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'gln_code',
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
    }

    protected function createFilters($module)
    {
        // Filter
        Filter::create([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.all',
            'type' => 'list',
            'columns' => [ 'label', 'account', 'type', 'address_1', 'address_2', 'address_3', 'postal_code', 'city', 'country', 'gln_code' ],
            'conditions' => null,
            'order' => null,
            'is_default' => true,
            'is_public' => false,
            'data' => [ 'readonly' => true ]
        ]);

    }

    protected function createRelatedLists($module)
    {
        $sourceModule = Module::where('name', 'account')->first();

        Relatedlist::create([
            'module_id' => $sourceModule->id,
            'related_module_id' => $module->id,
            'related_field_id' => $module->fields->where('name', 'account')->first()->id,
            'tab_id' => null,
            'label' => 'relatedlist.addresses',
            'type' => 'n-1',
            'method' => 'getDependentList',
            'sequence' => 0,
            'data' => [ 'actions' => [ 'add' ] ]
        ]);
    }

    protected function createLinks($module)
    {
    }
}