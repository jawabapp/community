<?php

namespace JawabApp\Community\Http\Controllers\Api\Community\Post;

use Illuminate\Http\JsonResponse;
use JawabApp\Community\Models\Post;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;
use JawabApp\Community\Http\Controllers\Controller;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class RelatedController extends Controller
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
        $post = Post::with(['related'])->find($id);

        if (!$post) {
            throw ValidationException::withMessages([
                'id' => [trans('The post is not valid!')],
            ]);
        }

        $query = Post::whereNull('parent_post_id')
            ->whereNull('related_post_id')
            ->with(['related', 'account'])
            ->latest();

        // Get user's filtered home data
        Post::getUserFilteredData($query);

        // Get Related filter
        Post::getRelatedPostFilteredData($query, $post);

        return response()->json(
            $query->paginate(10)
        );
    }
}
