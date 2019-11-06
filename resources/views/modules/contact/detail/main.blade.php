@extends('uccello::modules.default.detail.main')

@section('breadcrumb')
    <div class="nav-wrapper">
        <div class="col s12">
            <div class="breadcrumb-container left">
                {{-- Admin --}}
                @if ($admin_env)
                <span class="breadcrumb">
                    <a class="btn-flat" href="{{ ucroute('uccello.settings.dashboard', $domain) }}">
                        <i class="material-icons left">settings</i>
                        <span class="hide-on-small-only">{{ uctrans('breadcrumb.admin', $module) }}</span>
                    </a>
                </span>
                @endif

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