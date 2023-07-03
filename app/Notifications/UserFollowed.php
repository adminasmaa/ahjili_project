<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Kutia\Larafirebase\Messages\FirebaseMessage;

class UserFollowed extends Notification implements ShouldQueue
{
    use Queueable;
    public $user;
    public $fcmtoken;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $fcmtoken)
    {
        $this->user = $user;
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
        return [
            'title' => "followed by {$this->user->username}",
            'type' => 'followed',
            'user' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'profile_image' => $this->user->profile_image ? Storage::disk('public')->url($this->user->profile_image) : url('/')."/images/ahjili.png",
            ] ,
        ];
    }

     /**
     * Get the firebase representation of the notification.
     */
    public function toFirebase($notifiable)
    {
        return (new FirebaseMessage())
            ->withTitle("followed by {$this->user->username}")
            ->withSound('default')
            ->withPriority('high')
            ->asNotification($this->fcmtoken);// OR ->asMessage($deviceTokens);
    }
}
