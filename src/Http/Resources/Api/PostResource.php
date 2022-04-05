<?php

namespace Jawabapp\Community\Http\Resources\Api;

use Illuminate\Http\Resources\Json\Resource;

class PostResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = parent::toArray($request);

        unset($resource['user_interactions']);
        unset($resource['my_subscribes']);
        unset($resource['my_interactions']);
        unset($resource['tags']);

        return $resource;
    }

}
