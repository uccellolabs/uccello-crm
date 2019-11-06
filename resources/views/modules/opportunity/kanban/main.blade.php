@extends('layouts.uccello')

@section('page', 'kanban')

@section('extra-meta')
<meta name="kanban-url" content="{{ ucroute('crm.opportunity.kanban.boards', $domain, $module) }}">
<meta name="kanban-update-phase-url" content="{{ ucroute('crm.opportunity.phase.update', $domain, $module) }}">
<meta name="kanban-opportunity-url" content="{{ ucroute('uccello.detail', $domain, $module) }}">
@append

@section('navbar-top')
<header class="navbar-fixed navbar-top">
    <nav class="transparent z-depth-0">
        <div class="row">
            <div class="col s12 m7">
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

                            {{-- List view --}}
                            <a href="{{ ucroute('uccello.list', $domain, $module) }}"
                                class="btn-floating btn-small waves-effect orange z-depth-0"
                                data-position="top"
                                data-tooltip="{{ uctrans('view.kanban', $module) }}"
                                style="margin-left: 10px">
                                <i class="material-icons">list</i>
                            </a>
                        </div>
                    </div>
                </div>
                @show
            </div>

            <div class="col s12 m5 hide-on-small-only">
                @section('top-action-buttons')
                <div class="row">
                    <div class="input-field col s6">
                        <select id="closing_date">
                            @include('crm::modules.opportunity.kanban.period')
                        </select>
                        {{-- <label>{{ uctrans('kanban.closing_date.label', $module) }}</label> --}}
                    </div>

                    <div class="input-field col s6">
                        <select id="assigned_user" multiple>
                            <optgroup label="Utilisateurs">
                                <option value="{{ auth()->user()->uuid }}" selected>{{ uctrans('kanban.assigned_user.me', $module) }}</option>
                                {{-- <option value="">{{ uctrans('kanban.assigned_user.all', $module) }}</option> --}}
                                @foreach ($users as $user)
                                    @continue($user->id === auth()->id())
                                    <option value="{{ $user->uuid }}">{{ $user->recordLabel }}</option>
                                @endforeach
                            </optgroup>
                            @if ($groups->count() > 0)
                            <optgroup label="Groupes">
                                @foreach ($groups as $group)
                                    <option value="{{ $group->uuid }}">{{ $group->recordLabel }}</option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                        {{-- <label>{{ uctrans('kanban.assigned_user.label', $module) }}</label> --}}
                    </div>
                </div>
                @show
            </div>
        </div>
    </nav>
</header>
@endsection


@section('content')
    <div id="kanban-loader" style="display: none; position: absolute; z-index: 100; left: calc(50% - 40px); top: calc(50% - 40px)">
        <div class="preloader pl-size-xl">
            <div class="spinner-layer pl-green">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div>
                <div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="kanban-board" style="width: 100%; overflow-x: auto"></div>

    <div id="page-action-buttons">
        @if (Auth::user()->canCreate($domain, $module))
        <a href="{{ ucroute('uccello.edit', $domain, $module) }}" class="btn-large btn-floating green waves-effect" data-tooltip="{{ uctrans('button.new', $module) }}" data-position="top">
            <i class="material-icons">add</i>
        </a>
        @endif
    </div>
@endsection

@section('extra-css')
    {!! Html::style(mix('css/crm.css', 'vendor/uccello/crm')) !!}
@append

@section('extra-script')
    {!! Html::script(mix('js/opportunity/autoloader.js', 'vendor/uccello/crm')) !!}
@append