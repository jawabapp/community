<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\HashTag\Follow;

use Illuminate\Http\JsonResponse;
use Jawabapp\Community\Models\Tag;
use Jawabapp\Community\Models\TagFollower;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Community\Tag\Follow\FollowRequest;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class FollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index($accountId, FollowRequest $request): JsonResponse
    {
        $user = config('community.user_class')::getDefaultAccount();

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

        if (TagFollower::whereAccountId($owner_account->id)->whereTagId($tag->id)->first()) {
            throw ValidationException::withMessages([
                'hash_tag' => [trans('hash tag is already in your follower')],
            ]);
        }

        TagFollower::create([
            'account_id' => $owner_account->id,
            'tag_id' => $tag->id,
            'created_by' => 'follow_api'
        ]);

        return response()->json([
            'result' => 'OK'
        ]);
    }
}
