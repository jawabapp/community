<?php

namespace Jawabapp\Community\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PostDelete
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $post_id;
    public $post_user_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        if (!empty($data['post_id'])) {
            $this->post_id = $data['post_id'];
        }

        if (!empty($data['post_user_id'])) {
            $this->post_user_id = $data['post_user_id'];
        }
    }
}
