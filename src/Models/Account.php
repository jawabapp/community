<?php

namespace Jawabapp\Community\Models;

// use App\Jobs\AccountAutoFollowTagJob;
use Jawabapp\Community\Services\DeepLinkBuilder;
use Jawabapp\Community\Services\Slug;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Account extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'nickname',
        'avatar',
        'status',
        'default',
        'is_private',
        'slug',
        'deep_link',
        'extra_info',
        'topic',

        'post_count',
        'followers_count',
        'following_count',
        'mutual_follower_count',
    ];

    protected $casts = [
        'avatar' => 'array',
        'extra_info' => 'array'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at', 'extra_info'
    ];

    protected $appends = [
        'slug_without_at',

        'is_subscribed',
        'account_following',
        'account_is_blocked',
    ];

    public function getSlugWithoutAtAttribute() {
        return str_replace('@', '', $this->slug);
    }

    public function getIsSubscribedAttribute() {
        $activeAccountId = self::getActiveAccountId();
        if($activeAccountId) {
            return $this->subscribedAccounts()->where('account_id', $activeAccountId)->exists();
        }
        return false;
    }

    public function getAccountFollowingAttribute() {
        return $this->isAccountFollowingBy();
    }

    public function getAccountIsBlockedAttribute() {
         return $this->isAccountBlocked();
    }

    public function getPostCount() {
        return $this->posts()->whereNull('related_post_id')->whereNull('parent_post_id')->count();
    }

    public function getFollowersCount() {
        return $this->following()->count();
    }

    public function getFollowingCount() {
        return $this->followers()->count();
    }

    public function getMutualFollowerCount() {
        return $this->getMutualFollower()->count();
    }

    public static function getActiveAccountId() {
        if(auth('api')->check()) {

            static $defaultAccountId;

            if(is_null($defaultAccountId)) {
                $defaultAccountId = auth('api')->user()->getDefaultAccount()->id ?? false;
            }

            $activeAccount = request()->get('active_account', $defaultAccountId);

            if($activeAccount) {
                return $activeAccount;
            }
        }
        return false;
    }

    public function getMutualFollower($accountId = null) {

        $activeAccountId = $accountId ?? self::getActiveAccountId();
        $followerAccountId = $this->getKey();

        if($activeAccountId && $activeAccountId != $followerAccountId) {
            return AccountFollower::whereAccountId($activeAccountId)
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

        if($activeAccountId) {
            return AccountFollower::whereAccountId($activeAccountId)
                ->whereFollowerAccountId($this->getKey())
                ->exists();
        }

        return false;
    }

    public function isAccountBlocked()
    {
        $activeAccountId = self::getActiveAccountId();

        if($activeAccountId) {
            return AccountBlock::whereAccountId($activeAccountId)
                ->whereBlockAccountId($this->getKey())
                ->exists();
        }

        return false;
    }

    protected static function boot()
    {
        parent::boot();

        // static::created(function (self $node) {
        //     AccountAutoFollowTagJob::dispatch($node);
        // });

        static::saving(function(self $node) {
            if(empty($node->getAttribute('nickname')) || $node->isDirty('nickname')) {

                if(empty($node->getAttribute('nickname'))) {
                    $nickname = Str::random(16);
                } else {
                    $nickname = $node->getAttribute('nickname');
                }

                $node->setAttribute('slug', app(Slug::class)->createSlug($nickname, $node->getKey()));
                $node->setAttribute('deep_link', $node->generateDeepLink(true));

            }
            $node->setAttribute('topic', 'notifications/accounts/' . $node->id);
        });
    }

    public function generateDeepLink($returnOnly = false) {

        $deep_link = '';

        try {

            $slug = ($this->slug_without_at);

            $deep_link = DeepLinkBuilder::generate(new Request([
                'link' => "https://trends.jawab.app/{$slug}?mode=account&slug={$slug}",
                'analyticsUtmSource' => "jawabchat",
                'analyticsUtmMedium' => "account",
                'analyticsUtmCampaign' => "{$slug}",
            ]), 'https://account.jawab.app');

            if(!$returnOnly) {
                $this->update([
                    'deep_link' => $deep_link
                ]);
            }

        } catch (Exception $e) {}

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
        return $this->hasMany(AccountBlock::class, 'account_id');
    }

    /**
     * Get the account contacts
     */
    public function friends()
    {
        return $this->hasMany(AccountFriend::class, 'account_id');
    }

    /**
     * Get the account contacts
     */
    public function followers()
    {
        return $this->hasMany(AccountFollower::class, 'account_id');
    }

    /**
     * Get the account contacts
     */
    public function following()
    {
        return $this->hasMany(AccountFollower::class, 'follower_account_id');
    }

    public function followingTag()
    {
        return $this->hasMany(TagFollower::class, 'account_id');
    }

    /**
     * Get the account posts
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'account_id');
    }

    /**
     * Get the account groups
     */
    public function groups()
    {
        return $this->belongsToMany(AccountGroup::class, 'account_group_members', 'account_id', 'account_group_id');
    }

    /**
     * @param $groupId
     * @return AccountGroup|null
     */
    public function getGroup($groupId)
    {
        static $groups;

        if(empty($groups[$groupId])) {
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

        if(empty($friends[$friendId])) {
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

        if(empty($followers[$followerId])) {
            $followers[$followerId] = $this->followers()->where('account_followers.follower_account_id', '=', $followerId)->first();
        }

        return $followers[$followerId];
    }

    public function getBroadcastAccounts() {

        if($this->user->created_by === 'broadcast') {
            if($this->extra_info['channel']['target'] ?? false) {

                $usersQuery = User::getTargetAudience($this->extra_info['channel']['target'], false, true);

                return self::select('accounts.*')->joinSub($usersQuery, 'users', function ($join) {
                    $join->on('users.id', '=', 'accounts.user_id');
                })->get();
            }
        }

        return collect();
    }

    public function subscribedAccounts()
    {
        return $this->morphToMany(Account::class, 'notifiable', 'account_notifications')->withTimestamps();
    }

    public function subscribeAccounts()
    {
        return $this->morphedByMany(Account::class, 'notifiable', 'account_notifications')->withTimestamps();
    }

    public function subscribePosts()
    {
        return $this->morphedByMany(Post::class, 'notifiable', 'account_notifications')->withTimestamps();
    }

    public function subscribeTags()
    {
        return $this->morphedByMany(Tag::class, 'notifiable', 'account_notifications')->withTimestamps();
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
