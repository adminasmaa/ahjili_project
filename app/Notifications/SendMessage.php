<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Kutia\Larafirebase\Messages\FirebaseMessage;

class SendMessage extends Notification implements ShouldQueue
{
    use Queueable;
    public $user_sender;
    public $message;
    public $fcmtoken;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_sender, $message, $fcmtoken)
    {
        $this->user_sender = $user_sender;
        $this->message = $message;
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
        return ['firebase'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // return (new MailMessage())
        //             ->line('The introduction to the notification.')
        //             ->action('Notification Action', url('/'))
        //             ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // return [
        //     'title' => "{$this->user_sender->username} sent you a message",
        //     'type' => 'message',
        //     'body' => $this->message,
        //     'user_sender' => [
        //         'id' => $this->user_sender->id,
        //         'username' => $this->user_sender->username,
        //         'profile_image' => $this->user_sender->profile_image ? Storage::disk('public')->url($this->user_sender->profile_image) : url('/')."/images/ahjili.png",
        //     ] ,
        // ];
    }


    /**
    * Get the firebase representation of the notification.
    */
    public function toFirebase($notifiable)
    {
        return (new FirebaseMessage())
            ->withTitle("{$this->user_sender->username} sent you a message")
            ->withBody($this->message)
            ->withSound('default')
            ->withPriority('high')
            ->asMessage($this->fcmtoken);// OR ->asMessage($deviceTokens);
    }
}
