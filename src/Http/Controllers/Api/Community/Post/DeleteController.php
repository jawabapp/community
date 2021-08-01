<?php

namespace JawabApp\Community\Http\Controllers\Api\Community\Post;

use JawabApp\Community\Models\Post;
use Illuminate\Validation\ValidationException;
use JawabApp\Community\Http\Controllers\Controller;
use JawabApp\Community\Http\Requests\Community\Post\DeleteRequest;

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

        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->is_anonymous) {
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

        $post->delete();

        return response()->json(null, 204);
    }
}
