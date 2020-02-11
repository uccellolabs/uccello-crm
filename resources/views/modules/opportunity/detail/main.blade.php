@extends('uccello::modules.default.detail.main')

@section('breadcrumb')
    <div class="nav-wrapper">
        <div class="col s12">
            <div class="breadcrumb-container left">
                {{-- Module icon --}}
                <span class="breadcrumb">
                    <a class="btn-flat" href="{{ ucroute('uccello.list', $domain, $module) }}">
                        <i class="material-icons left">{{ $module->icon ?? 'extension' }}</i>
                        <span class="hide-on-small-only">{{ uctrans($module->name, $module) }}</span>
                    </a>
                </span>
                @if (!empty($record->account))<a  class="breadcrumb" href="{{ ucroute('uccello.detail', $domain, ucmodule('account'), ['id' => $record->account_id]) }}">{{ $record->account->name }}</a>@endif
                <span class="breadcrumb active">{{ $record->recordLabel }}</span>
            </div>
        </div>
    </div>
@endsection

@section('after-tabs')
    {{-- Status --}}
    <div class="progress" style="height: 33px">
        <?php $field = $module->fields()->where('name', 'step')->first(); ?>
        <?php $width = 100 / count($field->data->choices); ?>
        @foreach ($field->data->choices as $i => $step)
            @if ($record->step === $step)
            <div class="determinate green white-text center-align troncate"
                style="width: {{ $width }}%; left: {{ $i * $width }}%; padding-top: 5px; white-space: nowrap">
                    {{ uctrans($step, $module)}}
            </div>
            @else
                <a href="{{ ucroute('crm.opportunity.step.update', $domain, $module, ['id' => $record->id, 'status' => $step]) }}"
                    class="determinate grey white-text center-align troncate"
                    style="width: {{ $width }}%; left: {{ $i * $width }}%; padding-top: 5px; white-space: nowrap"
                    data-toggle="tooltip"
                    data-tooltip="{{ uctrans('button.edit_step', $module) }}"
                    data-position="top">
                    {{ uctrans($step, $module)}}
                </a>
            @endif
        @endforeach
    </div>
@endsection