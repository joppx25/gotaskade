<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function __construct(private readonly Task $model) {}

    public function findByUser(int $userId, ?string $date = null, ?string $search = null): Collection
    {
        $query = $this->model->where('user_id', $userId);

        if ($date) {
            $query->whereDate('task_date', $date);
        }

        if ($search) {
            $query->whereRaw('LOWER(description) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        return $query->orderBy('sort_order')->get();
    }

    public function getTaskDates(int $userId): array
    {
        return $this->model
            ->where('user_id', $userId)
            ->select('task_date')
            ->distinct()
            ->orderByDesc('task_date')
            ->pluck('task_date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();
    }

    public function findById(int $id): ?Task
    {
        return $this->model->find($id);
    }

    public function create(int $userId, array $data): Task
    {
        return $this->model->create([
            'user_id' => $userId,
            'description' => $data['description'],
            'task_date' => $data['task_date'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_completed' => $data['is_completed'] ?? false,
        ]);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function reorder(int $userId, array $items): void
    {
        foreach ($items as $item) {
            $this->model
                ->where('id', $item['id'])
                ->where('user_id', $userId)
                ->update(['sort_order' => $item['sort_order']]);
        }
    }

    public function getMaxSortOrder(int $userId, string $date): int
    {
        return (int) $this->model
            ->where('user_id', $userId)
            ->whereDate('task_date', $date)
            ->max('sort_order');
    }
}
