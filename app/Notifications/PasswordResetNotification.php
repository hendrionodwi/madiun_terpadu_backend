<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        $broker = (string) config('auth.defaults.passwords', 'users');
        $expirationMinutes = (int) config("auth.passwords.{$broker}.expire", 60);
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi - Madiun Terpadu')
            ->greeting('Halo, '.$notifiable->name)
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun Madiun Terpadu Anda.')
            ->action('Atur Ulang Kata Sandi', $resetUrl)
            ->line("Tautan ini berlaku selama {$expirationMinutes} menit dan hanya dapat digunakan satu kali.")
            ->line('Jika Anda tidak meminta perubahan kata sandi, abaikan email ini. Akun Anda tetap aman.')
            ->salutation('Dinas Lingkungan Hidup Kota Madiun');
    }
}
