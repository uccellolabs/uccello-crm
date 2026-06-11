<?php

namespace App\Infrastructure\Assistant\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchRecordsTool extends BaseCrmTool
{
    public function name(): string
    {
        return 'search_records';
    }

    public function description(): Stringable|string
    {
        return 'Recherche des enregistrements dans un module du CRM. Renvoie les champs standards '
            .'ET les champs personnalisés (custom fields) de chaque enregistrement.';
    }

    public function handle(Request $request): Stringable|string
    {
        return $this->run('search_records', $request);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'module' => $schema->string()->enum($this->tools->modules())->description('Le module à interroger.')->required(),
            'query' => $schema->string()->description('Texte libre recherché dans les champs principaux (nom, email, titre…).'),
            'filters' => $schema->string()->description(
                'Filtres au format JSON, ex: {"city":"Paris","segment":"ent"}. '
                .'Clés possibles : champs standards, relations (owner, company, stage, assignee…) ou clé d\'un champ personnalisé.'
            ),
            'sort' => $schema->string()->description('Champ de tri (ex: created_at, amount, due_at).'),
            'direction' => $schema->string()->enum(['asc', 'desc'])->description('Sens du tri.'),
            'limit' => $schema->integer()->description('Nombre max d\'enregistrements (défaut 25, max 25).'),
        ];
    }
}
