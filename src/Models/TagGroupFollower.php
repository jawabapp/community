<?php

namespace Jawabapp\Community\Models;

use Jawabapp\Community\Services\Caching;
use Illuminate\Database\Eloquent\Model;

class TagGroupFollower extends Model
{
    protected $fillable = [
        'tag_group_id',
        'account_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function (self $node) {
            $node->resetCache();
        });

        static::deleted(function (self $node) {
            $node->resetCache();
        });
    }

    public function resetCache()
    {
        Caching::deleteCacheByTags("posts-{$this->account_id}");
    }
}
