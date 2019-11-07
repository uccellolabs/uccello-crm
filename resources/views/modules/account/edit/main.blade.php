@extends('uccello::modules.default.edit.main')

@section('extra-script')
    {!! Html::script(mix('js/account/autoloader.js', 'vendor/uccello/crm')) !!}
@append