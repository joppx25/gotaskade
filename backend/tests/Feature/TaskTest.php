<?php

use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

// ---------------------------------------------------------------------------
// INDEX
// ---------------------------------------------------------------------------
describe('GET /api/tasks', function () {
    it('returns tasks for the authenticated user', function () {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        Task::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('returns tasks wrapped in a data key', function () {
        Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'description', 'is_completed', 'task_date', 'sort_order', 'created_at', 'updated_at'],
                ],
            ]);
    });

    it('filters tasks by date', function () {
        Task::factory()->forDate('2026-02-18')->create(['user_id' => $this->user->id]);
        Task::factory()->forDate('2026-02-17')->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/tasks?date=2026-02-18');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.task_date', '2026-02-18');
    });

    it('filters tasks by search keyword', function () {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'description' => 'Fix login page bug',
        ]);
        Task::factory()->create([
            'user_id' => $this->user->id,
            'description' => 'Update dashboard layout',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/tasks?search=login');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', 'Fix login page bug');
    });

    it('returns empty data array when no tasks exist', function () {
        $response = $this->actingAs($this->user)->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertExactJson(['data' => []]);
    });

    it('does not return other users tasks', function () {
        Task::factory()->count(3)->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('orders tasks by sort_order', function () {
        Task::factory()->create(['user_id' => $this->user->id, 'sort_order' => 2, 'description' => 'Second']);
        Task::factory()->create(['user_id' => $this->user->id, 'sort_order' => 0, 'description' => 'First']);
        Task::factory()->create(['user_id' => $this->user->id, 'sort_order' => 1, 'description' => 'Middle']);

        $response = $this->actingAs($this->user)->getJson('/api/tasks');

        $response->assertOk();
        $descriptions = collect($response->json('data'))->pluck('description')->toArray();
        expect($descriptions)->toBe(['First', 'Middle', 'Second']);
    });

    it('rejects unauthenticated request', function () {
        $this->getJson('/api/tasks')->assertUnauthorized();
    });
});

// ---------------------------------------------------------------------------
// STORE
// ---------------------------------------------------------------------------
describe('POST /api/tasks', function () {
    it('creates a task successfully', function () {
        $response = $this->actingAs($this->user)->postJson('/api/tasks', [
            'description' => 'New task',
            'task_date' => '2026-02-18',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.description', 'New task')
            ->assertJsonPath('data.task_date', '2026-02-18')
            ->assertJsonPath('data.is_completed', false);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'description' => 'New task',
        ]);
    });

    it('returns task resource in standard data wrapper', function () {
        $response = $this->actingAs($this->user)->postJson('/api/tasks', [
            'description' => 'Test task',
            'task_date' => '2026-02-18',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'description', 'is_completed', 'task_date', 'sort_order', 'created_at', 'updated_at'],
            ]);
    });

    it('auto-increments sort_order for the same date', function () {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'task_date' => '2026-02-18',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/tasks', [
            'description' => 'Second task',
            'task_date' => '2026-02-18',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.sort_order', 1);
    });

    it('validates required description', function () {
        $response = $this->actingAs($this->user)->postJson('/api/tasks', [
            'task_date' => '2026-02-18',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('description');
    });

    it('validates required task_date', function () {
        $response = $this->actingAs($this->user)->postJson('/api/tasks', [
            'description' => 'Missing date',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('task_date');
    });

    it('validates task_date format', function () {
        $response = $this->actingAs($this->user)->postJson('/api/tasks', [
            'description' => 'Bad date',
            'task_date' => '18-02-2026',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('task_date');
    });

    it('validates description max length', function () {
        $response = $this->actingAs($this->user)->postJson('/api/tasks', [
            'description' => str_repeat('a', 1001),
            'task_date' => '2026-02-18',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('description');
    });

    it('rejects unauthenticated request', function () {
        $this->postJson('/api/tasks', [
            'description' => 'Sneaky task',
            'task_date' => '2026-02-18',
        ])->assertUnauthorized();
    });
});

// ---------------------------------------------------------------------------
// SHOW
// ---------------------------------------------------------------------------
describe('GET /api/tasks/{task}', function () {
    it('returns a single task', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'description', 'is_completed', 'task_date', 'sort_order']])
            ->assertJsonPath('data.id', $task->id);
    });

    it('forbids viewing another users task', function () {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->getJson("/api/tasks/{$task->id}");

        $response->assertForbidden();
    });

    it('returns 404 for non-existent task', function () {
        $response = $this->actingAs($this->user)->getJson('/api/tasks/99999');

        $response->assertNotFound();
    });

    it('rejects unauthenticated request', function () {
        $task = Task::factory()->create();

        $this->getJson("/api/tasks/{$task->id}")->assertUnauthorized();
    });
});

// ---------------------------------------------------------------------------
// UPDATE
// ---------------------------------------------------------------------------
describe('PATCH /api/tasks/{task}', function () {
    it('updates task description', function () {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'description' => 'Original',
        ]);

        $response = $this->actingAs($this->user)->patchJson("/api/tasks/{$task->id}", [
            'description' => 'Updated description',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.description', 'Updated description');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'description' => 'Updated description',
        ]);
    });

    it('toggles task completion', function () {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'is_completed' => false,
        ]);

        $response = $this->actingAs($this->user)->patchJson("/api/tasks/{$task->id}", [
            'is_completed' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.is_completed', true);
    });

    it('returns task resource in standard data wrapper', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->patchJson("/api/tasks/{$task->id}", [
            'description' => 'Changed',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'description', 'is_completed', 'task_date', 'sort_order', 'created_at', 'updated_at'],
            ]);
    });

    it('forbids updating another users task', function () {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->patchJson("/api/tasks/{$task->id}", [
            'description' => 'Hacked',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
            'description' => 'Hacked',
        ]);
    });

    it('validates description max length on update', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->patchJson("/api/tasks/{$task->id}", [
            'description' => str_repeat('a', 1001),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('description');
    });

    it('rejects unauthenticated request', function () {
        $task = Task::factory()->create();

        $this->patchJson("/api/tasks/{$task->id}", [
            'description' => 'Nope',
        ])->assertUnauthorized();
    });
});

