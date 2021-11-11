<?php

namespace Jawabapp\Community\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class PostCreate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deep_link;
    public $post_id;
    public $post_user_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        if (!empty($data['deep_link'])) {
            $this->deep_link = $data['deep_link'];
        }

        if (!empty($data['post_id'])) {
            $this->post_id = $data['post_id'];
        }

        if (!empty($data['post_user_id'])) {
            $this->post_user_id = $data['post_user_id'];
        }

        Log::info([
            'deep_link' => $this->deep_link,
            'post_id' => $this->post_id,
            'post_user_id' => $this->post_user_id,
        ]);
    }
}
