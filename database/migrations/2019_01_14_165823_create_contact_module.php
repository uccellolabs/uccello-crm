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

class CreateContactModule extends Migration
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
        Schema::dropIfExists($this->tablePrefix . 'contacts');

        // Delete module
        Module::where('name', 'contact')->forceDelete();
    }

    protected function initTablePrefix()
    {
        $this->tablePrefix = 'crm_';

        return $this->tablePrefix;
    }

    protected function createTable()
    {
        Schema::create($this->tablePrefix . 'contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('civility')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->unsignedInteger('account_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('function')->nullable();
            $table->string('service')->nullable();
            $table->unsignedInteger('address_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->text('description')->nullable();
            $table->uuid('assigned_user_id')->nullable();
            $table->unsignedInteger('domain_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('domain_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'domains');
            $table->foreign('account_id')->references('id')->on($this->tablePrefix . 'accounts');
            $table->foreign('address_id')->references('id')->on($this->tablePrefix . 'addresses');
            // $table->foreign('assigned_user_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'entities');
        });
    }

    protected function createModule()
    {
        $module = new Module([
            'name' => 'contact',
            'icon' => 'person',
            'model_class' => 'Uccello\Crm\Models\Contact',
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

        // Field civility
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'civility',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [
                // "rules" => "required",
                "choices" => [
                    "civility.mr",
                    "civility.ms",
                    "civility.mrs",
                    "civility.dr",
                    "civility.prof",
                    "civility.mstr",
                ]
            ]
        ]);

        // Field first_name
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'first_name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field last_name
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'last_name',
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

        // Field function
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'function',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field service
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'service',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field phone
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'phone',
            'uitype_id' => uitype('phone')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field mobile
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'mobile',
            'uitype_id' => uitype('phone')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field email
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'email',
            'uitype_id' => uitype('email')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field address
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'address',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [ 'module' => 'address' ]
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

        // Field description
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'description',
            'uitype_id' => uitype('textarea')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),0,
            'data' => json_decode('{"large":false}')
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
            'columns' => [ 'civility', 'first_name', 'last_name', 'account', 'function', 'phone', 'mobile', 'email', 'address', 'assigned_user' ],
            'conditions' => null,
            'order' => null,
            'is_default' => true,
            'is_public' => false
        ]);
        $filter->save();

        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.related-list',
            'type' => 'related-list',
            'columns' => [ 'first_name', 'last_name', 'function', 'phone', 'mobile' ],
            'conditions' => null,
            'order' => null,
            'is_default' => true,
            'is_public' => false
        ]);
        $filter->save();
    }

    protected function createRelatedLists($module)
    {
        $accountModule = Module::where('name', 'account')->first();
        Relatedlist::create([
            'module_id' => $accountModule->id,
            'related_module_id' => $module->id,
            'related_field_id' => $module->fields()->where('name', 'account')->first()->id,
            'tab_id' => null,
            'label' => 'relatedlist.contacts',
            'type' => 'n-1',
            'method' => 'getDependentList',
            'sequence' => 0,
            'data' => [ 'actions' => [ 'add' ], 'add_tab' => false ]
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