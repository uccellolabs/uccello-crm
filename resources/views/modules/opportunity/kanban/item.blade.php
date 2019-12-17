<div class="row" style="margin-bottom: 0">
        <div class="col s12">
            <small data-tooltip="{{ trans('opportunity.kanban.item.closing_date') }}" data-position="top">{{ !empty($opportunity->closing_date) ? (new \Carbon\Carbon($opportunity->closing_date))->format('d/m/Y') : '' }}</small>
        </div>
        <div class="col s12">
            <a href="#" class="black-text"><strong >{{ $opportunity->recordLabel }}</strong></a><br>
            <span class="primary-text">{{ $opportunity->account->recordLabel ?? '' }}</span><br>
            {{ ucrecord($opportunity->assigned_user_id)->recordLabel ?? '' }}
        </div>
        <div class="col s7">
            <small class="green-text step">{{ uctrans($opportunity->step, $module) }}</small>
        </div>
        <div class="col s5 right-align">
            <span class="red-text amount" data-amount="{{ $opportunity->amount }}">{{ number_format($opportunity->amount, 0, ',', ' ') }} €</span>
        </div>
        {{-- @if (Auth::user()->canUpdate($domain, $module))
        <div class="col s12 right-align">
            <a href="{{ ucroute('uccello.edit', $domain, $module, ['id' => $opportunity->getKey()]) }}" class="btn-floating btn-large waves-effect green" data-tooltip="{{ uctrans('button.edit', $module) }}" data-position="top">
                <i class="material-icons">edit</i>
            </a>
        </div>
        @endif --}}
</div>