<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_users_are_listed_with_pagination_of_twenty(): void
    {
        $admin = User::factory()->create();
        User::factory(25)->create();

        $response = $this->actingAs($admin)->get('/users');

        $response->assertOk();
        $this->assertCount(20, $response->viewData('users'));
        $this->assertSame(26, $response->viewData('users')->total());
    }

    public function test_users_can_be_searched(): void
    {
        $admin = User::factory()->create();
        User::factory()->create(['name' => 'Zelda Searchable']);
        User::factory()->create(['name' => 'Someone Else']);

        $response = $this->actingAs($admin)->get('/users?search=Zelda');

        $this->assertCount(1, $response->viewData('users'));
    }

    public function test_user_can_be_created_with_hashed_password(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/users');

        $created = User::where('email', 'new@example.com')->first();

        $this->assertNotNull($created);
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_email_must_be_unique(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/users', [
            'name' => 'Duplicate',
            'email' => $admin->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_user_can_be_updated_without_changing_password(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['password' => Hash::make('original-pass')]);

        $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Renamed',
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect('/users');

        $user->refresh();

        $this->assertSame('Renamed', $user->name);
        $this->assertTrue(Hash::check('original-pass', $user->password));
    }

    public function test_user_can_be_deleted_with_their_tasks(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        Task::factory(3)->for($user)->create();

        $this->actingAs($admin)->delete("/users/{$user->id}")->assertRedirect('/users');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->delete("/users/{$admin->id}")->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
