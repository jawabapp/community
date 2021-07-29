<?php

namespace App\Http\Controllers\Api\Community\HashTag\Follow;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FollowingTagResource;
use App\Http\Resources\Api\PaginateResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        $account = Account::find($accountId);

        if (!$account) {
            throw ValidationException::withMessages([
                'id' => [trans('Account id is not valid!')],
            ]);
        }

        return response()->json([
            'result' => $account->followingTag()->with('tag')->get()
        ]);
    }

    public function v2($accountId, Request $request): JsonResponse
    {

        $account = Account::find($accountId);

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