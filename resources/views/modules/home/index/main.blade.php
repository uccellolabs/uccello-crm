@extends('layouts.uccello')

@section('page', 'index')

@section('breadcrumb')
<div class="nav-wrapper">
    <div class="col s12">
        <div class="breadcrumb-container left">
            <span class="breadcrumb">
                <a class="btn-flat" href="{{ ucroute('uccello.home', $domain) }}">
                    <i class="material-icons left">{{ $module->icon ?? 'extension' }}</i>
                </a>
            </span>
            <span class="breadcrumb active">{{ uctrans('home', $module) }}</span>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    {{-- Customers --}}
    <?php
        $accountModule = ucmodule('account');
        $count = \Uccello\Crm\Models\Account::where('type', 'type.customer')->count();
    ?>
    @if (Auth::user()->canRetrieve($domain, $accountModule))
    <div class="col s12 m6 l3">
        <div class="card horizontal info-box">
            <div class="icon primary">
                <i class="material-icons">{{ $accountModule->icon }}</i>
            </div>
            <div class="card-stacked">
                <div class="card-content">
                    <div class="text uppercase">{{ uctrans('customers', $accountModule)}}</div>
                    <div class="number count-to" data-from="0" data-to="{{ $count }}" data-speed="1000" data-fresh-interval="20">{{ $count }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Leads --}}
    <?php
        $accountModule = ucmodule('account');
        $count = \Uccello\Crm\Models\Account::where('type', 'type.lead')->count();
    ?>
    @if (Auth::user()->canRetrieve($domain, $accountModule))
    <div class="col s12 m6 l3">
        <div class="card horizontal info-box">
            <div class="icon green">
                <i class="material-icons">gps_fixed</i>
            </div>
            <div class="card-stacked">
                <div class="card-content">
                    <div class="text uppercase">{{ uctrans('leads', $accountModule)}}</div>
                    <div class="number count-to" data-from="0" data-to="{{ $count }}" data-speed="1000" data-fresh-interval="20">{{ $count }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Contacts --}}
    <?php $contactModule = ucmodule('contact'); ?>
    @if (Auth::user()->canRetrieve($domain, $contactModule))
    <div class="col s12 m6 l3">
        <div class="card horizontal info-box">
            <div class="icon red">
                <i class="material-icons">{{ $contactModule->icon }}</i>
            </div>
            <div class="card-stacked">
                <div class="card-content">
                    <div class="text uppercase">{{ uctrans('leads', $accountModule)}}</div>
                    <div class="number count-to" data-from="0" data-to="{{ \Uccello\Crm\Models\Contact::count() }}" data-speed="1000" data-fresh-interval="20">{{ \Uccello\Crm\Models\Contact::count() }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Opportunities --}}
    <?php $opportunityModule = ucmodule('opportunity'); ?>
    @if (Auth::user()->canRetrieve($domain, $opportunityModule))
    <div class="col s12 m6 l3">
        <div class="card horizontal info-box">
            <div class="icon orange">
                <i class="material-icons">{{ $opportunityModule->icon }}</i>
            </div>
            <div class="card-stacked">
                <div class="card-content">
                    <div class="text uppercase">{{ uctrans('opportunity', $opportunityModule)}}</div>
                    <div class="number count-to" data-from="0" data-to="{{ \Uccello\Crm\Opportunity::count() }}" data-speed="1000" data-fresh-interval="20">{{ \Uccello\Crm\Models\Opportunity::count() }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="row" style="margin-bottom: 0">
    <div class="col s12 m6">
        @widget('CalendarWidget')
    </div>

    <div class="col s12 m6">
        @widget('TasksWidget', ['domain' => $domain])
    </div>
</div>
@endsection