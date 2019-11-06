<?php

namespace Uccello\Crm\Http\Controllers\Account;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Events\BeforeSaveEvent;
use Uccello\Core\Events\AfterSaveEvent;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Crm\Models\Account;
use Uccello\Crm\Models\Contact;
use Uccello\Crm\Models\Opportunity;

class DetailController extends Controller
{
    public function updateStatus(?Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $record = Account::find(request('id'));

        if (!is_null($record)) {

            event(new BeforeSaveEvent($domain, $module, $request, $record, 'edit'));

            $record->lead_status = request('status');
            $record->save();

            event(new AfterSaveEvent($domain, $module, $request, $record, 'edit'));
        }

        return redirect()->back();
    }

    public function getRelatedRecords(?Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $record = Account::find(request('id'));

        if (!is_null($record)) {
            // Get related contacts
            $relatedContacts = collect();
            $contacts = Contact::where('account_id', $record->id)->get();
            foreach ($contacts as $contact) {
                $relatedContacts->push([
                    'id' => $contact->getKey(),
                    'module' => 'contact',
                    'label' => $contact->recordLabel,
                ]);
            }

            // Get related opportunities
            $relatedOpportunities = collect();
            $opportunities = Opportunity::where('account_id', $record->id)->get();
            foreach ($opportunities as $opportunity) {
                $relatedOpportunities->push([
                    'id' => $opportunity->getKey(),
                    'module' => 'opportunity',
                    'label' => $opportunity->recordLabel
                ]);
            }
        }

        $data = [
            'record' => $record,
            'contacts' => $relatedContacts,
            'opportunities' => $relatedOpportunities,
        ];

        return $data;
    }
}
