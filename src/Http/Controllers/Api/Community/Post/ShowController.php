<?php

namespace JawabApp\Community\Http\Controllers\Api\Community\Post;

use Illuminate\Http\JsonResponse;
use JawabApp\Community\Models\Post;
use Illuminate\Support\Facades\Request;
use JawabApp\Community\Models\PostInteraction;
use Illuminate\Validation\ValidationException;
use JawabApp\Community\Http\Controllers\Controller;

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
        $query = Post::with(['related', 'account', 'tags']);

        if (preg_match("/[a-zA-Z]/i", $id)) {
            $post = $query->where('hash', $id)->first();
        } else {
            $post = $query->find($id);

            if (!$post) {
                $query = Post::with(['related', 'account', 'tags']);
                $post = $query->where('hash', $id)->first();
            }
        }

        if (!$post) {
            throw ValidationException::withMessages([
                'id' => [trans('The post is not valid!')],
            ]);
        }

        PostInteraction::assignInteractionToAccount('viewed', $post->id, false);

        return response()->json([
            'result' => $post
        ]);
    }
}
