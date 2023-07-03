<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Kutia\Larafirebase\Messages\FirebaseMessage;

class PostLiked extends Notification implements ShouldQueue
{
    use Queueable;
    public $owner_post;
    public $post;
    public $user;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($owner_post, $user, $post)
    {
        $this->owner_post = $owner_post;
        $this->user = $user;
        $this->post = $post;
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
            'title' => "Post liked by {$this->user->username}",
            'type' => 'post_liked',
            'owner_post' => [
                'id' => $this->owner_post->id,
                'username' => $this->owner_post->username,
                'profile_image' => $this->owner_post->profile_image ? Storage::disk('public')->url($this->owner_post->profile_image) : url('/')."/images/ahjili.png",
            ] ,
            'user' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'profile_image' => $this->user->profile_image ? Storage::disk('public')->url($this->user->profile_image) : url('/')."/images/ahjili.png",
            ] ,
            'post' => $this->post
        ];
    }


        /**
     * Get the firebase representation of the notification.
     */
    public function toFirebase($notifiable)
    {
        return (new FirebaseMessage())
            ->withTitle("Post liked by {$this->user->username}")
            ->withBody($this->post)
            ->withSound('default')
            ->withPriority('high')
            ->asMessage($this->user->fcmtoken); // OR ->asMessage($deviceTokens);
    }
}
