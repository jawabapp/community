<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Traits\HasDynamicRelation;

class TagFollower extends Model
{
    use HasDynamicRelation;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id', 'tag_id', 'created_by'
    ];

    /**
     * Get the user that owns the contact.
     */
    public function account()
    {
        return $this->belongsTo(CommunityFacade::getUserClass(), 'account_id');
    }

    /**
     * Get the user that owns the contact.
     */
    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }

    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub

        static::created(function ($node) {
            $node->updateFollowersCount();
        });

        static::deleted(function ($node) {
            $node->updateFollowersCount();
        });
    }

    private function updateFollowersCount()
    {
        $this->tag->update([
            'followers_count' => $this->tag->getFollowersCount(),
        ]);
    }
}
