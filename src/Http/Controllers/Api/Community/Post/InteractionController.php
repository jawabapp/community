<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

use Carbon\Carbon;
use App\Plugins\CommonPlugin;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Models\PostInteraction;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\Events\PostInteraction as EventsPostInteraction;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Post\InteractionRequest;

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

        $user = $request->user();

        if ($user->is_anonymous && $request->get('type') == 'viewed') {
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
                ->whereAccountId($request->get('account_id'))
                ->whereIn('type', PostInteraction::SINGLE_TYPES)
                ->first();

            if ($request->get('isRemove')) {
                if ($postInteraction) {
                    $postInteraction->delete();
                }
            } else {
                if ($postInteraction) {
                    $postInteraction->update([
                        'type' => $request->get('type')
                    ]);
                } else {
                    PostInteraction::create([
                        'post_id' => $post->id,
                        'account_id' => $request->get('account_id'),
                        'type' => $request->get('type')
                    ]);

                    if ($request->get('type') == 'vote_up' && $account->getAccountUser()->id != $post->account->getAccountUser()->id) {
                        $rootPost = $post->getRootPost();
                        event(new EventsPostInteraction([
                            'interaction' => $request->get('type'),
                            'deeplink' => $rootPost->deep_link,
                            'post_id' => $rootPost->id,
                            'sender_id' => $account->id
                        ]));
                    }
                }
            }
        }

        return response()->json(Post::find($id)->interactions);
    }
}
