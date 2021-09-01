<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jawabapp\Community\Services\DeepLinkBuilder;
use Jawabapp\Community\Traits\HasDynamicRelation;

class Tag extends Model
{
    use SoftDeletes, HasDynamicRelation;

    protected $fillable = [
        'hash_tag',
        'deep_link',
        'tag_group_id',
        'topic',

        'posts_count',
        'followers_count',
    ];

    protected $appends = [
        'hash_tag_string',

        'account_following',
        'is_subscribed',
    ];

    public function getHashTagStringAttribute()
    {
        return str_replace('#', '', $this->hash_tag);
    }

    public function getAccountFollowingAttribute()
    {
        return $this->isAccountFollowingBy();
    }

    public function getIsSubscribedAttribute()
    {
        $activeAccountId = config('community.user_class')::getActiveAccountId();
        if ($activeAccountId) {
            return $this->subscribedAccounts()->where('account_id', $activeAccountId)->exists();
        }
        return false;
    }

    public function getPostsCount()
    {
        return $this->posts()->count();
    }

    public function getFollowersCount()
    {
        return $this->followers()->count();
    }

    public function tagGroup()
    {
        return $this->belongsTo(TagGroup::class, 'tag_group_id');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tags', 'tag_id', 'post_id');
    }

    public function followers()
    {
        return $this->belongsToMany(config('community.user_class'), 'tag_followers', 'tag_id', 'account_id');
    }

    public function isAccountFollowingBy()
    {
        $activeAccountId = config('community.user_class')::getActiveAccountId();

        if ($activeAccountId) {
            return TagFollower::whereAccountId($activeAccountId)
                ->whereTagId($this->getKey())
                ->exists();
        }

        return false;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (self $node) {
            $node->generateDeepLink();
        });

        static::saving(function (self $node) {
            $node->setAttribute('topic', 'notifications/tags/' . $node->id);
        });
    }

    public function generateDeepLink()
    {
        $hash_tag = (str_replace('#', '', $this->hash_tag));

        $deep_link = DeepLinkBuilder::generate(
            [
                'mode' => 'hashtag',
                'hashtag' => $hash_tag,
            ],
            [
                'domain-uri-prefix' => config('community.deep_link.hashtag.url_prefix'),
                'utm-source' => config('community.deep_link.hashtag.utm_source'),
                'utm-medium' => config('community.deep_link.hashtag.utm_medium'),
                'utm-campaign' => config('community.deep_link.hashtag.utm_campaign') ?? "{$hash_tag}",
            ]
        );

        if ($deep_link) {
            $this->update([
                'deep_link' => $deep_link
            ]);
        }

        return $deep_link;
    }

    public function subscribedAccounts()
    {
        return $this->morphToMany(config('community.user_class'), 'notifiable', 'account_notifications', null, 'account_id')->withTimestamps();
    }
}
