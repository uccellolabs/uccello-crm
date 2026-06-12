<?php

use App\Http\Controllers\Crm\ActivityController;
use App\Http\Controllers\Crm\AssistantController;
use App\Http\Controllers\Crm\CompanyController;
use App\Http\Controllers\Crm\ContactController;
use App\Http\Controllers\Crm\CustomFieldDefinitionController;
use App\Http\Controllers\Crm\DealController;
use App\Http\Controllers\Crm\PicklistOptionController;
use App\Http\Controllers\Crm\PipelineSettingsController;
use App\Http\Controllers\Crm\TaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// UI language preference — available to everyone, incl. guests on the auth screens.
Route::put('locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', function (Request $request) {
    $user = $request->user();

    if (! $user) {
        return redirect()->route('login');
    }

    $team = $user->currentTeam ?? $user->personalTeam();

    if (! $team) {
        return redirect()->route('login');
    }

    return redirect("/{$team->slug}/dashboard");
})->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::post('assistant/chat', [AssistantController::class, 'chat'])
            ->middleware('throttle:10,1')
            ->name('assistant.chat');

        Route::resource('companies', CompanyController::class);
        Route::resource('contacts', ContactController::class);

        Route::get('pipeline', [DealController::class, 'board'])->name('deals.board');
        Route::patch('deals/{deal}/move', [DealController::class, 'move'])->name('deals.move');
        Route::resource('deals', DealController::class)
            ->only(['create', 'store', 'show', 'edit', 'update', 'destroy']);

        Route::resource('tasks', TaskController::class)->only(['index', 'create', 'store', 'update', 'destroy']);
        Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');

        Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
        Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

        Route::patch('custom-fields/reorder', [CustomFieldDefinitionController::class, 'reorder'])->name('custom-fields.reorder');
        Route::resource('custom-fields', CustomFieldDefinitionController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['custom-fields' => 'customField']);

        Route::patch('picklists/reorder', [PicklistOptionController::class, 'reorder'])->name('picklists.reorder');
        Route::resource('picklists', PicklistOptionController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['picklists' => 'picklistOption']);

        Route::get('pipeline-settings', [PipelineSettingsController::class, 'index'])->name('pipeline-settings.index');
        Route::post('pipeline-settings/stages', [PipelineSettingsController::class, 'storeStage'])->name('pipeline-settings.stages.store');
        Route::patch('pipeline-settings/stages/reorder', [PipelineSettingsController::class, 'reorderStages'])->name('pipeline-settings.stages.reorder');
        Route::patch('pipeline-settings/stages/{stage}', [PipelineSettingsController::class, 'updateStage'])->name('pipeline-settings.stages.update');
        Route::delete('pipeline-settings/stages/{stage}', [PipelineSettingsController::class, 'destroyStage'])->name('pipeline-settings.stages.destroy');
    });

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
});

require __DIR__.'/settings.php';
