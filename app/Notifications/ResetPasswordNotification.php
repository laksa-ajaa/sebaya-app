<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * The source of the request (e.g., 'mobile').
     *
     * @var string|null
     */
    public $source;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token, $source = null)
    {
        $this->token = $token;
        $this->source = $source;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $params = [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ];

        if ($this->source) {
            $params['source'] = $this->source;
        }

        $url = url(route('password.reset', $params, false));

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi - Sebaya')
            ->line('Anda menerima email ini karena kami menerima permintaan atur ulang kata sandi untuk akun Anda.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line('Tautan atur ulang kata sandi ini akan kedaluwarsa dalam 60 menit.')
            ->line('Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.')
            ->salutation('Salam, Tim Sebaya');
    }
}
