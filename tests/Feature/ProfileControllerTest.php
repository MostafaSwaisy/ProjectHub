<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user role if it doesn't exist
        Role::firstOrCreate(['name' => 'student']);

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    private function actingAsWithToken(User $user = null)
    {
        $user = $user ?? $this->user;
        $token = $user->createToken('test-token')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    // ============ SHOW PROFILE TESTS ============

    public function test_show_profile_requires_authentication(): void
    {
        $response = $this->getJson('/api/profile');
        $response->assertStatus(401);
    }

    public function test_show_profile_returns_user_data(): void
    {
        $response = $this->actingAsWithToken()
            ->getJson('/api/profile');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'avatar_url',
                'bio',
            ],
            'preferences',
        ]);
        $response->assertJsonPath('data.id', $this->user->id);
        $response->assertJsonPath('data.email', $this->user->email);
    }

    // ============ UPDATE PROFILE TESTS ============

    public function test_update_profile_validates_name(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile', [
                'name' => '',
                'email' => 'john@example.com',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_update_profile_validates_email(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile', [
                'name' => 'John Doe',
                'email' => 'invalid-email',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_update_profile_validates_email_uniqueness(): void
    {
        // Create another user with different email
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAsWithToken()
            ->putJson('/api/profile', [
                'name' => 'John Doe',
                'email' => $otherUser->email, // Try to use another user's email
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_update_profile_succeeds(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile', [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'bio' => 'Updated bio',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'Jane Doe');
        $response->assertJsonPath('data.email', 'jane@example.com');
        $response->assertJsonPath('data.bio', 'Updated bio');

        // Verify database update
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }

    // ============ AVATAR UPLOAD TESTS ============

    public function test_upload_avatar_requires_file(): void
    {
        $response = $this->actingAsWithToken()
            ->postJson('/api/profile/avatar', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('avatar');
    }

    public function test_upload_avatar_validates_image_type(): void
    {
        $response = $this->actingAsWithToken()
            ->postJson('/api/profile/avatar', [
                'avatar' => UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('avatar');
    }

    public function test_upload_avatar_validates_file_size(): void
    {
        $response = $this->actingAsWithToken()
            ->postJson('/api/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('avatar.jpg')->size(6000), // 6MB, max is 5MB
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('avatar');
    }

    public function test_upload_avatar_succeeds(): void
    {
        Storage::fake('public');

        $response = $this->actingAsWithToken()
            ->postJson('/api/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('avatar.jpg')->size(1000),
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Avatar uploaded successfully.');

        // Verify avatar_url was set
        $this->assertNotNull($response['avatar_url']);
        $this->assertStringStartsWith($this->user->id . '_', $response['avatar_url']);
    }

    // ============ DELETE AVATAR TESTS ============

    public function test_delete_avatar_succeeds(): void
    {
        // Set avatar on user
        $this->user->update(['avatar_url' => 'test_avatar.jpg']);

        $response = $this->actingAsWithToken()
            ->deleteJson('/api/profile/avatar');

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Avatar deleted successfully.');

        // Verify database update
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'avatar_url' => null,
        ]);
    }

    // ============ UPDATE PASSWORD TESTS ============

    public function test_update_password_requires_current_password(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile/password', [
                'current_password' => '',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('current_password');
    }

    public function test_update_password_validates_wrong_current_password(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile/password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('current_password');
    }

    public function test_update_password_validates_minimum_length(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile/password', [
                'current_password' => 'password123',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_update_password_validates_confirmation(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile/password', [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_update_password_validates_different_from_current(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile/password', [
                'current_password' => 'password123',
                'password' => 'password123', // Same as current
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422);
    }

    public function test_update_password_succeeds(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile/password', [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Password changed successfully.');

        // Verify password was updated in database
        $updatedUser = $this->user->fresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $updatedUser->password));
    }

    // ============ UPDATE PREFERENCES TESTS ============

    public function test_update_preferences_validates_frequency(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile/preferences', [
                'notification_frequency' => 'invalid_frequency',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('notification_frequency');
    }

    public function test_update_preferences_succeeds(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile/preferences', [
                'notification_frequency' => 'daily',
                'notification_email' => false,
                'notification_assignments' => true,
                'notification_comments' => true,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Preferences updated successfully.');

        // Verify preferences were saved
        $this->user->refresh();
        $preferences = $this->user->preferences->pluck('value', 'key');
        $this->assertEquals(1, $preferences['notification_assignments']);
        $this->assertEquals(0, $preferences['notification_email']);
    }

    public function test_update_preferences_with_defaults(): void
    {
        $response = $this->actingAsWithToken()
            ->putJson('/api/profile/preferences', []);

        $response->assertStatus(200);
        $response->assertJsonPath('preferences.notification_frequency', 'realtime');
        $response->assertJsonPath('preferences.notification_email', true);
    }
}
