@extends('uccello::modules.default.detail.main')

@section('content')

    {{-- Tab list --}}
    @include('uccello::modules.default.detail.tabs')

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

    <div class="detail-blocks">
        @section('default-tabs')
            {{-- Summary --}}
            @if ($widgets->count() > 0)
            <div id="summary" @if ((empty($selectedTabId) && empty($selectedRelatedlistId) && $widgets->count() > 0) || $selectedTabId === 'summary')class="active"@endif>
                @include('uccello::modules.default.detail.summary')
            </div>
            @endif

            {{-- Tabs and blocks --}}
            @foreach ($module->tabs as $i => $tab)
            <div id="{{ $tab->id }}" @if ((empty($selectedTabId) && empty($selectedRelatedlistId) && $i === 0 && $widgets->count() === 0) || $selectedTabId === $tab->id)class="active"@endif>
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
        @show

        {{-- Other blocks --}}
        @yield('other-blocks')
    </div>

    {{-- Action buttons --}}
    @section('page-action-buttons')
        @include('uccello::modules.default.detail.buttons')
    @show
@endsection

@section('extra-script')
<script>
$(document).ready(function() {
    var taskListContentUrl = "{{ ucroute('uccello.list.content', $domain, ucmodule('task')) }}"

    $('#tasks-period').on('change', (ev) => {
        var value = $(ev.currentTarget).val()
        var dateStart, dateEnd = ''
        switch(value) {
            case 'today':
                dateStart = dateEnd = moment().format('YYYY-MM-DD')
            break

            case 'month':
                dateStart = moment().startOf('month').format('YYYY-MM-DD')
                dateEnd = moment().endOf('month').format('YYYY-MM-DD')
            break

            case 'week':
            default:
                dateStart = moment().lang($('html').attr('lang')).startOf('week').format('YYYY-MM-DD')
                dateEnd = moment().lang($('html').attr('lang')).endOf('week').format('YYYY-MM-DD')
            break
        }

        var url = taskListContentUrl+"?start="+dateStart+"&end="+dateEnd

        $('.tasks-widget-card table').attr('data-content-url', url)
        var datatableId = $('.tasks-widget-card table').attr('id')
        var event = new CustomEvent('uccello.list.refresh', {detail: datatableId});
        dispatchEvent(event);
    })
})
</script>
@append