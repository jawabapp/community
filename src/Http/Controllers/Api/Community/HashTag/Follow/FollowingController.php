<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\HashTag\Follow;

use Jawabapp\Community\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Resources\Api\FollowingTagResource;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class FollowingController extends Controller
{
    public function __construct()
    {
        if (request()->server('HTTP_AUTHORIZATION')) {
            $this->middleware('auth:api');
        } else {
            $this->middleware('guest');
        }
    }

    /**
     * Hashtag Account Following
     *
     * [Api to get Following]
     * Note use {api-version:2} you can find the response under 300
     *
     * @urlParam  accountId required The accountId of the user.
     *
     * @responseFile  responses/community/hash-tag/follow/following.json
     * @responseFile 300 responses/community/hash-tag/follow/following.v2.json
     */
    public function index($accountId, Request $request): JsonResponse
    {

        $account = config('community.user_class')::find($accountId);

        if (!$account) {
            throw ValidationException::withMessages([
                'id' => [trans('Account id is not valid!')],
            ]);
        }

        $query = $account->followingTag()->with('tag');

        $data = FollowingTagResource::collection(
            $query->paginate(10)
        )->resource;

        return response()->json($data);
    }
}
