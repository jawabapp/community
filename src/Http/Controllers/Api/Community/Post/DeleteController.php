<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

use Exception;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Events\PostDelete;
use Jawabapp\Community\Events\CommentDelete;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Post\DeleteRequest;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class DeleteController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index($id, DeleteRequest $request)
    {

        $user = CommunityFacade::getLoggedInUser();

        if (!empty($user->is_anonymous)) {
            throw ValidationException::withMessages([
                'id' => [trans('User is anonymous')],
            ]);
        }

        $post = Post::find($id);

        if (!$post) {
            throw ValidationException::withMessages([
                'id' => [trans('The post is not valid!')],
            ]);
        }

        $account = $user->getAccount($post->account_id);

        if (!$account) {
            throw ValidationException::withMessages([
                'id' => [trans("You don't have permission to delete this post!")],
            ]);
        }

        try {
            if ($post->parent_post_id) {
                $parent_post = $post->getRootPost();
                event(new CommentDelete([
                    'post_id' => $parent_post->id,
                    'sender_id' => $account->id,
                    'post_user_id' => $parent_post->account_id,
                ]));
            } else {
                event(new PostDelete([
                    'post_id' => $post->id,
                    'post_user_id' => $post->account_id,
                ]));
            }
        } catch (Exception $e) {
            //throw $e;
        }

        $post->delete();

        return response()->json(null, 204);
    }
}
