<?php

namespace App\Infrastructure\Assistant\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetRecordTool extends BaseCrmTool
{
    public function name(): string
    {
        return 'get_record';
    }

    public function description(): Stringable|string
    {
        return 'Récupère la fiche complète d\'un enregistrement : champs standards, champs personnalisés résolus, '
            .'enregistrements liés (contacts, opportunités), activités et tâches associées.';
    }

    public function handle(Request $request): Stringable|string
    {
        return $this->run('get_record', $request);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'module' => $schema->string()->enum($this->tools->modules())->description('Le module de l\'enregistrement.')->required(),
            'id' => $schema->integer()->description('Identifiant de l\'enregistrement.')->required(),
        ];
    }
}
