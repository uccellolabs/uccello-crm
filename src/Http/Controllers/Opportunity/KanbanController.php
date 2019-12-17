<?php

namespace Uccello\Crm\Http\Controllers\Opportunity;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Core\Models\Field;
use Uccello\Crm\Models\Opportunity;
use App\User;
use Uccello\Crm\Models\Product;
use Carbon\Carbon;
use Uccello\Core\Models\Group;

class KanbanController extends Controller
{
    protected $viewName = 'kanban.main';

    /**
     * Check user permissions
     */
    protected function checkPermissions()
    {
        $this->middleware('uccello.permissions:retrieve');
    }

    /**
     * Process and display asked page
     * @param Domain|null $domain
     * @param Module $module
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function process(?Domain $domain, Module $module, Request $request)
    {
        // Pre-process
        $this->preProcess($domain, $module, $request);

        $users = User::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return $this->autoView(compact('users', 'groups', 'products'));
    }

    /**
     * Get kanban structure
     *
     * @param Domain|null $domain
     * @param Module $module
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    protected function getBoards(?Domain $domain, Module $module)
    {
        // Pre-process
        $this->preProcess($domain, $module, request());

        $boards = [];

        $field = Field::where('module_id', $module->id)->where('name', 'phase')->first();

        $phase = $field->data->choices ?? [];
        foreach ($phase as $i => $phase) {
            $board = new \StdClass(

            );
            $board->id = $phase;
            $board->title = uctrans($phase, $module).'<small class="right total-amount"></small>';
            $board->class = $this->getClass($i);
            $board->item = $this->getItems($phase);

            $boards[] = $board;
        }

        return $boards;
    }

    /**
     * Get all items with a certain phase
     *
     * @param string $phase
     * @return array
     */
    protected function getItems($phase)
    {
        $items = [];

        $domain = $this->domain;
        $module = $this->module;

        $opportunities = Opportunity::where('phase', $phase);

        // Filter on closing_date
        $closingDate = $this->getPeriodDates(request('closing_date'));

        $closingDateStart = $closingDate['start'];
        $closingDateEnd = $closingDate['end'];

        if (request('closing_date') === 'month') {
            $opportunities->where(function ($query) use($closingDateStart, $closingDateEnd, $phase) {
                if (!in_array($phase, [ 'phase.5_won', 'phase.6_lost' ])) {
                    $query->whereBetween('closing_date', [ $closingDateStart, $closingDateEnd ])
                        ->orWhereBetween('closing_date', [ $closingDateStart->copy()->subMonth(), $closingDateEnd->copy()->subMonth() ]);
                } else {
                    $query->whereBetween('closing_date', [ $closingDateStart, $closingDateEnd ]);
                }
            });
        } else {
            $opportunities->whereBetween('closing_date', [ $closingDateStart, $closingDateEnd ]);
        }

        // Filter on user
        if (request('user')) {
            $userIds = explode(',', request('user'));
            $opportunities->whereIn('assigned_user_id', $userIds);
        }

        foreach ($opportunities->get() as $opportunity) {
            $item = new \StdClass;
            $item->id = $opportunity->id;
            $item->title = view('crm::modules.opportunity.kanban.item', compact('domain', 'module', 'opportunity'))->render();
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get dates from period name
     *
     * @param string $periodName
     * @return array
     */
    protected function getPeriodDates($periodName)
    {
        switch ($periodName) {
            case 'week':
                $dateStart = Carbon::now()->startOfWeek();
                $dateEnd = $dateStart->copy()->endOfWeek();
            break;

            case 'last_week':
                $dateStart = Carbon::now()->startOfWeek()->subWeek(1);
                $dateEnd = $dateStart->copy()->endOfWeek();
            break;

            case 'next_week':
                $dateStart = Carbon::now()->startOfWeek()->addWeek(1);
                $dateEnd = $dateStart->copy()->endOfWeek();
            break;

            case 'quarter':
                $dateStart = Carbon::now()->startOfQuarter();
                $dateEnd = $dateStart->copy()->endOfQuarter();
            break;

            case 'last_quarter':
                $dateStart = Carbon::now()->startOfQuarter()->subQuarter(1);
                $dateEnd = $dateStart->copy()->endOfQuarter();
            break;

            case 'next_quarter':
                $dateStart = Carbon::now()->startOfQuarter()->addQuarter(1);
                $dateEnd = $dateStart->copy()->endOfQuarter();
            break;

            case 'semester':
                $startOfYear = Carbon::now()->startOfYear();

                if (Carbon::now()->startOfQuarter()->subQuarters(2)->lt($startOfYear)) {
                    $dateStart = $startOfYear;
                } else {
                    $dateStart = $startOfYear->startOfQuarter()->addQuarters(2);
                }

                $dateEnd = $dateStart->copy()->addQuarters(1)->endOfQuarter();
            break;

            case 'last_semester':
                $startOfYear = Carbon::now()->startOfYear();

                if (Carbon::now()->subQuarters(2)->startOfQuarter()->lt($startOfYear)) {
                    $dateStart = $startOfYear->startOfQuarter()->subQuarters(2);
                } else {
                    $dateStart = $startOfYear->startOfQuarter();
                }

                $dateEnd = $dateStart->copy()->addQuarters(1)->endOfQuarter();
            break;

            case 'next_semester':
                $startOfYear = Carbon::now()->startOfYear();

                if (Carbon::now()->subQuarters(2)->startOfQuarter()->lt($startOfYear)) {
                    $dateStart = $startOfYear->startOfQuarter()->addQuarters(2);
                } else {
                    $dateStart = $startOfYear->startOfQuarter()->addQuarters(4);
                }

                $dateEnd = $dateStart->copy()->addQuarters(1)->endOfQuarter();
            break;

            case 'year':
                $dateStart = Carbon::now()->startOfYear();
                $dateEnd = $dateStart->copy()->endOfYear();
            break;

            case 'last_year':
                $dateStart = Carbon::now()->startOfYear()->subYear(1);
                $dateEnd = $dateStart->copy()->endOfYear();
            break;

            case 'next_year':
                $dateStart = Carbon::now()->startOfYear()->addYear(1);
                $dateEnd = $dateStart->copy()->endOfYear();
            break;

            case 'last_month':
                $dateStart = Carbon::now()->startOfMonth()->subMonth(1); // startOfMonth before because it doesn't work when today is 30 or 31 and last month ends on 28 or 29
                $dateEnd = $dateStart->copy()->endOfMonth();
            break;

            case 'next_month':
                $dateStart = Carbon::now()->startOfMonth()->addMonth(1); // startOfMonth before because it doesn't work when today is 31 and next month ends on 28, 29 or 30
                $dateEnd = $dateStart->copy()->endOfMonth();
            break;

            case 'month':
            default:
                $dateStart = Carbon::now()->startOfMonth();
                $dateEnd = $dateStart->copy()->endOfMonth();
            break;
        }

        return [
            'start' => $dateStart,
            'end' => $dateEnd
        ];
    }

    /**
     * Get a CSS class to use for a board
     *
     * @param integer $index
     * @return string
     */
    protected function getClass($index)
    {
        $classes = [
            'primary',
            'light-green',
            'orange',
            'indigo',
            'green',
            'red',
            'purple',
            'teal'
        ];

        $class = $classes[$index] ?? 'primary';

        return $class;
    }
}
