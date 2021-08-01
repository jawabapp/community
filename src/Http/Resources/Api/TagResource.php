<?php

namespace JawabApp\Community\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [

            'id' => $this->id,
            'hash_tag' => $this->hash_tag,
            'hash_tag_string' => $this->hash_tag_string,
            'deep_link' => $this->deep_link,
            'tag_group_id' => $this->tag_group_id,
            'topic' => $this->topic,

            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : null,

            'posts_count' => $this->posts_count,
            'followers_count' => $this->followers_count,
            'account_following' => $this->account_following,
            'is_subscribed' => $this->is_subscribed,
        ];
    }
}
