@extends('uccello::modules.default.detail.main')

@section('after-tabs')
    {{-- Status --}}
    @if ($record->type === 'type.prospect' || $record->type === 'type.lead')
    <div class="progress" style="height: 33px">
        <?php $field = $module->fields()->where('name', 'lead_status')->first(); ?>
        <?php $width = 100 / count($field->data->choices); ?>
        @foreach ($field->data->choices as $i => $status)
            @if ($record->lead_status === $status)
            <div class="determinate green white-text center-align troncate"
                style="width: {{ $width }}%; left: {{ $i * $width }}%; padding-top: 5px; white-space: nowrap">
                    {{ uctrans($status, $module)}}
            </div>
            @else
                <a href="{{ ucroute('crm.account.status.update', $domain, $module, ['id' => $record->id, 'status' => $status]) }}"
                    class="determinate grey white-text center-align troncate"
                    style="width: {{ $width }}%; left: {{ $i * $width }}%; padding-top: 5px; white-space: nowrap"
                    data-toggle="tooltip"
                    data-tooltip="{{ uctrans('button.edit_status', $module) }}"
                    data-position="top">
                    {{ uctrans($status, $module)}}
                </a>
            @endif
        @endforeach
    </div>
    @endif
@endsection