<?php

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;

beforeEach(function () {
    $this->policy = new TaskPolicy();
    $this->owner = new User(['id' => 1]);
    $this->owner->id = 1;
    $this->stranger = new User(['id' => 2]);
    $this->stranger->id = 2;
    $this->task = new Task(['user_id' => 1]);
    $this->task->user_id = 1;
});

it('allows any authenticated user to view task list', function () {
    expect($this->policy->viewAny($this->owner))->toBeTrue();
    expect($this->policy->viewAny($this->stranger))->toBeTrue();
});

it('allows any authenticated user to create tasks', function () {
    expect($this->policy->create($this->owner))->toBeTrue();
    expect($this->policy->create($this->stranger))->toBeTrue();
});

it('allows owner to view their task', function () {
    expect($this->policy->view($this->owner, $this->task))->toBeTrue();
});

it('denies stranger from viewing task', function () {
    expect($this->policy->view($this->stranger, $this->task))->toBeFalse();
});

it('allows owner to update their task', function () {
    expect($this->policy->update($this->owner, $this->task))->toBeTrue();
});

it('denies stranger from updating task', function () {
    expect($this->policy->update($this->stranger, $this->task))->toBeFalse();
});

it('allows owner to delete their task', function () {
    expect($this->policy->delete($this->owner, $this->task))->toBeTrue();
});

it('denies stranger from deleting task', function () {
    expect($this->policy->delete($this->stranger, $this->task))->toBeFalse();
});

it('allows owner to restore their task', function () {
    expect($this->policy->restore($this->owner, $this->task))->toBeTrue();
});

it('denies stranger from restoring task', function () {
    expect($this->policy->restore($this->stranger, $this->task))->toBeFalse();
});
