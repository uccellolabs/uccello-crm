<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GIN indexes accelerate containment/key lookups on the custom_fields
     * jsonb columns (used when filtering on custom fields).
     */
    public function up(): void
    {
        foreach (['companies', 'contacts', 'deals', 'tasks'] as $table) {
            DB::statement("CREATE INDEX {$table}_custom_fields_gin ON {$table} USING gin (custom_fields jsonb_path_ops)");
        }
    }

    public function down(): void
    {
        foreach (['companies', 'contacts', 'deals', 'tasks'] as $table) {
            DB::statement("DROP INDEX IF EXISTS {$table}_custom_fields_gin");
        }
    }
};
