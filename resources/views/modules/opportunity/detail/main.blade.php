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

@section('content')

    {{-- Tab list --}}
    @include('uccello::modules.default.detail.tabs')

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

    <div class="detail-blocks">
        @section('default-blocks')
            <div class="tab-content">
                {{-- Summary --}}
                @if ($widgets->count() > 0)
                <div role="tabpanel" id="summary" class="tab-pane fade in @if ((empty($selectedTabId) && empty($selectedRelatedlistId) && $widgets->count() > 0) || $selectedTabId === 'summary')active @endif" >
                    @include('crm::modules.opportunity.detail.summary')
                </div>
                @endif

                {{-- Tabs and blocks --}}
                @foreach ($module->tabs as $i => $tab)
                <div role="tabpanel" id="{{ $tab->id }}" class="tab-pane fade in @if ((empty($selectedTabId) && empty($selectedRelatedlistId) && $i === 0 && $widgets->count() === 0) || $selectedTabId === $tab->id)active @endif">
                    {{-- Blocks --}}
                    @include('uccello::modules.default.detail.blocks')

                    {{-- Related lists as blocks --}}
                    @include('uccello::modules.default.detail.relatedlists.as-blocks')
                </div>
                @endforeach

                {{-- Related lists as tabs --}}
                @include('uccello::modules.default.detail.relatedlists.as-tabs')

                {{-- Other tabs --}}
                @yield('other-tabs')
            </div>
        @show

        {{-- Other blocks --}}
        @yield('other-blocks')
    </div>

    {{-- Action buttons --}}
    @section('page-action-buttons')
        @include('uccello::modules.default.detail.buttons')
    @show
@endsection