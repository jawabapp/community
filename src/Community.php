<?php

namespace Jawabapp\Community;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Jawabapp\Community\Models\Tag;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Events\PostCreate;
use Jawabapp\Community\Events\CommentCreate;
use Illuminate\Validation\ValidationException;

class Community
{

    public function createPost(Request $request)
    {
        $user = CommunityFacade::getLoggedInUser();

        if(!$user) {
            throw ValidationException::withMessages([
                'account_id' => [trans('Account id is not valid!')],
            ]);
        }

        if (config('community.check_anonymous', true) && !empty($user->is_anonymous)) {
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

        return $this->insertPost($request);


    }

    /**
     * @throws Exception
     */
    public function createPostWithTag(Request $request)
    {

        $hash_tag = str_replace([' ', '#'], ['_', ''], trim($request->get('hash_tag')));

        if ($hash_tag) {

            $tag = Tag::firstOrCreate(['hash_tag' => "#{$hash_tag}"]);

            if($tag) {
                $post = $this->createPost($request);

                $post->tags()->attach([$tag->id]);

                return $post->refresh();
            }
        }

        return null;
    }

    public function getLoggedInUser()
    {

        $userClass = $this->getUserClass();

        if (method_exists($userClass, 'getLoggedInUser')) {
            return $userClass::getLoggedInUser();
        }

        return null;
    }

    public function getUserClass()
    {

        if (class_exists(config('community.user_class'))) {
            return config('community.user_class');
        }

        return null;
    }

    public function insertPost($request)
    {
        $postObject = null;
        if ($request->get('parent_post_id')) {
            $parent_post = Post::find($request->get('parent_post_id'));
        }

        if ($request->get('post')) {
            $postObject = Post\Text::create([
                'account_id' => $request->get('account_id'),
                'parent_post_id' => $parent_post->id ?? null,
                'content' => $request->get('post'),
                'is_status' => false
            ]);
        }

        if (!empty($request->get('attachment_type'))) {

            $postClass = Post::class . '\\' . ucfirst($request->get('attachment_type'));

            if (class_exists($postClass)) {
                foreach ($request->attachments as $attachment) {
                    $attachedPost = $postClass::create([
                        'account_id' => $request->get('account_id'),
                        'parent_post_id' => $parent_post->id ?? null,
                        'related_post_id' => $this->post->id ?? null,
                        'content' => $attachment,
                        'is_status' => false
                    ]);

                    if (!$postObject) {
                        $postObject = $attachedPost;
                    }
                }
            } else {
                throw ValidationException::withMessages([
                    'post_class' => [trans('Post Class Type not Found')],
                ]);
            }
        }

        $post = Post::whereId($postObject->id)->with(['related', 'account'])->first();

        try {
            //comment
            if ($post->parent_post_id) {
                $parentPost = Post::whereId($post->parent_post_id)->first();

                if ($request->get('account_id') != $parentPost->account->id) {
                    $rootPost = $post->getRootPost();

                    event(new CommentCreate([
                        'deep_link' => $rootPost->deep_link,
                        'post_id' => $rootPost->id,
                        'sender_id' => $request->get('account_id'),
                        'post_user_id' => $parentPost->account_id,
                    ]));
                }
            } else {
                event(new PostCreate([
                    'deep_link' => $post->deep_link,
                    'post_id' => $post->id,
                    'post_user_id' => $post->account_id,
                ]));
            }
        } catch (Exception $e) {
            //throw $e;
        }

        return $post;
    }
}
