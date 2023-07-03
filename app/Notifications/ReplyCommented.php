<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Kutia\Larafirebase\Messages\FirebaseMessage;

class ReplyCommented extends Notification
{
    use Queueable;
    public $owner_comment;
    public $user_replied;
    public $comment;
    public $reply;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($owner_comment, $user_replied, $comment, $reply)
    {
        $this->owner_comment = $owner_comment;
        $this->user_replied = $user_replied;
        $this->comment = $comment;
        $this->reply = $reply;
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
            'title' => "Comment replied by {$this->user_replied->username}",
            'type' => 'replies',
            'owner_comment' => [
                'id' => $this->owner_comment->id,
                'username' => $this->owner_comment->username,
                'profile_image' => $this->owner_comment->profile_image ? Storage::disk('public')->url($this->owner_comment->profile_image) : url('/')."/images/ahjili.png",
            ] ,

            'user_replied' => [
                'id' => $this->user_replied->id,
                'username' => $this->user_replied->username,
                'profile_image' => $this->user_replied->profile_image ? Storage::disk('public')->url($this->user_replied->profile_image) : url('/')."/images/ahjili.png",
            ] ,
            'comment' => $this->comment,
            'reply' => $this->reply,
        ];
    }

        /**
     * Get the firebase representation of the notification.
     */
    public function toFirebase($notifiable)
    {
        return (new FirebaseMessage())
            ->withTitle("Comment replied by {$this->user_replied->username}")
            ->withBody($this->reply)
            ->withSound('default')
            ->withPriority('high')
            ->asMessage($this->owner_comment->fcmtoken); // OR ->asMessage($deviceTokens);
    }
}
