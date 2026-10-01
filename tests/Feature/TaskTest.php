<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/tasks')->assertRedirect('/login');
    }

    public function test_user_sees_only_their_own_tasks(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'My task']);
        Task::factory()->for($other)->create(['title' => 'Other task']);

        $this->actingAs($user)->getJson('/tasks')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'My task');
    }

    public function test_tasks_are_paginated_by_twenty(): void
    {
        $user = User::factory()->create();
        Task::factory(25)->for($user)->create();

        $this->actingAs($user)->getJson('/tasks')
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 25);
    }

    public function test_tasks_can_be_searched(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['title' => 'Buy milk']);
        Task::factory()->for($user)->create(['title' => 'Walk the dog']);

        $this->actingAs($user)->getJson('/tasks?search=milk')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Buy milk');
    }

    public function test_user_can_create_a_task(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/tasks', ['title' => 'New task', 'description' => 'Details'])
            ->assertRedirect('/tasks');

        $this->assertDatabaseHas('tasks', ['user_id' => $user->id, 'title' => 'New task']);
    }

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/tasks', ['title' => ''])
            ->assertSessionHasErrors('title');
    }

    public function test_user_can_update_their_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user)
            ->put("/tasks/{$task->id}", ['title' => 'Updated'])
            ->assertRedirect('/tasks');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated']);
    }

    public function test_user_can_toggle_task_completion(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['completed_at' => null]);

        $this->actingAs($user)->patchJson("/tasks/{$task->id}/toggle")
            ->assertOk()
            ->assertJson(['completed' => true]);

        $this->assertNotNull($task->fresh()->completed_at);

        $this->actingAs($user)->patchJson("/tasks/{$task->id}/toggle")
            ->assertJson(['completed' => false]);

        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_user_can_delete_their_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user)->delete("/tasks/{$task->id}")->assertRedirect('/tasks');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_access_another_users_task(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $task = Task::factory()->for($owner)->create(['title' => 'Private']);

        $this->actingAs($user)->get("/tasks/{$task->id}/edit")->assertForbidden();
        $this->actingAs($user)->put("/tasks/{$task->id}", ['title' => 'Hacked'])->assertForbidden();
        $this->actingAs($user)->patch("/tasks/{$task->id}/toggle")->assertForbidden();
        $this->actingAs($user)->delete("/tasks/{$task->id}")->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Private']);
    }
}
