<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type'); // company | contact | deal | task
            $table->string('key');
            $table->string('label');
            $table->string('type'); // text|textarea|number|date|select|multiselect|checkbox|email|url|phone
            $table->jsonb('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('help_text')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'entity_type', 'position']);
        });

        // Partial unique index so a soft-deleted field key can be re-created.
        DB::statement(
            'CREATE UNIQUE INDEX custom_field_definitions_team_entity_key_unique '
            .'ON custom_field_definitions (team_id, entity_type, key) WHERE deleted_at IS NULL'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_definitions');
    }
};
