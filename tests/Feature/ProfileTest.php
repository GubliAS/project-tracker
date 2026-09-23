<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_for_verified_users(): void
    {
        $this->signIn();

        $this->get('/profile')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Profile/Edit'));
    }

    public function test_profile_details_can_be_updated(): void
    {
        $user = $this->signIn();

        $this->patch('/profile', [
            'name' => 'Kofi Boateng',
            'email' => $user->email,
        ])->assertRedirect();

        $this->assertSame('Kofi Boateng', $user->fresh()->name);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_changing_email_clears_verification(): void
    {
        $user = $this->signIn();

        $this->patch('/profile', [
            'name' => $user->name,
            'email' => 'new-email@example.com',
        ])->assertRedirect();

        $this->assertSame('new-email@example.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_password_can_be_updated_with_the_current_password(): void
    {
        $user = $this->signIn();

        $this->put('/password', [
            'current_password' => 'password',
            'password' => 'updated-password',
            'password_confirmation' => 'updated-password',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('updated-password', $user->fresh()->password));
    }

    public function test_password_update_rejects_an_incorrect_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'updated-password',
            'password_confirmation' => 'updated-password',
        ])->assertRedirect('/profile')->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
