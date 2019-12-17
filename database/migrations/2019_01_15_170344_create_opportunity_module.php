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
            $table->string('account_name')->nullable();
            $table->string('type')->nullable();
            $table->string('type_other')->nullable();
            $table->string('origin')->nullable();
            $table->unsignedInteger('business_provider_id')->nullable();
            $table->string('phase')->nullable();
            $table->string('step')->default('step.new')->nullable();
            $table->date('closing_date')->nullable();
            $table->decimal('amount', 13, 2)->nullable();
            $table->text('description')->nullable();
            $table->uuid('assigned_user_id')->nullable();
            $table->unsignedInteger('domain_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('domain_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'domains');
            $table->foreign('account_id')->references('id')->on($this->tablePrefix.'accounts');
            $table->foreign('business_provider_id')->references('id')->on($this->tablePrefix.'accounts');
            // $table->foreign('assigned_user_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'entities');
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

        // Field account_name
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'account_name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('hidden')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field amount
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'amount',
            'uitype_id' => uitype('number')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"min":0,"step":0.01,"precision":2}')
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

        // Field phase
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'phase',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [
                'choices' => [
                    'phase.1_new',
                    'phase.2_opportunity_hight',
                    'phase.3_opportunity_low',
                    'phase.4_project',
                    'phase.5_won',
                    'phase.6_lost',
                ],
                'default' => 'phase.1_new'
            ]
        ]);

        // Field closing_date
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'closing_date',
            'uitype_id' => uitype('date')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field origin
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'origin',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [
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

        // Field business_provider
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'business_provider',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [ 'module' => 'account']
        ]);

        // Field type
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'type',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [
                "choices" => [
                    "Bureautique",
                    "Informatique",
                    "Écrans",
                    "Téléphonie",
                    "Autre",
                ]
            ]
        ]);

        // Field type_other
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'type_other',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field step
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'step',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('hidden')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"choices":["step.qualification", "step.study", "step.proposal", "step.negociation", "step.won", "step.lost"], "default":"step.qualification"}')
        ]);

        // Field description
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'description',
            'uitype_id' => uitype('textarea')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => json_decode('{"large":true}')
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
            'columns' => [ 'name', 'account', 'amount', 'phase', 'closing_date', 'assigned_user', 'origin' ],
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