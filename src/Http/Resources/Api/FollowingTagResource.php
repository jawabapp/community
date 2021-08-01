<?php

namespace JawabApp\Community\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class FollowingTagResource extends JsonResource
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
            'account_id' => $this->account_id,
            'tag_id' => $this->tag_id,
            'created_by' => $this->created_by,
            'tag' => TagResource::make($this->whenLoaded('tag')),

            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
