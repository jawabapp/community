<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;

class PostInteraction extends Model
{
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
                    $post->update([
                        'interactions' => $post->getInteractions()
                    ]);
                }
            }
        });

        static::deleted(function (self $node) {

            if ($node->getAttribute('post_id')) {
                $post = Post::find($node->getAttribute('post_id'));
                if ($post) {
                    $post->update([
                        'interactions' => $post->getInteractions()
                    ]);
                }
            }
        });
    }

    public function account()
    {
        return $this->belongsTo(config('community.user_class'), 'account_id');
    }

    public function post()
    {
        return $this->belongsTo(config('community.user_class'), 'post_id');
    }

    public static function assignInteractionToAccount($interaction, $postId, $root = true, $accountId = null)
    {

        if (is_null($accountId)) {
            $accountId = Account::getActiveAccountId();
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
