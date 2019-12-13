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

class CreateOpportunityModule extends Migration
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
        Schema::dropIfExists($this->tablePrefix . 'opportunities');

        // Delete module
        Module::where('name', 'opportunity')->forceDelete();
    }

    protected function initTablePrefix()
    {
        $this->tablePrefix = 'crm_';

        return $this->tablePrefix;
    }

    protected function createTable()
    {
        Schema::create($this->tablePrefix . 'opportunities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('account_id')->nullable();
            $table->string('type')->nullable();
            $table->string('type_other')->nullable();
            $table->string('origin')->nullable();
            $table->unsignedInteger('business_provider_id')->nullable();
            $table->string('phase')->nullable();
            $table->string('step')->default('step.new')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->string('assigned_user_id')->nullable();
            $table->decimal('amount', 13, 2)->nullable();
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
            'name' => 'opportunity',
            'icon' => 'attach_money',
            'model_class' => 'Uccello\Crm\Models\Opportunity',
            'data' => [ 'menu' => 'crm.opportunity.kanban', 'package' => 'uccello/crm' ]
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

        // Field name
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
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
            'sequence' => 1,
            'data' => json_decode('{"rules":"required","module":"account"}')
        ]);
        $field->save();

        // Field amount
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'amount',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 2,
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
        ]);
        $field->save();

        // Field assigned_user
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'assigned_user',
            'uitype_id' => uitype('assigned_user')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 3,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field contract_end_date
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'contract_end_date',
            'uitype_id' => uitype('date')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 4,
            'data' => null
        ]);
        $field->save();

        // Field closing_date
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'closing_date',
            'uitype_id' => uitype('date')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 5,
            'data' => null
        ]);
        $field->save();

        // Field origin
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'origin',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 6,
            'data' => [
                "rules" => "required",
                "choices" => [
                    "Kompass",
                    "BNI",
                    "Pro Contact",
                    "Réseau Professionnel",
                    "Relation",
                    "Apporteur affaire",
                    "Parc existant",
                ]
            ]
        ]);
        $field->save();

        // Field business_provider
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'business_provider',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 7,
            'data' => [ 'module' => 'business-provider']
        ]);
        $field->save();

        // Field type
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'type',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 8,
            'data' => [
                "rules" => "required",
                "choices" => [
                    "Bureautique",
                    "Informatique",
                    "Écrans",
                    "Téléphonie",
                    "Autre",
                ]
            ]
        ]);
        $field->save();

        // Field type_other
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'type_other',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 9,
            'data' => null
        ]);
        $field->save();

        // Field phase
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'phase',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 10,
            'data' => json_decode('{"rules":"required", "choices":["phase.1.outlook","phase.2.oppo_hight","phase.3.oppo_low","phase.4.project","phase.5.won", "phase.6.lost"]}')
        ]);
        $field->save();

        // Field step
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'step',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('hidden')->id,
            'sequence' => 11,
            'data' => json_decode('{"rules":"required", "choices":["step.qualification", "step.study", "step.proposal", "step.negociation", "step.won", "step.lost"], "default":"step.qualification"}')
        ]);
        $field->save();

        // Field description
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'description',
            'uitype_id' => uitype('textarea')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 12,
            'data' => json_decode('{"large":false}')
        ]);
        $field->save();

        // Field created_at
        $field = Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'created_at',
            'uitype_id' => uitype('date')->id,
            'displaytype_id' => displaytype('detail')->id,
            'sequence' => 13,
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
            'columns' => [ 'name', 'account', 'amount', 'phase', 'contract_end_date', 'closing_date', 'assigned_user', 'origin' ],
            'conditions' => null,
            'order' => [ 'phase' => 'asc' ],
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
            'columns' => [ 'name', 'phase', 'amount', 'closing_date' ],
            'conditions' => null,
            'order' => [ 'phase' => 'asc' ],
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
            'related_field_id' => Field::where('module_id', $module->id)->where('name', 'account')->first()->id,
            'label' => 'relatedlist.opportunities',
            'type' => 'n-1',
            'method' => 'getDependentList',
            'sequence' => 1,
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