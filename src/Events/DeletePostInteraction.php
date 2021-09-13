<?php

namespace Jawabapp\Community\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DeletePostInteraction
{
    use SerializesModels;

    public $interaction;
    public $deep_link;
    public $post_id;
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
        if (!empty($data['deep_link'])) {
            $this->deep_link = $data['deep_link'];
        }
        if (!empty($data['post_id'])) {
            $this->post_id = $data['post_id'];
        }
        if (!empty($data['sender_id'])) {
            $this->sender_id = $data['sender_id'];
        }
    }
}
