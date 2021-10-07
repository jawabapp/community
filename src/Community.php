<?php

namespace Jawabapp\Community;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Models\Tag;
use Jawabapp\Community\Events\CreatePostReply;
use Illuminate\Validation\ValidationException;

class Community
{
    // Build your next great package.
    public function createPost(Request $request)
    {
        // creatre posts

        $user = config('community.user_class')::getLoggedInUser();

        if (!empty($user->is_anonymous)) {
            throw ValidationException::withMessages([
                'id' => [trans('User is anonymous')],
            ]);
        }

        if (!empty($user->block_until) && Carbon::parse($user->block_until)->isFuture()) {
            throw ValidationException::withMessages([
                'account_id' => [trans('Account is blocked until') . ' ' . $user->block_until],
            ]);
        }

        $account = $user->getAccount($request->get('account_id'));

        if (!$account) {
            throw ValidationException::withMessages([
                'account_id' => [trans('Account id is not valid!')],
            ]);
        }

        if ($request->parent_post_id) {
            $parent_post = Post::find($request->get('parent_post_id'));
        }

        if ($request->post) {
            $this->post = Post\Text::create([
                'account_id' => $account->id,
                'parent_post_id' => $parent_post->id ?? null,
                'content' => $request->get('post'),
                'is_status' => false
            ]);
        }

        if (!empty($request->get('attachment_type'))) {

            $postClass = Post::class . '\\' . ucfirst($request->get('attachment_type'));

            if (class_exists($postClass)) {
                foreach ($request->attachments as $attachment) {
                    $post = $postClass::create([
                        'account_id' => $account->id,
                        'parent_post_id' => $parent_post->id ?? null,
                        'related_post_id' => $this->post->id ?? null,
                        'content' => $attachment,
                        'is_status' => false
                    ]);

                    if (empty($this->post) || is_null($this->post)) {
                        $this->post = $post;
                    }
                }
            } else {
                throw new \Exception('Post Class Type not Found');
            }
        }

        $post = Post::whereId($this->post->id)->with(['related', 'account'])->first();

        if ($post->parent_post_id) {
            $parentPost = Post::whereId($post->parent_post_id)->first();

            if ($account->id != $parentPost->account->id) {
                $rootPost = $post->getRootPost();

                event(new CreatePostReply([
                    'deeplink' => $rootPost->deep_link,
                    'post_id' => $rootPost->id,
                    'sender_id' => $account->id,
                    'post_user_id' => $rootPost->account_id,
                ]));
            }
        }

        return $post;
    }

    public function createPostWithTag(Request $request)
    {
        $post = $this->createPost($request);
        $this->linkPostWithTag($post, $request->get('hash_tag'));
    }

    public function linkPostWithTag($post, $hash_tag)
    {
        $tag = Tag::firstOrCreate(['hash_tag' => $hash_tag]);

        $post->tags()->attach([$tag->id]);
    }
}
