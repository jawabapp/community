<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\HashTag;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Jawabapp\Community\Models\Tag;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\Http\Controllers\Controller;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class InfoController extends Controller
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

        return response()->json([
            'result' => $tag
        ]);
    }
}
