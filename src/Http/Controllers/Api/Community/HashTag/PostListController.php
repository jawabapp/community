<?php

namespace App\Http\Controllers\Api\Community\HashTag;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class PostListController extends Controller
{

    public function __construct()
    {
        if (request()->server('HTTP_AUTHORIZATION')) {
            $this->middleware('auth:api');
        } else {
            $this->middleware('guest');
        }
    }

    public function index(Request $request): JsonResponse
    {
        $hashTag = '#' . str_replace('#', '', $request->get('hash_tag'));

        $tag = Tag::where('hash_tag', $hashTag)->first();

        if (!$tag) {
            throw ValidationException::withMessages([
                'tag' => [trans('The tag is not valid!')],
            ]);
        }

        $query = $tag->posts()
            ->whereNull('related_post_id')
            ->whereNull('parent_post_id')
            ->with(['related', 'account']);

        return response()->json($query->latest()->paginate(10));
    }
}
