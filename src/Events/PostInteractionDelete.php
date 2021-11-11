<?php

namespace Jawabapp\Community\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PostInteractionDelete
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $interaction;
    public $post_id;
    public $post_user_id;
    public $sender_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        if (!empty($data['interaction'])) {
            $this->interaction = $data['interaction'];
        }

        if (!empty($data['post_id'])) {
            $this->post_id = $data['post_id'];
        }

        if (!empty($data['post_user_id'])) {
            $this->post_user_id = $data['post_user_id'];
        }

        if (!empty($data['sender_id'])) {
            $this->sender_id = $data['sender_id'];
        }
    }
}
