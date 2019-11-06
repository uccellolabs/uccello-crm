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
            $table->string('last_name');
            $table->unsignedInteger('account_id');
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('function')->nullable();
            $table->string('service')->nullable();
            $table->string('assigned_user_id')->nullable();
            $table->string('lane')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->text('description')->nullable();
            $table->string('vtiger_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
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

        // Field civility
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'civility',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
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
        $field->save();

        // Field first_name
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'first_name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => null
        ]);
        $field->save();

        // Field last_name
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'last_name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 2,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field account
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'account',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 3,
            'data' => json_decode('{"rules":"required","module":"account"}')
        ]);
        $field->save();

        // Field function
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'function',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 4,
            'data' => null
        ]);
        $field->save();

        // Field service
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'service',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 5,
            'data' => null
        ]);
        $field->save();

        // Field phone
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'phone',
            'uitype_id' => uitype('phone')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 6,
            'data' => null
        ]);
        $field->save();

        // Field mobile
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'mobile',
            'uitype_id' => uitype('phone')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 7,
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
            'sequence' => 8,
            'data' => null
        ]);
        $field->save();

        // Field assigned_user
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'assigned_user',
            'uitype_id' => uitype('assigned_user')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 9,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field description
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'description',
            'uitype_id' => uitype('textarea')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 10,
            'data' => json_decode('{"large":false}')
        ]);
        $field->save();

        // Block block.address
        $block = new Block([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.address',
            'icon' => 'location_on',
            'sequence' => 1,
            'data' => null
        ]);
        $block->save();

        // Field lane
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'lane',
            'uitype_id' => uitype('textarea')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
            'data' => null
        ]);
        $field->save();

        // Field postal_code
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'postal_code',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => null
        ]);
        $field->save();

        // Field city
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'city',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 2,
            'data' => null
        ]);
        $field->save();

        // Field country
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'country',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 3,
            'data' => [ 'module' => 'country' ]
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
            'columns' => [ 'civility', 'first_name', 'last_name', 'account', 'function', 'phone', 'mobile', 'email', 'assigned_user' ],
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