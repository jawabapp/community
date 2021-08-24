<?php

namespace Jawabapp\Community\Models;

use Jawabapp\Community\Services\Caching;
use Jawabapp\Community\Services\DeepLinkBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
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
        'topic'
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

    public function getTypeAttribute()
    {
        return strtolower(str_replace(self::class . '\\', '', $this->class_type));
    }

    public function getAccountInteractionAttribute()
    {
        $activeAccountId = config('community.user_class')::getActiveAccountId();

        if ($activeAccountId) {
            return PostInteraction::wherePostId($this->getKey())
                ->whereAccountId($activeAccountId)
                ->whereIn('type', PostInteraction::SINGLE_TYPES)
                ->first()->type ?? '';
        }

        return '';
    }

    public function getIsSubscribedAttribute()
    {
        $activeAccountId = config('community.user_class')::getActiveAccountId();
        if ($activeAccountId) {
            return $this->subscribedAccounts()->where('account_id', $activeAccountId)->exists();
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

            $activeAccountId = config('community.user_class')::getActiveAccountId();

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
        });

        static::created(function (self $node) {
            $node->generateDeepLink();
            $node->updateParentsCount();
            $node->updatePostAccountCount();
            $node->updatePostAccountCount();
            $node->updatePostTagsCount();
        });

        static::saving(function (self $node) {
            if (static::class != self::class) {
                $node->setAttribute('topic', 'notifications/posts/' . $node->id);
                $node->setAttribute('class_type', static::class);
            }
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
        if ($this->account)
            $this->account->update([
                'post_count' => $this->account->getPostCount()
            ]);
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
        Caching::deleteCacheByTags('posts');
    }

    public function account()
    {
        return $this->belongsTo(config('community.user_class'), 'account_id');
    }

    public function interactions()
    {
        return $this->hasMany(PostInteraction::class, 'post_id');
    }

    public function getReports()
    {
        return $this->reports()
            ->select('report', \DB::raw('count(*) as total'))
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
                ->select('type', \DB::raw('count(*) as total'))
                ->groupBy('type')->get()->pluck('total', 'type')->all()
        );
    }

    public function updateParentsCount($isDecrease = false)
    {

        $parent_post = self::find($this->parent_post_id);

        if ($parent_post && empty($this->related_post_id)) {
            $parent_post->update([
                'children_count' => ($isDecrease ? ($parent_post->children_count - $this->children_count - 1) : ($parent_post->children_count + 1))
            ]);

            $parent_post->updateParentsCount($isDecrease);
        }
    }

    public function generateDeepLink()
    {

        $deep_link = '';

        try {

            $slug = ($this->account->slug_without_at);
            $hash = ($this->hash);

            $deep_link = DeepLinkBuilder::generate(new Request([
                'link' => "https://trends.jawab.app/{$slug}/post/{$hash}?mode=post&hash={$hash}",
                'analyticsUtmSource' => "jawabchat",
                'analyticsUtmMedium' => "post",
                'analyticsUtmCampaign' => "{$slug}-{$hash}",
            ]), 'https://post.jawab.app');

            $this->update([
                'deep_link' => $deep_link
            ]);
        } catch (\Exception $e) {
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

    public static function getUserFilteredData(Builder $builder)
    {

        $activeAccountId = config('community.user_class')::getActiveAccountId();
        if ($activeAccountId) {

            $tagGroupFollower = TagGroupFollower::where('account_id', $activeAccountId)->count();

            if ($tagGroupFollower == 0) {
                $builder->where(function ($query) use ($activeAccountId) {
                    $query->whereNotIn('posts.id', function ($q) use ($activeAccountId) {
                        $q->select('post_tags.post_id')->from('post_tags')
                            ->join('tags', 'post_tags.tag_id', '=', 'tags.id')
                            ->join('tag_groups', 'tags.tag_group_id', '=', 'tag_groups.id')
                            ->where('tag_groups.hide_in_public', 1);
                    });
                });
            } else {
                $builder->where(function ($query) use ($activeAccountId) {
                    // Get user's followed tag-groups posts
                    $query->whereIn('posts.id', function ($q) use ($activeAccountId) {
                        $q->select('post_tags.post_id')->from('post_tags')
                            ->join('tags', 'post_tags.tag_id', '=', 'tags.id')
                            ->join('tag_group_followers', 'tags.tag_group_id', '=', 'tag_group_followers.tag_group_id')
                            ->where('tag_group_followers.account_id', $activeAccountId);
                    });
                    // Get user's followed hashtags posts
                    $query->orWhereIn('posts.id', function ($q) use ($activeAccountId) {
                        $q->select('post_tags.post_id')->from('post_tags')
                            ->join('tag_followers', 'post_tags.tag_id', '=', 'tag_followers.tag_id')
                            ->where('tag_followers.account_id', $activeAccountId);
                    });
                    //                    // Get user's followed accounts posts
                    //                    $query->orWhereIn('posts.account_id', function($q) use ($activeAccountId) {
                    //                        $q->select('account_followers.follower_account_id')->from('account_followers')
                    //                            ->where('account_followers.account_id', $activeAccountId);
                    //                    });
                    // Get user's own posts
                    $query->orWhere('posts.account_id', $activeAccountId);
                });
            }
        }
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
            $q->select(\DB::raw('(CASE WHEN p.related_post_id IS NULL THEN p.id ELSE p.related_post_id END) AS post_id'))->from('posts as p')
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
        return $this->morphToMany(config('community.user_class'), 'notifiable', 'account_notifications', null, 'account_id')->withTimestamps();
    }
}
