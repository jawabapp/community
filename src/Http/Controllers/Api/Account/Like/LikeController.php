<?php

namespace Jawabapp\Community\Http\Controllers\Api\Account\Like;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Http\Requests\Account\Like\LikeRequest;
use Jawabapp\Community\Http\Controllers\Controller;
// use Jawabapp\Community\Plugins\CommonPlugin;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\Events\AccountLikeCreate;
use Jawabapp\Community\Models\AccountLike;

/**
 * @group  Account management
 *
 * APIs for managing user accounts
 */
class LikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index($accountId, LikeRequest $request): JsonResponse
    {
        $user = CommunityFacade::getLoggedInUser();

        if (config('community.check_anonymous', true) && $user->is_anonymous) {
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

        $account = CommunityFacade::getUserClass()::find($request->get('liked_account_id'));

        if (!$account || $account->id == $owner_account->id) {
            throw ValidationException::withMessages([
                'follower_account_id' => [trans('like account id is not valid or your own account!')],
            ]);
        }

        //        if (AccountLike::whereAccountId($owner_account->id)->whereLikedAccountId($account->id)->first()) {
        //            throw ValidationException::withMessages([
        //                'liked_account_id' => [trans('Account is already liked')],
        //            ]);
        //        }

        $owner_account->likes()->create([
            'liked_account_id' => $account->id
        ]);

        $owner_account->likeCounts();
        $account->likeCounts();

        event(new AccountLikeCreate([
            'flow' => $request->get('flow'),
            'user_id' => $owner_account->id,
            'deep_link' => $account->deep_link,
            'receiver_user_id' => $account->id,
        ]));

        return response()->json([
            'result' => $owner_account->likes()->with('liked_account')->get()
        ]);
    }
}
