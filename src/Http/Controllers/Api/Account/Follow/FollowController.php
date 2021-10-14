<?php

namespace Jawabapp\Community\Http\Controllers\Api\Account\Follow;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Http\Requests\Account\Follow\FollowRequest;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Models\AccountFollower;
// use Jawabapp\Community\Plugins\CommonPlugin;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group  Account management
 *
 * APIs for managing user accounts
 */
class FollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index($accountId, FollowRequest $request): JsonResponse
    {
        $user = CommunityFacade::getLoggedInUser();

        if($user->is_anonymous) {
            throw ValidationException::withMessages([
                'id' => [trans('User is anonymous')],
            ]);
        }

        $owner_account = $user->getAccount($accountId);

        if(!$owner_account) {
            throw ValidationException::withMessages([
                'account_id' => [trans('Account id is not valid!')],
            ]);
        }

        $account = CommunityFacade::getUserClass()::find($request->get('follower_account_id'));

        if (!$account || $account->id == $owner_account->id) {
            throw ValidationException::withMessages([
                'follower_account_id' => [trans('follower id is not valid or your own account!')],
            ]);
        }

        if (AccountFollower::whereAccountId($owner_account->id)->whereFollowerAccountId($account->id)->first()) {
            throw ValidationException::withMessages([
                'friend_account_id' => [trans('Account is already in your contact')],
            ]);
        }

        $owner_account->followers()->create([
            'follower_account_id' => $account->id
        ]);

        $owner_account->followCounts();
        $account->followCounts();

//        CommonPlugin::mqttPublish($account->id,'usr/community/' . $account->user->id, [
//            'type' => 'follow',
//            'content' => trans('notification.profile_follow', ['nickname' => $owner_account->slug], $account->user->language),
//            'deeplink' => $owner_account->deep_link,
//            'account_sender_nickname' => $owner_account->slug,
//            'account_sender_avatar' => $owner_account->avatar['100*100'] ?? '',
//            'account_sender_id' => $owner_account->id
//        ]);

        return response()->json([
            'result' => $owner_account->followers()->with('follower')->get()
        ]);
    }
}
