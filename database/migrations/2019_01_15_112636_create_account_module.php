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

class CreateAccountModule extends Migration
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
        Schema::dropIfExists($this->tablePrefix . 'accounts');

        // Delete module
        Module::where('name', 'account')->forceDelete();
    }

    protected function initTablePrefix()
    {
        $this->tablePrefix = 'crm_';

        return $this->tablePrefix;
    }

    protected function createTable()
    {
        Schema::create($this->tablePrefix . 'accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('sign')->nullable();
            $table->string('type');
            $table->string('lead_status')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            $table->string('ape_code')->nullable();
            $table->string('siret')->nullable();
            $table->string('origin')->nullable();
            $table->string('origin_other')->nullable();
            $table->string('business_sector')->nullable();
            $table->string('classify')->nullable();
            $table->string('billing_lane')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_city')->nullable();
            $table->unsignedInteger('billing_country_id')->nullable();
            $table->string('shipping_lane')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('shipping_city')->nullable();
            $table->unsignedInteger('shipping_country_id')->nullable();
            $table->string('assigned_user_id')->nullable();
            $table->text('employees_address')->nullable();
            $table->text('employees_france')->nullable();
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
            'name' => 'account',
            'icon' => 'business',
            'model_class' => 'Uccello\Crm\Models\Account',
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

        // Field name
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
            'data' => [ "rules" => "required" ]
        ]);
        $field->save();

        // Field sign
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'sign',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => null
        ]);
        $field->save();

        // Field type
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'type',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 2,
            'data' => [
                "rules" => "required",
                "choices" => [
                    "type.customer",
                    "type.prospect",
                    "type.lead",
                    "type.old_customer",
                    "type.vendor"
                ],
                // "default" => "type.customer"
            ]
        ]);
        $field->save();

        // Field lead_status
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'lead_status',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('hidden')->id,
            'sequence' => 3,
            'data' => [
                "rules" => "required",
                "choices" => [
                    "status.new",
                    "status.contacted",
                    "status.discovery",
                    "status.qualified",
                    "status.disqualified",
                ]
            ]
        ]);
        $field->save();

        // Field assigned_user
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'assigned_user',
            'uitype_id' => uitype('assigned_user')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 4,
            'data' => json_decode('{"rules":"required"}')
        ]);
        $field->save();

        // Field business_sector
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'business_sector',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 5,
            'data' => [
                "rules" => "required",
                "choices" => [
                    "Adm. publique",
                    "Commerce",
                    "Conseil",
                    "Construction",
                    "Enseignement",
                    "Hôtellerie et Restauration",
                    "Industrie",
                    "Ingénierie",
                    "Santé",
                    "Services",
                    "Transport",
                    "Autre"
                ]
            ]
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

        // Field origin
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'origin',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 7,
            'data' => [
                "rules" => "required",
                "choices" => [
                    "Kompass",
                    "BNI",
                    "Pro Contact",
                    "Réseau Professionnel",
                    "Relation",
                    "Autre"
                ]
            ]
        ]);
        $field->save();

        // Field origin_other
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'origin_other',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 8,
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
            'sequence' => 9,
            'data' => null
        ]);
        $field->save();

        // Field website
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'website',
            'uitype_id' => uitype('url')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 10,
            'data' => null
        ]);
        $field->save();

        // Field ape_code
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'ape_code',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 11,
            'data' => null
        ]);
        $field->save();

        // Field siret
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'siret',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 12,
            'data' => null
        ]);
        $field->save();

        // Field employees_address
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'employees_address',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 13,
            'data' => [
                'choices' => [
                    'employees.2_or_less',
                    'employees.3_5',
                    'employees.6_9',
                    'employees.10_19',
                    'employees.20_49',
                    'employees.50_99',
                    'employees.100_or_more'
                ]
            ]
        ]);
        $field->save();

        // Field employees_france
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'employees_france',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 14,
            'data' => [
                'choices' => [
                    'employees.2_or_less',
                    'employees.3_5',
                    'employees.6_9',
                    'employees.10_19',
                    'employees.20_49',
                    'employees.50_99',
                    'employees.100_or_more'
                ]
            ]
        ]);
        $field->save();

        // Field fax
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'fax',
            'uitype_id' => uitype('phone')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 15,
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
            'sequence' => 16,
            'data' => json_decode('{"large":false}')
        ]);
        $field->save();

        // Field classify
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'classify',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 17,
            'data' => json_decode('{"choices":["Compte non exploitable","Pas intéressé / Pas besoin","Refus de communiquer"]}')
        ]);
        $field->save();

        // Block block.billing_address
        $block = new Block([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.billing_address',
            'icon' => 'location_on',
            'sequence' => 1,
            'data' => null
        ]);
        $block->save();

        // Field billing_lane
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'billing_lane',
            'uitype_id' => uitype('textarea')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
            'data' => null
        ]);
        $field->save();

        // Field billing_postal_code
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'billing_postal_code',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => null
        ]);
        $field->save();

        // Field billing_city
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'billing_city',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 2,
            'data' => null
        ]);
        $field->save();

        // Field billing_country
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'billing_country',
            'uitype_id' => uitype('entity')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 3,
            'data' => [ 'module' => 'country' ]
        ]);
        $field->save();

        // Block block.shipping_address
        $block = new Block([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.shipping_address',
            'icon' => 'location_on',
            'sequence' => 2,
            'data' => null
        ]);
        $block->save();

        // Field shipping_lane
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'shipping_lane',
            'uitype_id' => uitype('textarea')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 0,
            'data' => null
        ]);
        $field->save();

        // Field shipping_postal_code
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'shipping_postal_code',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 1,
            'data' => null
        ]);
        $field->save();

        // Field shipping_city
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'shipping_city',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => 2,
            'data' => null
        ]);
        $field->save();

        // Field shipping_country
        $field = new Field([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'shipping_country',
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
            'columns' => [ 'name', 'type', 'assigned_user', 'origin', 'billing_lane', 'billing_postal_code', 'billing_city' ],
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
            'name' => 'filter.customers',
            'type' => 'list',
            'columns' => [ 'name', 'type', 'assigned_user', 'origin', 'billing_lane', 'billing_postal_code', 'billing_city' ],
            'conditions' => json_decode('{"search":{"type":"type.customer"}}'),
            'order' => null,
            'is_default' => true,
            'is_public' => false
        ]);
        $filter->save();

        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.leads',
            'type' => 'list',
            'columns' => [ 'name', 'type', 'assigned_user', 'origin', 'billing_lane', 'billing_postal_code', 'billing_city' ],
            'conditions' => json_decode('{"search":{"type":"type.lead"}}'),
            'order' => null,
            'is_default' => true,
            'is_public' => false
        ]);
        $filter->save();

        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.prospects',
            'type' => 'list',
            'columns' => [ 'name', 'type', 'assigned_user', 'origin', 'billing_lane', 'billing_postal_code', 'billing_city' ],
            'conditions' => json_decode('{"search":{"type":"type.prospect"}}'),
            'order' => null,
            'is_default' => true,
            'is_public' => false
        ]);
        $filter->save();

    }

    protected function createRelatedLists($module)
    {
        $contactModule = Module::where('name', 'contact')->first();
        Relatedlist::create([
            'module_id' => $module->id,
            'related_module_id' => $contactModule->id,
            'related_field_id' => $contactModule->fields()->where('name', 'account')->first()->id,
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
            'sequence' => 3,
            'data' => [ 'actions' => [ 'add', 'select' ] ]
        ]);
    }

    protected function createLinks($module)
    {
    }
}