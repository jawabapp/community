<?php

namespace Jawabapp\Community\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Services\DeepLinkBuilder;
use Jawabapp\Community\Traits\HasDynamicRelation;

class Post extends Model
{
    use SoftDeletes, HasDynamicRelation;

    protected $table = 'posts';

    protected $fillable = [
        'account_id',
        'parent_post_id',
        'related_post_id',
        'class_type',
        'content',
        'is_status',
        'interactions',
        'deep_link',
        'children_count',
        'hash',
        'extra_info',
        'topic',
        'weight'
    ];

    protected $hidden = [
        'class_type',
    ];

    protected $casts = [
        'interactions' => 'array',
        'extra_info' => 'array'
    ];

    protected $appends = [
        'type',

        'account_interaction',
        'is_subscribed'
    ];

    public function myInteractions()
    {
        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();

        if ($activeAccountId) {
            return $this->interactions()->whereIn('type', PostInteraction::SINGLE_TYPES)->whereAccountId($activeAccountId);
        }
    }

    public function mySubscribes()
    {
        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();

        if ($activeAccountId) {
            return $this->subscribedAccounts()->whereAccountId($activeAccountId);
        }
    }

    public function getTypeAttribute()
    {
        return strtolower(str_replace(self::class . '\\', '', $this->class_type));
    }

    public function getAccountInteractionAttribute()
    {
        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();

        if ($activeAccountId) {
            return $this->myInteractions->where('post_id', $this->getKey())->first()->type ?? '';
        }

        return '';
    }

    public function getIsSubscribedAttribute()
    {
        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();

        if ($activeAccountId) {
            return $this->mySubscribes->contains('pivot_notifiable_id', $this->getKey());
        }

        return false;
    }

    public function getInteractionsAttribute($value)
    {

        if (is_null($value)) {
            return array_fill_keys(PostInteraction::TYPES, 0);
        }

        return json_decode($value, true);
    }

    #################### From Builder Depend on Class Type :: START ####################

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('active_account', function (Builder $builder) {

            $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();

            if ($activeAccountId) {
                $builder->whereNotIn('posts.id', function ($q) use ($activeAccountId) {
                    $q->select('post_reports.post_id')->from('post_reports')->where('post_reports.account_id', $activeAccountId);
                });

                $builder->whereNotIn('posts.account_id', function ($q) use ($activeAccountId) {
                    $q->select('account_blocks.block_account_id')->from('account_blocks')->where('account_blocks.account_id', $activeAccountId);
                });
            }
        });

        static::creating(function (self $node) {
            $node->setAttribute('hash', uniqid());
            $node->setAttribute('topic', 'notifications/posts/');
            $node->setAttribute('deep_link', $node->generateDeepLink(true));
        });

        static::updating(function (self $node) {
            if($node->getAttribute('topic') == 'notifications/posts/') {
                $node->setAttribute('topic', 'notifications/posts/' . $node->getKey());
            }
        });

        static::saving(function (self $node) {
            if (static::class != self::class) {
                $node->setAttribute('class_type', static::class);
            }
        });

        static::created(function (self $node) {
            $node->updateParentsCount();
            $node->updatePostAccountCount();
            $node->updatePostTagsCount();
        });

        static::saved(function (self $node) {
            $node->resetCache();
        });

