<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

use Illuminate\Http\JsonResponse;
use Jawabapp\Community\Models\Post;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Post\EditRequest;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class EditController extends Controller
{

    private $post = null;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function edit($id, EditRequest $request): JsonResponse
    {
        /** @var \Jawabapp\Community\Models\User $user */
        $user = $request->user();

        if ($user->is_anonymous) {
            throw ValidationException::withMessages([
                'id' => [trans('User is anonymous')],
            ]);
        }

        $this->post = Post::find($id);

        if (!$this->post) {
            throw ValidationException::withMessages([
                'id' => [trans('The post is not valid!')],
            ]);
        }

        $account = $user->getAccount($this->post->account_id);

        if (!$account) {
            throw ValidationException::withMessages([
                'id' => [trans("You don't have permission to delete this post!")],
            ]);
        }

        if ($this->post instanceof Post\Text) {
            $this->post->update([
                'content' => $request->get('post'),
            ]);
        }

        return response()->json([
            'result' => $this->post
        ]);
    }
}
