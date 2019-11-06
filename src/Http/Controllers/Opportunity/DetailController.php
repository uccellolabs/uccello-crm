<?php

namespace Uccello\Crm\Http\Controllers\Opportunity;

use Uccello\Crm\Models\Opportunity;
use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Events\BeforeSaveEvent;
use Uccello\Core\Events\AfterSaveEvent;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;

class DetailController extends Controller
{
    /**
     * Update opportunity step
     *
     * @param Domain|null $domain
     * @param Module $module
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function updateStep(?Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $record = Opportunity::find(request('id'));
        if (!is_null($record)) {

            event(new BeforeSaveEvent($domain, $module, $request, $record, 'edit'));

            $record->step = request('status');
            $record->save();

            event(new AfterSaveEvent($domain, $module, $request, $record, 'edit'));
        }

        return redirect()->back();
    }

    /**
     * Update opportunity selling phase
     *
     * @param Domain|null $domain
     * @param Module $module
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function updatePhase(?Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $record = Opportunity::find(request('id'));
        if (!is_null($record)) {

            event(new BeforeSaveEvent($domain, $module, $request, $record, 'edit'));

            $record->phase = request('value');

            // Change step according to phase
            if ($record->phase === 'phase.5.won' && $record->step !== 'step.won') {
                $record->step = 'step.won';
                $record->save();
            } elseif ($record->phase === 'phase.6.lost' && $record->step !== 'step.lost') {
                $record->step = 'step.lost';
                $record->save();
            }

            $record->save();

            event(new AfterSaveEvent($domain, $module, $request, $record, 'edit'));
        }

        return $record;
    }
}
