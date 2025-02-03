<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jawabapp\Community\CommunityFacade;
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

    public function getConnectionName()
    {
        return config('community.database_connection');
    }

    public function getHashTagStringAttribute()
    {
        return str_replace('#', '', $this->hash_tag);
    }

    public function getAccountFollowingAttribute()
    {
        return $this->isAccountFollowingBy();
    }

    public function mySubscribes()
    {
        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();

        if ($activeAccountId) {
            return $this->subscribedAccounts()->whereAccountId($activeAccountId);
        }
    }

    public function getIsSubscribedAttribute()
    {
        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();
        if ($activeAccountId) {
            return $this->mySubscribes->contains('pivot_notifiable_id', $this->getKey());
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
        return $this->belongsToMany(CommunityFacade::getUserClass(), 'tag_followers', 'tag_id', 'account_id');
    }

    public function myFollowers()
    {
        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();

        if ($activeAccountId) {
            return $this->followers()->where('account_id', $activeAccountId);
        }
    }

    public function isAccountFollowingBy()
    {
        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();

        if ($activeAccountId) {
            return $this->myFollowers->contains('pivot_tag_id', $this->getKey());
        }

        return false;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $node) {
            $node->setAttribute('topic', 'notifications/tags/');
            $node->setAttribute('deep_link', $node->generateDeepLink(true));
        });

        static::updating(function (self $node) {
            if($node->getAttribute('topic') == 'notifications/tags/') {
                $node->setAttribute('topic', 'notifications/tags/' . $node->getKey());
            }
        });
    }

    public function generateDeepLink($returnOnly = false)
    {
        if(!config('community.deep_link.hashtag')) {
            return null;
        }

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

        if (!$returnOnly && $deep_link) {
            $this->update([
                'deep_link' => $deep_link
            ]);
        }

        return $deep_link;
    }

    public function subscribedAccounts()
    {
        return $this->morphToMany(CommunityFacade::getUserClass(), 'notifiable', 'account_notifications', null, 'account_id')->withTimestamps();
    }
}
