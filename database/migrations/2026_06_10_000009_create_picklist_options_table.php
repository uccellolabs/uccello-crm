<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('picklist_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('picklist');
            $table->string('value');
            $table->string('label');
            $table->string('color', 9)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['team_id', 'picklist', 'value']);
            $table->index(['team_id', 'picklist', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('picklist_options');
    }
};
