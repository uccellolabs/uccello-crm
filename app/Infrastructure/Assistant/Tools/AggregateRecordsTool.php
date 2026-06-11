<?php

namespace App\Infrastructure\Assistant\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Tools\Request;
use Stringable;

class AggregateRecordsTool extends BaseCrmTool
{
    public function name(): string
    {
        return 'aggregate_records';
    }

    public function description(): Stringable|string
    {
        return 'Calcule des agrégats sur un module : nombre d\'enregistrements, somme ou moyenne d\'un champ '
            .'numérique (ex: montant des opportunités), avec regroupement optionnel par champ ou champ personnalisé.';
    }

    public function handle(Request $request): Stringable|string
    {
        return $this->run('aggregate_records', $request);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'module' => $schema->string()->enum($this->tools->modules())->description('Le module à agréger.')->required(),
            'operation' => $schema->string()->enum(['count', 'sum', 'avg'])->description('L\'opération.')->required(),
            'field' => $schema->string()->description('Champ numérique pour sum/avg (ex: amount pour les opportunités).'),
            'group_by' => $schema->string()->description('Regroupement : status, stage, owner, industry, priority, type, ou clé d\'un champ personnalisé.'),
            'filters' => $schema->string()->description('Filtres au format JSON, ex: {"status":"open"}.'),
        ];
    }
}
