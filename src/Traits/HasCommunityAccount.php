<?php

namespace Jawabapp\Community\Traits;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Models;
use Jawabapp\Community\Services\DeepLinkBuilder;
use Jawabapp\Community\Services\Slug;

trait HasCommunityAccount
{
    protected $dynamic_fillable = [
        'slug',
        'deep_link',
        'extra_info',
        'topic',
        'post_count',
        'followers_count',
        'following_count',
        'mutual_follower_count',
        'likes_count',
    ];

    public function getFillable()
    {
        $this->fillable = array_unique(array_merge($this->dynamic_fillable, $this->fillable ?? []));

        return parent::getFillable();
    }

    public function getSlugWithoutAtAttribute()
    {
        return str_replace('@', '', $this->slug);
    }

    public function getAccountIsSubscribedAttribute()
    {
        $activeAccountId = self::getActiveAccountId();
        if ($activeAccountId) {
            return $this->subscribedAccounts()->where('account_id', $activeAccountId)->exists();
        }
        return false;
    }

    public function getAccountIsFollowedAttribute()
    {
        return $this->isAccountFollowingBy();
    }

    public function getAccountIsBlockedAttribute()
    {
        return $this->isAccountBlocked();
    }

    public function getPostCount()
    {
        return $this->posts()->whereNull('related_post_id')->whereNull('parent_post_id')->count();
    }

    public function getFollowersCount()
    {
        return $this->following()->count();
    }

    public function getLikesCount()
    {
        return $this->likes()->count();
    }

    public function getFollowingCount()
    {
        return $this->followers()->count();
    }

    public function getMutualFollowerCount()
    {
        return $this->getMutualFollower()->count();
    }

    public static function getActiveAccountId()
    {
        static $activeAccountId;

        if (is_null($activeAccountId)) {
            $activeAccountId = CommunityFacade::getLoggedInUser()->id ?? false;
        }

        $activeAccountId = request()->get('active_account', $activeAccountId);

        if ($activeAccountId) {
            return $activeAccountId;
        }

        return false;
    }

    public function getMutualFollower($accountId = null)
    {

        $activeAccountId = $accountId ?? self::getActiveAccountId();
        $followerAccountId = $this->getKey();

        if ($activeAccountId && $activeAccountId != $followerAccountId) {
            return Models\AccountFollower::whereAccountId($activeAccountId)
                ->whereIn('follower_account_id', function ($query) use ($followerAccountId) {
                    $query->select('follower_account_id')
                        ->from('account_followers')
                        ->where('account_id', $followerAccountId);
                });
        }

        return collect();
    }

    public function isAccountFollowingBy()
    {
        $activeAccountId = self::getActiveAccountId();

        if ($activeAccountId) {
            return Models\AccountFollower::whereAccountId($activeAccountId)
                ->whereFollowerAccountId($this->getKey())
                ->exists();
        }

        return false;
    }

    public function isAccountBlocked()
    {
        $activeAccountId = self::getActiveAccountId();

        if ($activeAccountId) {
            return Models\AccountBlock::whereAccountId($activeAccountId)
                ->whereBlockAccountId($this->getKey())
                ->exists();
        }

        return false;
    }

    public function generateDeepLink($returnOnly = false)
    {
        if(!config('community.deep_link.account')) {
            return null;
        }

        $slug = ($this->slug_without_at);

        $deep_link = DeepLinkBuilder::generate(
            [
                'mode' => 'account',
                'slug' => $slug,
            ],
            [
                'domain-uri-prefix' => config('community.deep_link.account.url_prefix'),
                'utm-source' => config('community.deep_link.account.utm_source'),
                'utm-medium' => config('community.deep_link.account.utm_medium'),
                'utm-campaign' => config('community.deep_link.account.utm_campaign') ?? "{$slug}",
            ]
        );

        if (!$returnOnly && $deep_link) {
            $this->update([
                'deep_link' => $deep_link
            ]);
        }


        return $deep_link;
    }

    /**
     * Get the user that owns the contact.
     */
    public function user()
    {
        return $this->belongsTo(CommunityFacade::getUserClass());
    }

    /**
     * Get the account blocks
     */
    public function blocks()
    {
        return $this->hasMany(Models\AccountBlock::class, 'account_id');
    }

