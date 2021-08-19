<?php

namespace Jawabapp\Community\Traits;

trait AccountTraits
{
    public function getSlugWithoutAtAttribute()
    {
        return str_replace('@', '', $this->slug);
    }

    public function getIsSubscribedAttribute()
    {
        $activeAccountId = self::getActiveAccountId();
        if ($activeAccountId) {
            return $this->subscribedAccounts()->where('account_id', $activeAccountId)->exists();
        }
        return false;
    }

    public function getAccountFollowingAttribute()
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
        if (auth('api')->check()) {

            static $defaultAccountId;

            if (is_null($defaultAccountId)) {
                $defaultAccountId = auth('api')->user()->getDefaultAccount()->id ?? false;
            }

            $activeAccount = request()->get('active_account', $defaultAccountId);

            if ($activeAccount) {
                return $activeAccount;
            }
        }
        return false;
    }

    public function getMutualFollower($accountId = null)
    {

        $activeAccountId = $accountId ?? self::getActiveAccountId();
        $followerAccountId = $this->getKey();

        if ($activeAccountId && $activeAccountId != $followerAccountId) {
            return \Jawabapp\Community\Models\AccountFollower::whereAccountId($activeAccountId)
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
            return \Jawabapp\Community\Models\AccountFollower::whereAccountId($activeAccountId)
                ->whereFollowerAccountId($this->getKey())
                ->exists();
        }

        return false;
    }

    public function isAccountBlocked()
    {
        $activeAccountId = self::getActiveAccountId();

        if ($activeAccountId) {
            return \Jawabapp\Community\Models\AccountBlock::whereAccountId($activeAccountId)
                ->whereBlockAccountId($this->getKey())
                ->exists();
        }

        return false;
    }

    public function generateDeepLink($returnOnly = false)
    {

        $deep_link = '';

        try {

            $slug = ($this->slug_without_at);

            $deep_link = \Jawabapp\Community\Models\DeepLinkBuilder::generate(new Request([
                'link' => "https://trends.jawab.app/{$slug}?mode=account&slug={$slug}",
                'analyticsUtmSource' => "jawabchat",
                'analyticsUtmMedium' => "account",
                'analyticsUtmCampaign' => "{$slug}",
            ]), 'https://account.jawab.app');

            if (!$returnOnly) {
                $this->update([
                    'deep_link' => $deep_link
                ]);
            }
        } catch (Exception $e) {
        }

        return $deep_link;
    }

    /**
     * Get the user that owns the contact.
     */
    public function user()
    {
        return $this->belongsTo(config('community.user_class'));
    }

    /**
     * Get the account blocks
     */
    public function blocks()
    {
        return $this->hasMany(\Jawabapp\Community\Models\AccountBlock::class, 'account_id');
    }

    /**
     * Get the account contacts
     */
    public function friends()
    {
        return $this->hasMany(\Jawabapp\Community\Models\AccountFriend::class, 'account_id');
    }

    /**
     * Get the account contacts
     */
    public function followers()
    {
        return $this->hasMany(\Jawabapp\Community\Models\AccountFollower::class, 'account_id');
    }

    /**
     * Get the account contacts
     */
    public function following()
    {
        return $this->hasMany(\Jawabapp\Community\Models\AccountFollower::class, 'follower_account_id');
    }

    public function followingTag()
    {
        return $this->hasMany(\Jawabapp\Community\Models\TagFollower::class, 'account_id');
    }

    /**
     * Get the account posts
     */
    public function posts()
    {
        return $this->hasMany(\Jawabapp\Community\Models\Post::class, 'account_id');
    }

    /**
     * Get the account groups
     */
    public function groups()
    {
        return $this->belongsToMany(\Jawabapp\Community\Models\AccountGroup::class, 'account_group_members', 'account_id', 'account_group_id');
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
        return $this->morphToMany(config('community.user_class'), 'notifiable', 'account_notifications', null, 'account_id')->withTimestamps();
    }

    public function subscribeAccounts()
    {
        return $this->morphedByMany(config('community.user_class'), 'notifiable', 'account_notifications', null, 'account_id')->withTimestamps();
    }

    public function subscribePosts()
    {
        return $this->morphedByMany(\Jawabapp\Community\Models\Post::class, 'notifiable', 'account_notifications', null, 'account_id')->withTimestamps();
    }

    public function subscribeTags()
    {
        return $this->morphedByMany(\Jawabapp\Community\Models\Tag::class, 'notifiable', 'account_notifications', null, 'account_id')->withTimestamps();
    }

    public function followCounts()
    {
        $this->update([
            'following_count' => $this->getFollowingCount(),
            'followers_count' => $this->getFollowersCount(),
            'mutual_follower_count' => $this->getMutualFollowerCount(),
        ]);
    }
}
