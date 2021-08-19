<?php

namespace Jawabapp\Community;

use Carbon\Carbon;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Plugins\CommonPlugin;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\Http\Requests\Post\CreateRequest;

class Community
{
    // Build your next great package.
    public function createPost(CreateRequest $request)
    {
        // creatre posts

        /** @var \Jawabapp\Community\Models\User $user */
        $user = $request->user();

        if ($user->is_anonymous) {
            throw ValidationException::withMessages([
                'id' => [trans('User is anonymous')],
            ]);
        }

        if (Carbon::parse($user->block_until)->isFuture()) {
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

        if ($request->has('parent_post_id')) {
            $parent_post = Post::find($request->get('parent_post_id'));
        }

        if ($request->has('post')) {
            $this->post = Post\Text::create([
                'account_id' => $account->id,
                'parent_post_id' => $parent_post->id ?? null,
                'content' => $request->get('post'),
                'is_status' => false
            ]);
        }

        if ($request->has('attachment_type')) {

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

                    if (is_null($this->post)) {
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

            if ($account->user_id != $parentPost->account->getAccountUser()->id) {

                $rootPost = $post->getRootPost();

                // CommonPlugin::mqttPublish($parentPost->account->id, 'usr/community/' . $parentPost->account->getAccountUser()->id, [
                //     'type' => 'reply',
                //     'content' => trans('notification.post_reply', ['nickname' => $account->slug], $parentPost->account->getAccountUser()->language),
                //     'deeplink' => $rootPost->deep_link,
                //     'post_id' => $rootPost->id,
                //     'account_sender_nickname' => $account->slug,
                //     'account_sender_avatar' => $account->avatar['100*100'] ?? '',
                //     'account_sender_id' => $account->id
                // ]);
            }
        }

        return $post;
    }
    
}
