<?php

namespace Jawabapp\Community\Http\Controllers\Api\Account\Follow;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Http\Requests\Account\Follow\UnFollowRequest;
use Jawabapp\Community\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group  Account management
 *
 * APIs for managing user accounts
 */
class UnFollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index($accountId, UnFollowRequest $request): JsonResponse
    {
        $user = CommunityFacade::getLoggedInUser();

        if($user->is_anonymous) {
            throw ValidationException::withMessages([
                'id' => [trans('User is anonymous')],
            ]);
        }

        $account = $user->getAccount($accountId);

        if(!$account) {
            throw ValidationException::withMessages([
                'id' => [trans('Account id is not valid!')],
            ]);
        }

        $accountFollower = $account->getFollower($request->get('follower_account_id'));

        if(!$accountFollower) {
            throw ValidationException::withMessages([
                'follower_account_id' => [trans('Account id is not valid!')],
            ]);
        }

        $accountFollower->delete();

        $follower_account = CommunityFacade::getUserClass()::find($accountFollower->follower_account_id);
        $follower_account->followCounts();

        $account->followCounts();

        return response()->json(null, 204);
    }
}
