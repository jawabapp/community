<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

//use Jawabapp\Community\Http\Resources\Api\PostResource;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Models\PostInteraction;
use Jawabapp\Community\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class ShowController extends Controller
{
    public function __construct()
    {
        if (request()->server('HTTP_AUTHORIZATION')) {
            $this->middleware('auth:api');
        } else {
            $this->middleware('guest');
        }
    }

    public function index($id, Request $request): JsonResponse
    {

        $query = Post::with(Post::withPost());

        if (preg_match("/[a-zA-Z]/i", $id)) {
            $query->where('hash', $id);
        } else if (is_numeric($id)) {
            $query->where('id', $id);
        } else {
            $query->where('id', $id)->orWhere('hash', $id);
        }

        $post = $query->first();

        if (!$post) {
            throw ValidationException::withMessages([
                'id' => [trans('The post is not valid!')],
            ]);
        }

        PostInteraction::assignInteractionToAccount('viewed', $post->id, false);

        return response()->json([
//            'result' => PostResource::make($post),
            'result' => $post,
        ]);
    }
}