    /**
     * Get the account contacts
     */
    public function friends()
    {
        return $this->hasMany(Models\AccountFriend::class, 'account_id');
    }

    /**
     * Get the account contacts
     */
    public function followers()
    {
        return $this->hasMany(Models\AccountFollower::class, 'account_id');
    }

    /**
     * Get the account contacts
     */
    public function likes()
    {
        return $this->hasMany(Models\AccountLike::class, 'account_id');
    }

    /**
     * Get the account contacts
     */
    public function following()
    {
        return $this->hasMany(Models\AccountFollower::class, 'follower_account_id');
    }

    public function followingTag()
    {
        return $this->hasMany(Models\TagFollower::class, 'account_id');
    }

    /**
     * Get the account posts
     */
    public function posts()
    {
        return $this->hasMany(Models\Post::class, 'account_id');
    }

    /**
     * Get the account groups
     */
    public function groups()
    {
        return $this->belongsToMany(Models\AccountGroup::class, 'account_group_members', 'account_id', 'account_group_id');
    }

    /**
     * @param $groupId
     * @return AccountGroup|null
     */
    public function getGroup($groupId)
    {
        static $groups;

        if (empty($groups[$groupId])) {
            $groups[$groupId] = $this->groups()->where('account_groups.id', '=', $groupId)->first();
        }

        return $groups[$groupId];
    }

    /**
     * @param $friendId
     * @return AccountFriend|null
     */
    public function getFriend($friendId)
    {
        static $friends;

        if (empty($friends[$friendId])) {
            $friends[$friendId] = $this->friends()->where('account_friends.friend_account_id', '=', $friendId)->first();
        }

        return $friends[$friendId];
    }

    /**
     * @param $followerId
     * @return AccountFollower|null
     */
    public function getFollower($followerId)
    {
        static $followers;

        if (empty($followers[$followerId])) {
            $followers[$followerId] = $this->followers()->where('account_followers.follower_account_id', '=', $followerId)->first();
        }

        return $followers[$followerId];
    }

    public function subscribedAccounts()
    {
        return $this->morphToMany(CommunityFacade::getUserClass(), 'notifiable', 'account_notifications', 'notifiable_id', 'account_id')->withTimestamps();
    }

    public function subscribeAccounts()
    {
        return $this->morphedByMany(CommunityFacade::getUserClass(), 'notifiable', 'account_notifications', 'notifiable_id', 'account_id')->withTimestamps();
    }

    public function subscribePosts()
    {
        return $this->morphedByMany(
            Models\Post::class,
            'notifiable',
            'account_notifications',
            'notifiable_id',
            'account_id'
        )->withTimestamps();
    }

    public function subscribeTags()
    {
        return $this->morphedByMany(Models\Tag::class, 'notifiable', 'account_notifications', 'notifiable_id', 'account_id')->withTimestamps();
    }

    public function followCounts()
    {
        $this->update([
            'following_count' => $this->getFollowingCount(),
            'followers_count' => $this->getFollowersCount(),
            'mutual_follower_count' => $this->getMutualFollowerCount(),
        ]);
    }

    public function likeCounts()
    {
        $this->update([
            'likes_count' => $this->getLikesCount(),
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $node) {
            $node->setCommunityAttributes(true);
        });

        static::updating(function (self $node) {
            $node->setCommunityAttributes(false, $node->getKey());
        });
    }

    public function setCommunityAttributes($changeSlug = false, $id = 0) {

        if($id) {
            $this->setAttribute('topic', 'notifications/accounts/' . $id);
        } else {
            $this->setAttribute('topic', 'notifications/accounts/');
        }

        $nicknames = [];

        foreach (config('community.slug_fields', []) as $slug_field) {

            if (!empty($this->getAttribute($slug_field)) && $this->isDirty($slug_field)) {
                $changeSlug = true;
            }

            $nicknames[] = ucfirst(strtolower(trim($this->getAttribute($slug_field))));
        }

        if($changeSlug) {
            $nickname = trim(implode(' ', $nicknames));

            $this->setAttribute('slug', app(Slug::class)->createSlug($nickname, $id));
            $this->setAttribute('deep_link', $this->generateDeepLink(true));
        }

    }
}
