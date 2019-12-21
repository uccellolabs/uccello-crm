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
            $table->string('title')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('type');
            $table->string('category')->nullable();
            $table->string('lead_status')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('website')->nullable();
            $table->string('origin')->nullable();
            $table->string('business_sector')->nullable();
            $table->string('naf_code')->nullable();
            $table->string('siret')->nullable();
            $table->string('vat_intra')->nullable();
            $table->string('bic')->nullable();
            $table->string('iban')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('payment_validity')->nullable();
            $table->string('employees')->nullable();
            $table->text('description')->nullable();
            $table->uuid('assigned_user_id')->nullable();
            $table->unsignedInteger('domain_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('domain_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'domains');
            // $table->foreign('assigned_user_id')->references('id')->on(env('UCCELLO_TABLE_PREFIX', 'uccello_').'entities');
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

        // Field title
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'title',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [
                "choices" => [
                    "title.mr",
                    "title.mr_mrs",
                    "title.ms",
                    "title.mrs",
                    "title.dr",
                    "title.prof",
                    "title.mstr",
                    "title.assocation",
                    "title.earl",
                    "title.eurl",
                    "title.sa",
                    "title.sarl",
                    "title.sas",
                    "title.sasu",
                    "title.sci",
                ]
             ]
        ]);

        // Field name
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'name',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [ "rules" => "required" ]
        ]);

        // Field code
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'code',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('detail')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
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
                "rules" => "required",
                "multiple" => true,
                "choices" => [
                    "type.customer",
                    "type.prospect",
                    "type.lead",
                    "type.old_customer",
                    "type.vendor",
                    "type.sub_contractor",
                    "type.business_provider",
                ],
                "default" => "type.customer",
            ]
        ]);

        // Field lead_status
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'lead_status',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('hidden')->id,
            'sequence' => $block->fields()->count(),
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

        // Field category
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'category',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [
                "choices" => [
                    "category.collectivity",
                    "category.individual",
                    "category.professional",
                ],
                "default" => "category.professional",
            ]
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

        // Field employees
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'employees',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
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

        // Field business_sector
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'business_sector',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [
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
                    "Réseau Professionnel",
                    "Relation",
                    "Autre"
                ]
            ]
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

        // Block block.contact
        $block = Block::create([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.contact',
            'icon' => 'phone',
            'sequence' => $tab->blocks()->count(),
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

        // Field fax
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'fax',
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

        // Field website
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'website',
            'uitype_id' => uitype('url')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Block block.administrative
        $block = Block::create([
            'module_id' => $module->id,
            'tab_id' => $tab->id,
            'label' => 'block.administrative',
            'icon' => 'account_balance',
            'sequence' => $tab->blocks()->count(),
            'data' => null
        ]);

        // Field siret
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'siret',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field vat_intra
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'vat_intra',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field naf_code
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'naf_code',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field iban
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'iban',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field bic
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'bic',
            'uitype_id' => uitype('text')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => null
        ]);

        // Field payment_mode
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'payment_mode',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [
                "choices" => [
                    "payment_mode.all",
                    "payment_mode.transfer",
                    "payment_mode.check_transfer",
                    "payment_mode.check",
                    "payment_mode.cash",
                    "payment_mode.electronic",
                    "payment_mode.withdrawal",
                    "payment_mode.bill_exchange",
                    "payment_mode.credit_card",
                    "payment_mode.tip",
                ]
            ]
        ]);

        // Field payment_validity
        Field::create([
            'module_id' => $module->id,
            'block_id' => $block->id,
            'name' => 'payment_validity',
            'uitype_id' => uitype('select')->id,
            'displaytype_id' => displaytype('everywhere')->id,
            'sequence' => $block->fields()->count(),
            'data' => [
                "choices" => [
                    "payment_validity.paid",
                    "payment_validity.5_days",
                    "payment_validity.15_days",
                    "payment_validity.30_days",
                    "payment_validity.45_days",
                    "payment_validity.45_days_month_end",
                    "payment_validity.60_days",
                    "payment_validity.month_end",
                    "payment_validity.month_end_5",
                    "payment_validity.30_days_month_end",
                    "payment_validity.60_days_month_end",
                ]
            ]
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
            'columns' => ['title', 'name', 'code', 'type', 'category', 'email', 'phone' ],
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
            'columns' => ['title', 'name', 'code', 'type', 'category', 'email', 'phone' ],
            'conditions' => json_decode('{"search":{"type":"type.customer"}}'),
            'order' => null,
            'is_default' => false,
            'is_public' => false
        ]);
        $filter->save();

        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.leads',
            'type' => 'list',
            'columns' => ['title', 'name', 'code', 'type', 'category', 'email', 'phone' ],
            'conditions' => json_decode('{"search":{"type":"type.lead"}}'),
            'order' => null,
            'is_default' => false,
            'is_public' => false
        ]);
        $filter->save();

        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.prospects',
            'type' => 'list',
            'columns' => ['title', 'name', 'code', 'type', 'category', 'email', 'phone' ],
            'conditions' => json_decode('{"search":{"type":"type.prospect"}}'),
            'order' => null,
            'is_default' => false,
            'is_public' => false
        ]);
        $filter->save();

        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.old_customers',
            'type' => 'list',
            'columns' => ['title', 'name', 'code', 'type', 'category', 'email', 'phone' ],
            'conditions' => json_decode('{"search":{"type":"type.old_customer"}}'),
            'order' => null,
            'is_default' => false,
            'is_public' => false
        ]);
        $filter->save();

        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.vendors',
            'type' => 'list',
            'columns' => ['title', 'name', 'code', 'type', 'category', 'email', 'phone' ],
            'conditions' => json_decode('{"search":{"type":"type.vendor"}}'),
            'order' => null,
            'is_default' => false,
            'is_public' => false
        ]);
        $filter->save();

        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.sub_contractors',
            'type' => 'list',
            'columns' => ['title', 'name', 'code', 'type', 'category', 'email', 'phone' ],
            'conditions' => json_decode('{"search":{"type":"type.sub_contractor"}}'),
            'order' => null,
            'is_default' => false,
            'is_public' => false
        ]);
        $filter->save();

        $filter = new Filter([
            'module_id' => $module->id,
            'domain_id' => null,
            'user_id' => null,
            'name' => 'filter.business_providers',
            'type' => 'list',
            'columns' => ['title', 'name', 'code', 'type', 'category', 'email', 'phone' ],
            'conditions' => json_decode('{"search":{"type":"type.business_provider"}}'),
            'order' => null,
            'is_default' => false,
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
            'sequence' => 3,
            'data' => [ 'actions' => [ 'add', 'select' ] ]
        ]);
    }

    protected function createLinks($module)
    {
    }
}