        static::deleted(function (self $node) {
            $node->updateParentsCount(true);
            $node->updatePostAccountCount();
            $node->updatePostTagsCount();
            $node->resetCache();
        });
    }

    private function updatePostAccountCount()
    {
        if ($this->account) {
            try {
                $this->account->update([
                    'post_count' => $this->account->getPostCount()
                ]);
            } catch (\Exception $e) {
                \Log::error('updatePostAccountCount ' . $e->getMessage());
            }
        }
    }

    private function updatePostTagsCount()
    {
        if ($this->tags) {
            foreach ($this->tags as $tag) {
                $tag->update([
                    'posts_count' => $tag->getPostsCount()
                ]);
            }
        }
    }

    /**
     * https://github.com/laravel/framework/issues/555
     *
     * @param array $attributes
     * @param null $connection
     * @return Post|Model
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        if (isset($attributes->class_type) && class_exists($attributes->class_type)) {

            $model = (new $attributes->class_type((array) $attributes));

            $model->exists = true;

            $model->setTable($this->getTable());
        } else {
            $model = $this->newInstance([], true);
        }

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    #################### From Builder Depend on Class Type :: END ####################

    public function resetCache()
    {
        Cache::tags('posts')->flush();
    }

    public function account()
    {
        return $this->belongsTo(CommunityFacade::getUserClass(), 'account_id');
    }

    public function interactions()
    {
        return $this->hasMany(PostInteraction::class, 'post_id');
    }

    public function getReports()
    {
        return $this->reports()
            ->select('report', DB::raw('count(*) as total'))
            ->groupBy('report')->get()->pluck('total', 'report')->all();
    }

    public function reports()
    {
        return $this->hasMany(PostReport::class, 'post_id');
    }

    public function children()
    {
        return $this->hasMany(Post::class, 'parent_post_id', 'id')->whereNull('related_post_id');
    }

    public function related()
    {
        return $this->hasMany(Post::class, 'related_post_id', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags', 'post_id', 'tag_id');
    }

    public function getInteractions()
    {
        return array_merge(
            array_fill_keys(PostInteraction::TYPES, 0),
            $this->interactions()
                ->select('type', DB::raw('count(*) as total'))
                ->groupBy('type')->get()->pluck('total', 'type')->all()
        );
    }

    public function updateParentsCount($isDecrease = false)
    {
        if(!empty($this->parent_post_id)) {
            $parent_post = self::find($this->parent_post_id);

            if ($parent_post && empty($this->related_post_id)) {
                $parent_post->update([
//                'children_count' => ($isDecrease ? ($parent_post->children_count - $this->children_count - 1) : ($parent_post->children_count + 1))
                    'children_count' => self::where('parent_post_id', $parent_post->id)->count()
                ]);

                $parent_post->updateParentsCount($isDecrease);
            }
        }
    }

    public function generateDeepLink($returnOnly = false)
    {
        if(!config('community.deep_link.post')) {
            return null;
        }

        //$slug = ($this->account->slug_without_at);
        $hash = ($this->hash);

        $deep_link = DeepLinkBuilder::generate(
            [
                'mode' => 'post',
                //'slug' => $slug,
                'hash' => $hash,
            ],
            [
                'domain-uri-prefix' => config('community.deep_link.post.url_prefix'),
                'utm-source' => config('community.deep_link.post.utm_source'),
                'utm-medium' => config('community.deep_link.post.utm_medium'),
                'utm-campaign' => config('community.deep_link.post.utm_campaign') ?? "{$hash}",
            ]
        );

        if (!$returnOnly && $deep_link) {
            $this->update([
                'deep_link' => $deep_link
            ]);
        }

        return $deep_link;
    }

    public function getRootPost()
    {
        if ($this->parent_post_id) {
            $parentPost = self::find($this->parent_post_id);
            if ($parentPost) {
                return $parentPost->getRootPost();
            }
        }

        return $this;
    }

    public static function getUserTimelineFilter(Builder $builder)
    {

        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();

        if ($activeAccountId) {

            $per_page = config('community.timeline_filter_limit', 1000);

            $myPosts = self::baseQuery()
                ->where('posts.account_id', $activeAccountId)
                ->limit($per_page)
                ->get(['posts.id'])->pluck('id')->all();


            $accountLikePosts = self::baseQuery()
                ->whereIn('posts.account_id', function(\Illuminate\Database\Query\Builder $q) use ($activeAccountId) {
                    $q->select('account_likes.liked_account_id')
                        ->from('account_likes')
                        ->where('account_likes.account_id', $activeAccountId)
                        ->whereNotIn('account_likes.liked_account_id', config('community.ignore_liked_user_posts_to_show_in_timeline', []));
                })
                ->limit($per_page)
                ->get(['posts.id'])->pluck('id')->all();


            $accountFollowPosts = self::baseQuery()
                ->whereIn('posts.account_id', function(\Illuminate\Database\Query\Builder $q) use ($activeAccountId) {
                    $q->select('account_followers.follower_account_id')
                        ->from('account_followers')
                        ->where('account_followers.account_id', $activeAccountId)
                        ->whereNotIn('account_followers.follower_account_id', config('community.ignore_followed_user_posts_to_show_in_timeline', []));
                })
                ->limit($per_page)
                ->get(['posts.id'])->pluck('id')->all();


            $interactionPosts = self::baseQuery()
                ->whereIn('posts.id', function (\Illuminate\Database\Query\Builder $q) use ($activeAccountId) {
                    $q->select('post_interactions.post_id')
                        ->from('post_interactions')
                        ->where('post_interactions.account_id', $activeAccountId);
                })
                ->limit($per_page)
                ->get(['posts.id'])->pluck('id')->all();


            $commentedPosts = self::baseQuery()
                ->whereIn('posts.id', function (\Illuminate\Database\Query\Builder $q) use ($activeAccountId) {
                    $q->select('posts.parent_post_id')
                        ->from('posts')
                        ->whereNotNull('parent_post_id')
                        ->where('posts.account_id', $activeAccountId);
                })
                ->limit($per_page)
                ->get(['posts.id'])->pluck('id')->all();


            $tagFollowPosts = self::baseQuery()
                ->whereIn('posts.id', function (\Illuminate\Database\Query\Builder $q) use ($activeAccountId) {
                    $q->select('post_tags.post_id')
                        ->from('post_tags')
                        ->join('tag_followers', 'post_tags.tag_id', '=', 'tag_followers.tag_id')
                        ->where('tag_followers.account_id', $activeAccountId);
                })
                ->limit($per_page)
                ->get(['posts.id'])->pluck('id')->all();


            $customPostIds = [];
            if(method_exists(CommunityFacade::getUserClass(), 'getCustomPostIdsForUserTimeline')) {
                $customPostIds = CommunityFacade::getUserClass()::getCustomPostIdsForUserTimeline($activeAccountId, $per_page);
            }

            $postIds = array_sort(array_unique(array_merge($myPosts, $accountLikePosts, $accountFollowPosts, $interactionPosts, $commentedPosts, $tagFollowPosts, $customPostIds), SORT_NUMERIC));

            $builder->whereIn('posts.id', $postIds)
                ->whereNull('related_post_id')
                ->whereNull('parent_post_id');

            if(method_exists(CommunityFacade::getUserClass(), 'customUserTimelineFilter')) {
                CommunityFacade::getUserClass()::customUserTimelineFilter($activeAccountId, $builder);
            }
        }

    }

    /**
     * Begin base query without any append or relation.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function baseQuery() {
        return DB::table('posts')->orderBy('posts.id', 'desc');
    }

    public static function getRelatedPostFilteredData(Builder $builder, Post $post)
    {

        if (!$post) {
            return;
        }

        // Filter by class_type
        $classType = $post->class_type;

        if ($post->related[0] ?? false) {
            $classType = $post->related[0]->class_type;
        }

        $builder->whereIn('posts.id', function ($q) use ($classType) {
            $q->select(DB::raw('(CASE WHEN p.related_post_id IS NULL THEN p.id ELSE p.related_post_id END) AS post_id'))->from('posts as p')
                ->where('p.class_type', $classType);
        });

        // Filter by tags
        $builder->whereIn('posts.id', function ($q) use ($post) {
            $q->select('post_tags.post_id')->from('post_tags')
                ->whereIn('post_tags.tag_id', function ($qq) use ($post) {
                    $qq->select('post_tags.tag_id')->from('post_tags')->where('post_tags.post_id', $post->id);
                });
        });

        // Filter by remove self
        $builder->where('posts.id', '!=', $post->id);
    }

    public function subscribedAccounts()
    {
        return $this->morphToMany(CommunityFacade::getUserClass(), 'notifiable', 'account_notifications', null, 'account_id')->withTimestamps();
    }

    public static function withPost() {

        $with = ['related', 'account', 'tags'];

        if($timeline_with = config('community.timeline_post_with', [])) {
            $with = array_merge($with, $timeline_with);
        }

        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();
        if ($activeAccountId) {
            $with = array_merge($with, [
                'myInteractions',
                'mySubscribes',
                'tags.myFollowers',
                'tags.mySubscribes',
            ]);
        }

        return $with;
    }
}
