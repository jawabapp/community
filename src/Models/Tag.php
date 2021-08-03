<?php

namespace Jawabapp\Community\Models;

use Jawabapp\Community\Services\DeepLinkBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Tag extends Model
{
    use SoftDeletes;

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
        $activeAccountId = Account::getActiveAccountId();
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
        return $this->belongsToMany(Account::class, 'tag_followers', 'tag_id', 'account_id');
    }

    public function isAccountFollowingBy()
    {
        $activeAccountId = Account::getActiveAccountId();

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

        $deep_link = '';

        try {

            $hash_tag = (str_replace('#', '', $this->hash_tag));

            $deep_link = DeepLinkBuilder::generate(new Request([
                'link' => "https://trends.jawab.app/hashtag/{$hash_tag}?mode=hashtag&hashtag={$hash_tag}",
                'analyticsUtmSource' => "jawabchat",
                'analyticsUtmMedium' => "hashtag",
                'analyticsUtmCampaign' => "{$hash_tag}",
            ]), 'https://hashtag.jawab.app');

            $this->update([
                'deep_link' => $deep_link
            ]);
        } catch (\Exception $e) {
        }

        return $deep_link;
    }

    public function subscribedAccounts()
    {
        return $this->morphToMany(Account::class, 'notifiable', 'account_notifications')->withTimestamps();
    }
}
