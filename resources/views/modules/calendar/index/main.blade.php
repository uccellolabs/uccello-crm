@extends('calendar::modules.calendar.index.main')

@section('extra-meta')
<meta name="related-records-url" content="{{ ucroute('crm.account.related.records', $domain) }}">
@append

@section('before-subject')
<div class="row" style="margin-bottom: 0">
    <div class="input-field col s8">
        <i class="material-icons prefix primary-text">domain</i>
        <input id="account" class="autocomplete emptyable" type="text" data-url="{{ ucroute('uccello.autocomplete', $domain, ucmodule('account')) }}">
        <label for="account">{{ uctrans('field.account', $module) }}</label>
    </div>

    <div id="related_record_container" class="input-field col s4" style="display: none">
        <select id="related_record"></select>
        <label for="related_record">{{ uctrans('field.related_record', $module) }}</label>
    </div>
</div>
@endsection

@section('extra-script')
{!! Html::script(mix('js/calendar/modal.js', 'vendor/uccello/crm')) !!}
@append