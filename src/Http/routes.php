<?php

Route::middleware('web', 'auth')
->namespace('Uccello\Crm\Http\Controllers')
->name('crm.')
->group(function() {

    // This makes it possible to adapt the parameters according to the use or not of the multi domains
    if (!uccello()->useMultiDomains()) {
        $domainParam = '';
        $domainAndModuleParams = '{module}';
    } else {
        $domainParam = '{domain}';
        $domainAndModuleParams = '{domain}/{module}';
    }

    Route::get($domainParam.'/account/status', 'Account\DetailController@updateStatus')
    ->defaults('module', 'account')
    ->name('account.status.update');

    Route::get($domainParam.'/account/related_records', 'Account\DetailController@getRelatedRecords')
        ->defaults('module', 'account')
        ->name('account.related.records');

    Route::get($domainParam.'/opportunity/step', 'Opportunity\DetailController@updateStep')
        ->defaults('module', 'opportunity')
        ->name('opportunity.step.update');

    Route::get($domainParam.'/opportunity/phase', 'Opportunity\DetailController@updatePhase')
        ->defaults('module', 'opportunity')
        ->name('opportunity.phase.update');

    Route::get($domainParam.'/opportunity/kanban', 'Opportunity\KanbanController@process')
        ->defaults('module', 'opportunity')
        ->name('opportunity.kanban');

    Route::get($domainParam.'/opportunity/kanban/boards', 'Opportunity\KanbanController@getBoards')
        ->defaults('module', 'opportunity')
        ->name('opportunity.kanban.boards');
});