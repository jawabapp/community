<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Traits\HasDynamicRelation;

class PostInteraction extends Model
{
    use HasDynamicRelation;

    protected $fillable = [
        'post_id',
        'account_id',
        'type'
    ];

    const TYPES = [
        'vote_up',
        'vote_down',
        'viewed'
    ];

    const SINGLE_TYPES = [
        'vote_up',
        'vote_down',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function (self $node) {

            if ($node->getAttribute('post_id')) {
                $post = Post::find($node->getAttribute('post_id'));
                if ($post) {
                    $extra_info = $post->extra_info;
                    $extra_info['interaction_users'] = $post->getUserInteractions();
                    $post->update([
                        'interactions' => $post->getInteractions(),
                        'extra_info' => $extra_info,
                    ]);
                }
            }
        });

        static::deleted(function (self $node) {

            if ($node->getAttribute('post_id')) {
                $post = Post::find($node->getAttribute('post_id'));
                if ($post) {
                    $extra_info = $post->extra_info;
                    $extra_info['interaction_users'] = $post->getUserInteractions();
                    $post->update([
                        'interactions' => $post->getInteractions(),
                        'extra_info' => $extra_info
                    ]);
                }
            }
        });
    }

    public function account()
    {
        return $this->belongsTo(CommunityFacade::getUserClass(), 'account_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public static function assignInteractionToAccount($interaction, $postId, $root = true, $accountId = null)
    {

        if (is_null($accountId)) {
            $accountId = CommunityFacade::getUserClass()::getActiveAccountId();
        }

        $post = Post::find($postId);

        if ($root && $post) {
            $post = $post->getRootPost();
        }

        if ($post->id ?? false) {
            if (self::wherePostId($post->id)->whereAccountId($accountId)->whereType($interaction)->doesntExist()) {
                self::create([
                    'post_id' => $post->id,
                    'account_id' => $accountId,
                    'type' => $interaction
                ]);
            }
        }

        return false;
    }
}
