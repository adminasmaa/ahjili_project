<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kutia\Larafirebase\Messages\FirebaseMessage;

class PostTagNotification extends Notification
{
    use Queueable;

    private $details;
    public $fcmtoken;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details, $fcmtoken)
    {
        $this->details = $details;
        $this->fcmtoken = $fcmtoken;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database','firebase'];
        //return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => $this->details['message'],
            'type'    => 'mentioned',
            'post_id' => $this->details['post_id'],
            'post_by' => $this->details['post_by'],
            'post_by_id' => $this->details['post_by_id'],
        ];
    }

        /**
     * Get the firebase representation of the notification.
     */
    public function toFirebase($notifiable)
    {
        return (new FirebaseMessage())
            ->withTitle($this->details['message'])
            ->withSound('default')
            ->withPriority('high')
            ->asMessage($this->fcmtoken);// OR ->asMessage($deviceTokens);
    }
}
