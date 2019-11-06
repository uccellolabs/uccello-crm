@extends('uccello::modules.default.edit.main')

@section('other-blocks')
<?php
    $isDone = (bool) old('done', false) || (!empty($record) && ((bool) $record->done));
?>
<div class="row">
    <div class="col s12">
        <div id="block-reminder" class="card" @if (!$isDone)style="display: none"@endif>
            <div class="card-content">
                {{-- Title --}}
                <div class="card-title">
                    {{-- Icon --}}
                    <i class="material-icons primary-text left">perm_phone_msg</i>

                    {{-- Label --}}
                    {{ uctrans('block.reminder', $module) }}

                    {{-- Description --}}
                    <small class="with-icon">{{ uctrans('block.reminder_description', $module) }}</small>
                </div>

                <div class="row display-flex">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">date_range</i>
                    <input id="reminder_datetime" type="text" autocomplete="off" name="reminder_datetime" value="{{ old('reminder_datetime') }}" class="datetimepicker" data-format="{{ config('uccello.format.js.datetime') }}">
                        <label for="reminder_datetime">{{ uctrans('field.reminder_datetime', $module) }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-script')
<script>
    $("#done").on("change", function() {
        if ($(this).is(":checked")) {
            $("#block-reminder").show()
        } else {
            $("#block-reminder").hide()
        }
    })
</script>
@append