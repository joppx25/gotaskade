<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    /**
     * List tasks for the authenticated user.
     * Supports ?date=YYYY-MM-DD and ?search=keyword filters.
     */
    public function index(Request $request): TaskCollection
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->taskRepository->findByUser(
            userId: $request->user()->id,
            date: $request->query('date'),
            search: $request->query('search'),
        );

        return new TaskCollection($tasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:1000'],
            'task_date' => ['required', 'date_format:Y-m-d'],
        ]);

        $maxOrder = $this->taskRepository->getMaxSortOrder(
            $request->user()->id,
            $validated['task_date'],
        );

        $task = $this->taskRepository->create($request->user()->id, [
            ...$validated,
            'sort_order' => $maxOrder + 1,
        ]);

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'description' => ['sometimes', 'string', 'max:1000'],
            'is_completed' => ['sometimes', 'boolean'],
            'task_date' => ['sometimes', 'date_format:Y-m-d'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $task = $this->taskRepository->update($task, $validated);

        return new TaskResource($task);
    }

    /**
     * Remove the specified task (soft delete).
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskRepository->delete($task);

        return response()->json(['message' => 'Task deleted.']);
    }

    /**
     * Reorder tasks for a given date.
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:tasks,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $this->taskRepository->reorder($request->user()->id, $validated['items']);

        return response()->json(['message' => 'Tasks reordered.']);
    }
}
