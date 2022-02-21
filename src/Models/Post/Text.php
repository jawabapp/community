<?php

namespace Jawabapp\Community\Models\Post;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Models\Tag;
use Jawabapp\Community\Events\PostMention;

class Text extends Post
{

    protected static function boot()
    {
        parent::boot();

        static::saved(function (self $node) {

            if ($node->isDirty('content')) {
                $content = $node->getAttribute('content');

                // hash tags
                $hashTags = self::getHashTags($content);

                $tags = [];
                foreach ($hashTags as $hashTag) {
                    $tag = Tag::firstOrCreate(['hash_tag' => $hashTag]);
                    array_push($tags, $tag->id);
                }
                $node->tags()->sync($tags);

                // mentions
                $mentions = self::getMentions($content);

                if ($mentions) {
                    foreach ($mentions as $mention) {
                        $account = CommunityFacade::getUserClass()::where('slug', $mention)->first();

                        if ($account) {

                            $rootPost = $node->getRootPost();

                            event(new PostMention([
                                'deep_link' => $rootPost->deep_link,
                                'post_id' => $rootPost->id,
                                'sender_id' => $node->account->id,
                                'post_user_id' => $account->id,
                            ]));
                        }
                    }
                }
            }
        });
    }

    public function getMorphClass()
    {
        return Post::class;
    }

    public function draw()
    {
        return view('community::admin.posts.types.text')->with('post', $this);
    }

    private static function getHashTags($string)
    {
        preg_match_all("/(#\w+)/u", $string, $matches);

        $hashTags = [];

        if ($matches) {
            $hashTagsArray = array_count_values($matches[0]);
            $hashTags = array_keys($hashTagsArray);
        }

        return $hashTags;
    }

    private static function getMentions($string)
    {
        preg_match_all("/(@\w+)/u", $string, $matches);

        $mentions = [];

        if ($matches) {
            $mentionsArray = array_count_values($matches[0]);
            $mentions = array_keys($mentionsArray);
        }

        return $mentions;
    }
}
