<?php

namespace Jawabapp\Community\Http\Resources\Api;

use Illuminate\Http\Resources\Json\Resource;

class AccountResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->setAppends([
            'account_is_subscribed',
            'account_is_followed',
            'account_is_blocked',
            'account_is_liked',
        ]);
    }
}
