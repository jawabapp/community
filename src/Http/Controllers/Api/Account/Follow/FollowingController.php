<?php

namespace Jawabapp\Community\Http\Controllers\Api\Account\Follow;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Http\Requests\Account\Follow\FollowingRequest;
use Jawabapp\Community\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group  Account management
 *
 * APIs for managing user accounts
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
     * Following Accounts
     *
     * [Api to get Following]
     * Note use {api-version:2} you can find the response under 300
     *
     * @urlParam  accountId required The accountId of the user.
     *
     * @responseFile  responses/account/follow/following.json
     * @responseFile 300 responses/account/follow/following.v2.json
     */
    public function index($accountId, FollowingRequest $request): JsonResponse
    {

        $account = CommunityFacade::getUserClass()::find($accountId);

        if(!$account) {
            throw ValidationException::withMessages([
                'id' => [trans('Account id is not valid!')],
            ]);
        }

        $query = $account->followers()->with('follower');

        $data = $query->paginate(500);
//        $data = $query->paginate(config('community.per_page', 10));

        $data->getCollection()->transform(function ($item) {
            $data = $item->toArray();
            if($item->follower) {
                $data['account'] = $item->follower->append([
                    'account_is_subscribed',
                    'account_is_followed',
                    'account_is_blocked',
                ]);
            }
            unset($data['follower']);
            return $data;
        });

        return response()->json($data);
    }
}
