<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;
use App\Constants;
use App\Traits\TasksTrait;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;

class TasksController extends Controller
{
    use TasksTrait;

    const PERMISSION = 'crm-tasks';

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check()) {
                if (!auth()->user()->tenant->can(self::PERMISSION)) {
                    return redirect()->route(Constants::SUBSCRIPTION_REDIRECTS_TO, ['feature' => self::PERMISSION]);
                }
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Task::query()
            ->with(['assignedTo', 'linkedTo'])
            ->where('tasks.tenant_id', auth()->user()->tenant->id);

        // Get all possible owners for the tenant before applying any filters
        $allOwners = Task::where('tenant_id', auth()->user()->tenant->id)
            ->whereNotNull('assigned_to')
            ->with('assignedTo')
            ->get()
            ->pluck('assignedTo')
            ->unique('id')
            ->filter()
            ->values();

        // Handle owners filter with debug
        if ($request->has('owners')) {
            $ownerIds = array_filter(explode(',', $request->owners));
            if (!empty($ownerIds)) {
                $query->whereIn('tasks.assigned_to', $ownerIds);
                
           
            }
        }

        // Handle search
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('tasks.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('tasks.description', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('assignedTo', function ($q) use ($searchTerm) {
                        $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email_1', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('linkedTo', function ($q) use ($searchTerm) {
                        $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email_1', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // Handle filters
        $filter = $request->query('filter', 'all');
        switch ($filter) {
            case 'incomplete':
                $query->where('tasks.status', '=', 'open')
                    ->whereNull('tasks.completed_at');
                break;

            case 'overdue':
                $query->where('tasks.status', '=', 'open')
                    ->whereNull('tasks.completed_at')
                    ->where('tasks.due', '<', Carbon::now());
                break;

            case 'today':
                $query->whereBetween('tasks.due', [
                    Carbon::today()->startOfDay(),
                    Carbon::today()->endOfDay()
                ]);
                break;

            case 'tomorrow':
                $query->whereBetween('tasks.due', [
                    Carbon::tomorrow()->startOfDay(),
                    Carbon::tomorrow()->endOfDay()
                ]);
                break;

            case 'week':
                $query->whereBetween('tasks.due', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;

            case 'custom':
                if ($request->has('start_date') && $request->has('end_date')) {
                    $query->whereBetween('tasks.due', [
                        Carbon::parse($request->start_date)->startOfDay(),
                        Carbon::parse($request->end_date)->endOfDay()
                    ]);
                }
                break;
        }

        // Handle sorting
        $sort = $request->get('sort', 'due_date|asc');
        list($sortField, $direction) = array_pad(explode('|', $sort), 2, 'asc');

        // Map frontend field names to database columns
        $sortFieldMap = [
            'title' => 'tasks.name',
            'status' => DB::raw("CASE 
                WHEN tasks.status = 'completed' THEN 3
                WHEN tasks.status = 'open' AND tasks.due < NOW() THEN 1
                WHEN tasks.status = 'open' THEN 2
                ELSE 4 END"),
            'due_date' => 'tasks.due',
            'assigned_to' => DB::raw("CONCAT(COALESCE(contacts1.first_name, ''), ' ', COALESCE(contacts1.last_name, ''))"),
            'linked_to' => DB::raw("CONCAT(COALESCE(contacts2.first_name, ''), ' ', COALESCE(contacts2.last_name, ''))")
        ];

        // Add joins for sorting by contact names
        $query->leftJoin('contacts as contacts1', 'tasks.assigned_to', '=', 'contacts1.id')
            ->leftJoin('contacts as contacts2', 'tasks.linked_to', '=', 'contacts2.id')
            ->select('tasks.*');

        // Use mapped field name or default to 'due'
        if (isset($sortFieldMap[$sortField])) {
            $query->orderBy($sortFieldMap[$sortField], $direction);
        } else {
            $query->orderBy('tasks.due', $direction);
        }

        // Handle CSV export
        if ($request->query('export') === 'csv') {
            $tasks = $query->get();
            
            // Create CSV content
            $output = fopen('php://temp', 'r+');
            
            // Add UTF-8 BOM for Excel compatibility
            fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Add headers
            fputcsv($output, [
                '#',
                'Title',
                'Status',
                'Due Date',
                'Assigned To',
                'Linked To',
                'Description'
            ]);
            
            // Add data
            foreach ($tasks as $index => $task) {
                fputcsv($output, [
                    $index + 1, // Row number starting from 1
                    $task->name,
                    $task->status,
                    $task->due ? Carbon::parse($task->due)->format('Y-m-d H:i:s') : '',
                    $task->assignedTo ? ($task->assignedTo->first_name . ' ' . $task->assignedTo->last_name) : '',
                    $task->linkedTo ? ($task->linkedTo->first_name . ' ' . $task->linkedTo->last_name) : '',
                    $task->description
                ]);
            }
            
            // Reset pointer to beginning of file
            rewind($output);
            
            // Get content
            $content = stream_get_contents($output);
            fclose($output);
            
            // Set headers for download
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="tasks-' . date('Y-m-d') . '.csv"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];
            
            return response($content, 200, $headers);
        }

        $tasks = $query->paginate(10);

        if ($request->wantsJson()) {
            // Add encrypted_id to each task
            $tasks->getCollection()->transform(function ($task) {
                $task->encrypted_id = \Crypt::encrypt($task->id);
                return $task;
            });

            return response()->json([
                'tasks' => $tasks
            ]);
        }

        return view('tasks.index', compact('tasks', 'allOwners'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $task = mapModel(new Task(), $request->all());
        $due = Carbon::parse(array_get($request, 'due'));
        array_set($task, 'due', $due->endOfDay());

        if (array_get($request, 'hour') != '---') {
            $time = implode(':', [array_get($request, 'hour'), array_get($request, 'minutes')]) . ' ' . array_get($request, 'when');
            $datetime = implode(' ', [array_get($request, 'due'), $time]);
            $due = setUTCDateTime($datetime);
            array_set($task, 'due', $due->toDateTimeString());
            array_set($task, 'show_time', true);
        }

        if (array_get($request, 'email_assignee_due') == 1) {
            $emailDue = null;

            switch (array_get($request, 'due_period')) {
                case 'day':
                    $emailDue = Carbon::parse(array_get($request, 'due'))->subDays(array_get($request, 'due_number'))->format('Y-m-d');
                    break;
                case 'week':
                    $emailDue = Carbon::parse(array_get($request, 'due'))->subWeeks(array_get($request, 'due_number'))->format('Y-m-d');
                    break;
                case 'month':
                    $emailDue = Carbon::parse(array_get($request, 'due'))->subMonths(array_get($request, 'due_number'))->format('Y-m-d');
                    break;
            }

            array_set($task, 'email_due', $emailDue);
        }

        if (auth()->user()->tenant->tasks()->save($task)) {
            if (array_get($request, 'email_assignee') == 1) {
                $this->emailAssignee($task);
            }

            return redirect()->back()->with('message', 'Task added successfully');
        }
        abort(500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
        if (array_get($request, 'due_date')) {
            $due = Carbon::parse(array_get($request, 'due_date'));
            array_set($task, 'due', $due->endOfDay());
        }

        if (array_has($request, 'complete') && array_get($request, 'complete') == 1) {
            array_set($task, 'completed_at', Carbon::now());
            array_set($task, 'status', 'completed');
            $task->update();
            return redirect()->back()->with('message', 'Task completed successfully');
        }

        mapModel($task, $request->all());
        array_set($task, 'show_time', false);
        if (array_get($request, 'hour') != '---') {
            $time = implode(':', [array_get($request, 'hour'), array_get($request, 'minutes')]) . ' ' . array_get($request, 'when');
            $datetime = implode(' ', [array_get($request, 'due_date'), $time]);
            $due = setUTCDateTime($datetime);
            array_set($task, 'due', $due->toDateTimeString());
            array_set($task, 'show_time', true);
        }


        switch (array_get($request, 'due_period')) {
            case 'day':
                $emailDue = Carbon::parse(array_get($request, 'due_date'))->subDays(array_get($request, 'due_number'))->format('Y-m-d');
                break;
            case 'week':
                $emailDue = Carbon::parse(array_get($request, 'due_date'))->subWeeks(array_get($request, 'due_number'))->format('Y-m-d');
                break;
            case 'month':
                $emailDue = Carbon::parse(array_get($request, 'due_date'))->subMonths(array_get($request, 'due_number'))->format('Y-m-d');
                break;
        }

        array_set($task, 'email_due', $emailDue);


        if (auth()->user()->tenant->tasks()->save($task)) {
            return redirect()->back()->with('message', 'Task updated successfully');
        }
        abort(500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Task::destroy($id);
        return redirect()->back()->with('message', 'Task deleted successfully');
    }

    /**
     * Generate CSV export of tasks
     *
     * @param \Illuminate\Database\Eloquent\Collection $tasks
     * @return \Illuminate\Http\Response
     */
    private function exportToCsv($tasks)
    {
        // Create CSV content
        $output = fopen('php://temp', 'r+');
        
        // Add UTF-8 BOM for Excel compatibility
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Add headers
        fputcsv($output, [
            '#',
            'Title',
            'Status',
            'Due Date',
            'Assigned To',
            'Linked To',
            'Description'
        ]);
        
        // Add data
        foreach ($tasks as $index => $task) {
            fputcsv($output, [
                $index + 1, // Row number starting from 1
                $task->name,
                $task->status,
                $task->due ? Carbon::parse($task->due)->format('Y-m-d H:i:s') : '',
                $task->assignedTo ? ($task->assignedTo->first_name . ' ' . $task->assignedTo->last_name) : '',
                $task->linkedTo ? ($task->linkedTo->first_name . ' ' . $task->linkedTo->last_name) : '',
                $task->description
            ]);
        }
        
        // Reset pointer to beginning of file
        rewind($output);
        
        // Get content
        $content = stream_get_contents($output);
        fclose($output);
        
        // Set headers for download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tasks-' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        return response($content, 200, $headers);
    }
}
