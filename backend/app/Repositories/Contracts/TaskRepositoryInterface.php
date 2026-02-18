<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function findByUser(int $userId, ?string $date = null, ?string $search = null): Collection;
    public function getTaskDates(int $userId): array;
    public function findById(int $id): ?Task;
    public function create(int $userId, array $data): Task;
    public function update(Task $task, array $data): Task;
    public function delete(Task $task): bool;
    public function reorder(int $userId, array $items): void;
    public function getMaxSortOrder(int $userId, string $date): int;
}
