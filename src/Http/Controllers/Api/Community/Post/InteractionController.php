<?php


namespace App\Http\Controllers\Api\Community\Post;


use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Post\InteractionRequest;
use App\Models\Post;
use App\Models\PostInteraction;
use App\Plugins\CommonPlugin;
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

    public function index($id, InteractionRequest $request) {

        $user = $request->user(); /** @var \App\Models\User $user */

        if($user->is_anonymous && $request->get('type') == 'viewed') {
            throw ValidationException::withMessages([
                'id' => [trans('User is anonymous')],
            ]);
        }

        if(Carbon::parse($user->block_until)->isFuture()) {
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

        if(!$account) {
            throw ValidationException::withMessages([
                'id' => [trans("You don't have permission to do any Interaction with this post!")],
            ]);
        }

        if($request->get('type') == 'viewed') {
            PostInteraction::assignInteractionToAccount('viewed', $post->id, false, $account->id);
        } else {
            $postInteraction = PostInteraction::wherePostId($post->id)
                ->whereAccountId($request->get('account_id'))
                ->whereIn('type', PostInteraction::SINGLE_TYPES)
                ->first();

            if($request->get('isRemove')) {
                if($postInteraction) {
                    $postInteraction->delete();
                }
            } else {
                if($postInteraction) {
                    $postInteraction->update([
                        'type' => $request->get('type')
                    ]);
                } else {
                    PostInteraction::create([
                        'post_id' => $post->id,
                        'account_id' => $request->get('account_id'),
                        'type' => $request->get('type')
                    ]);

                    if($request->get('type') == 'vote_up' && $account->user_id != $post->account->user->id) {

                        $rootPost = $post->getRootPost();

                        CommonPlugin::mqttPublish($post->account->id,'usr/community/' . $post->account->user->id, [
                            'type' => 'interaction',
                            'interaction' => $request->get('type'),
                            'content' => trans('notification.post_like', ['nickname' => $account->slug], $post->account->user->language),
                            'deeplink' => $rootPost->deep_link,
                            'post_id' => $rootPost->id,
                            'account_sender_nickname' => $account->slug,
                            'account_sender_avatar' => $account->avatar['100*100'] ?? '',
                            'account_sender_id' => $account->id
                        ]);
                    }
                }
            }
        }

        return response()->json(Post::find($id)->interactions);
    }
}
