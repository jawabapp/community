<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Jawabapp\Community\Traits\HasDynamicRelation;

class TagGroupFollower extends Model
{
    use HasDynamicRelation;

    protected $fillable = [
        'tag_group_id',
        'account_id',
    ];

    public function getConnectionName()
    {
        return config('community.database_connection');
    }

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
        //
    }
}
