<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;


use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Models\PostInteraction;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Events\PostInteractionCreate;
use Jawabapp\Community\Events\PostInteractionDelete;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Post\InteractionRequest;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class InteractionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index($id, InteractionRequest $request)
    {

        $user = CommunityFacade::getLoggedInUser();

        if (config('community.check_anonymous', true) && !empty($user->is_anonymous) && $request->get('type') == 'viewed') {
            throw ValidationException::withMessages([
                'id' => [trans('User is anonymous')],
            ]);
        }

        if (Carbon::parse($user->block_until)->isFuture()) {
            throw ValidationException::withMessages([
                'account_id' => [trans('Account is blocked until') . ' ' . $user->block_until],
            ]);
        }

        $post = Post::find($id);

        if (!$post) {
            throw ValidationException::withMessages([
                'id' => [trans('The post is not valid!')],
            ]);
        }

        $account = $user->getAccount($request->get('account_id'));
        if (!$account) {
            throw ValidationException::withMessages([
                'id' => [trans("You don't have permission to do any Interaction with this post!")],
            ]);
        }

        if ($request->get('type') == 'viewed') {
            PostInteraction::assignInteractionToAccount('viewed', $post->id, false, $account->id);
        } else {
            $postInteraction = PostInteraction::wherePostId($post->id)
                ->whereAccountId($account->id)
                ->whereIn('type', PostInteraction::SINGLE_TYPES)
                ->first();

            if ($request->get('isRemove')) {
                if ($postInteraction) {
                    $postInteraction->delete();

                    $rootPost = $post->getRootPost();
                    event(new PostInteractionDelete([
                        'interaction' => $request->get('type'),
                        'post_id' => $rootPost->id,
                        'post_user_id' => $rootPost->account_id,
                        'sender_id' => $account->id
                    ]));
                }
            } else {
                if ($postInteraction) {
                    $postInteraction->update([
                        'type' => $request->get('type')
                    ]);
                } else {
                    PostInteraction::create([
                        'post_id' => $post->id,
                        'account_id' => $account->id,
                        'type' => $request->get('type')
                    ]);
                    if ($account->id != $post->account->id) {

                        $rootPost = $post->getRootPost();

                        event(new PostInteractionCreate([
                            'interaction' => $request->get('type'),
                            'deep_link' => $rootPost->deep_link,
                            'post_id' => $rootPost->id,
                            'sender_id' => $account->id,
                            'post_user_id' => $post->account_id
                        ]));
                    }
                }
            }
        }

        return response()->json(Post::find($id)->interactions);
    }
}