// ---------------------------------------------------------------------------
// DELETE
// ---------------------------------------------------------------------------
describe('DELETE /api/tasks/{task}', function () {
    it('soft-deletes a task', function () {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Task deleted.');

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    });

    it('forbids deleting another users task', function () {
        $task = Task::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/tasks/{$task->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
    });

    it('returns 404 for non-existent task', function () {
        $response = $this->actingAs($this->user)->deleteJson('/api/tasks/99999');

        $response->assertNotFound();
    });

    it('rejects unauthenticated request', function () {
        $task = Task::factory()->create();

        $this->deleteJson("/api/tasks/{$task->id}")->assertUnauthorized();
    });
});

// ---------------------------------------------------------------------------
// REORDER
// ---------------------------------------------------------------------------
describe('POST /api/tasks/reorder', function () {
    it('reorders tasks for the authenticated user', function () {
        $taskA = Task::factory()->create(['user_id' => $this->user->id, 'sort_order' => 0]);
        $taskB = Task::factory()->create(['user_id' => $this->user->id, 'sort_order' => 1]);
        $taskC = Task::factory()->create(['user_id' => $this->user->id, 'sort_order' => 2]);

        $response = $this->actingAs($this->user)->postJson('/api/tasks/reorder', [
            'items' => [
                ['id' => $taskC->id, 'sort_order' => 0],
                ['id' => $taskA->id, 'sort_order' => 1],
                ['id' => $taskB->id, 'sort_order' => 2],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Tasks reordered.');

        expect($taskC->fresh()->sort_order)->toBe(0);
        expect($taskA->fresh()->sort_order)->toBe(1);
        expect($taskB->fresh()->sort_order)->toBe(2);
    });

    it('validates items array is required', function () {
        $response = $this->actingAs($this->user)->postJson('/api/tasks/reorder', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('items');
    });

    it('validates each item has id and sort_order', function () {
        $response = $this->actingAs($this->user)->postJson('/api/tasks/reorder', [
            'items' => [
                ['id' => null, 'sort_order' => 0],
            ],
        ]);

        $response->assertUnprocessable();
    });

    it('validates task ids exist', function () {
        $response = $this->actingAs($this->user)->postJson('/api/tasks/reorder', [
            'items' => [
                ['id' => 99999, 'sort_order' => 0],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('items.0.id');
    });

    it('rejects unauthenticated request', function () {
        $this->postJson('/api/tasks/reorder', [
            'items' => [],
        ])->assertUnauthorized();
    });
});
