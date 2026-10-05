<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email reset password berbahasa Indonesia dengan kredit hardcoded
 * "© {tahun} Toko Source Code" (https://id.shp.ee/gteU4cYe).
 */
class ResetPasswordNotification extends BaseResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi')
            ->greeting('Halo,')
            ->line('Anda menerima email ini karena kami menerima permintaan atur ulang kata sandi untuk akun Anda.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line("Tautan ini akan kedaluwarsa dalam {$expire} menit.")
            ->line('Jika Anda tidak meminta atur ulang kata sandi, abaikan email ini.')
            ->salutation('© '.date('Y')." Toko Source Code\nhttps://id.shp.ee/gteU4cYe");
    }
}
