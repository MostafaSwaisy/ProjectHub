@component('mail::message')
# Password Reset Request

Hello {{ $user->name }},

You have requested to reset your password. Click the button below to set a new password:

@component('mail::button', ['url' => route('password.reset', ['token' => $token, 'email' => $user->email])])
Reset Password
@endcomponent

This password reset link will expire in {{ config('auth.passwords.users.expire') }} minutes.

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}

@slot('subcopy')
If you're having trouble clicking the "Reset Password" button, copy and paste the URL below
into your web browser: [{{ route('password.reset', ['token' => $token, 'email' => $user->email]) }}]({{ route('password.reset', ['token' => $token, 'email' => $user->email]) }})
@endslot
@endcomponent
