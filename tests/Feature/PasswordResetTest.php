<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_login_page_links_to_forgot_password_page(): void
    {
        $this->get(route('login'))->assertSee(route('password.request'), false);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Atur ulang password.');
    }

    public function test_active_user_receives_reset_link_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user): bool {
            $mail = $notification->toMail($user);

            return $mail->subject === 'Reset Password Akun SafeTrack'
                && str_contains($mail->actionUrl, '/reset-password/'.$notification->token);
        });
    }

    public function test_unknown_email_gets_same_message_without_email_being_sent(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'tidak-terdaftar@example.com'])
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_inactive_user_does_not_receive_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['is_active' => false]);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('success');

        Notification::assertNotSentTo($user, ResetPasswordNotification::class);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('Buat password baru.');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertRedirect(route('login'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('PasswordBaru123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_token', ['email' => $user->email]);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.update'), [
            'token' => 'token-palsu',
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->travel(61)->minutes();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_new_password_must_be_confirmed_and_at_least_eight_characters(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'pendek',
            'password_confirmation' => 'berbeda',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
