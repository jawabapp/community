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

class AccountLikeCreate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deep_link;
    public $user_id;
    public $reciver_user_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {

        Log::info([
            'AccountLikeCreate' => $data
        ]);
        if (!empty($data['deep_link'])) {
            $this->deep_link = $data['deep_link'];
        }

        if (!empty($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }

        if (!empty($data['reciver_user_id'])) {
            $this->reciver_user_id = $data['reciver_user_id'];
        }
    }
}
