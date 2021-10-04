<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

use Jawabapp\Community\Models\Post;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Post\DeleteRequest;
use Jawabapp\Community\Events\DeletePostReply;

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

        $user = config('community.user_class')::getDefaultAccount();

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

        $parent_post_id = $post->parent_post_id;
        $post->delete();

        event(new DeletePostReply([
            'post_id' => $parent_post_id,
            'sender_id' => $account->id
        ]));

        return response()->json(null, 204);
    }
}
