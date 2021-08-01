<?php

namespace JawabApp\Community\Http\Controllers\Api\Community\HashTag\Follow;

use Illuminate\Http\JsonResponse;
use JawabApp\Community\Models\Tag;
use JawabApp\Community\Models\TagFollower;
use Illuminate\Validation\ValidationException;
use JawabApp\Community\Http\Controllers\Controller;
use JawabApp\Community\Http\Requests\Community\Tag\Follow\UnFollowRequest;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class UnFollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index($accountId, UnFollowRequest $request): JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\User $user */

        if ($user->is_anonymous) {
            throw ValidationException::withMessages([
                'id' => [trans('User is anonymous')],
            ]);
        }

        $owner_account = $user->getAccount($accountId);

        if (!$owner_account) {
            throw ValidationException::withMessages([
                'account_id' => [trans('Account id is not valid!')],
            ]);
        }

        if ($request->has('hash_tag')) {
            $hashTag = '#' . str_replace('#', '', $request->get('hash_tag'));
            $tag = Tag::where('hash_tag', $hashTag)->first();
        } else {
            $tag = Tag::find($request->get('hash_tag_id'));
        }

        if (empty($tag)) {
            throw ValidationException::withMessages([
                'hash_tag' => [trans('The hash tag is not valid!')],
            ]);
        }

        $tagFollower = TagFollower::whereAccountId($owner_account->id)->whereTagId($tag->id)->first();

        if (!$tagFollower) {
            throw ValidationException::withMessages([
                'hash_tag' => [trans('hash tag is not in your follower')],
            ]);
        }

        $tagFollower->delete();

        return response()->json(null, 204);
    }
}
