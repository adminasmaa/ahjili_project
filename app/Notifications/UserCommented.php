<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Kutia\Larafirebase\Messages\FirebaseMessage;

class UserCommented extends Notification implements ShouldQueue
{
    use Queueable;
    public $owner_post;
    public $user_commented;
    public $post;
    public $comment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($owner_post, $user_commented, $post, $comment)
    {
        $this->owner_post = $owner_post;
        $this->user_commented = $user_commented;
        $this->post = $post;
        $this->comment = $comment;
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
            'title' => "Post commented by {$this->user_commented->username}",
            'type' => 'commented',
            'owner_post' => [
                'id' => $this->owner_post->id,
                'username' => $this->owner_post->username,
                'profile_image' => $this->owner_post->profile_image ? Storage::disk('public')->url($this->owner_post->profile_image) : url('/')."/images/ahjili.png",
            ] ,
            'user_commented' => [
                'id' => $this->user_commented->id,
                'username' => $this->user_commented->username,
                'profile_image' => $this->user_commented->profile_image ? Storage::disk('public')->url($this->user_commented->profile_image) : url('/')."/images/ahjili.png",
            ] ,
            'post' => $this->post,
            'comment' => $this->comment,
            ];
    }


         /**
     * Get the firebase representation of the notification.
     */
    public function toFirebase($notifiable)
    {
        return (new FirebaseMessage())
            ->withTitle("Post commented by {$this->user_commented->username}")
            ->withBody($this->comment)
            ->withSound('default')
            ->withPriority('high')
            ->asMessage($this->owner_post->fcmtoken); // OR ->asMessage($deviceTokens);
    }
}
