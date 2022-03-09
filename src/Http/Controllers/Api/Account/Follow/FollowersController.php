<?php

namespace Jawabapp\Community\Http\Controllers\Api\Account\Follow;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Http\Requests\Account\Follow\FollowersRequest;
use Jawabapp\Community\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @group  Account management
 *
 * APIs for managing user accounts
 */
class FollowersController extends Controller
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
     * Followers Accounts
     *
     * [Api to get Followers]
     * Note use {api-version:2} you can find the response under 300
     *
     * @urlParam  accountId required The accountId of the user.
     *
     * @responseFile  responses/account/follow/followers.json
     * @responseFile 300 responses/account/follow/followers.v2.json
     */
    public function index($accountId, FollowersRequest $request): JsonResponse
    {

        $account = CommunityFacade::getUserClass()::find($accountId);

        if(!$account) {
            throw ValidationException::withMessages([
                'id' => [trans('Account id is not valid!')],
            ]);
        }

        $query = $account->following()->with('account');

        $data = $query->paginate(20);

        $data->getCollection()->transform(function ($item) {
            $data = $item->toArray();
            if($item->account) {
                $data['follower'] = $item->account->append([
                    'account_is_subscribed',
                    'account_is_followed',
                    'account_is_blocked',
                ]);
            }
            unset($data['account']);
            return $data;
        });

        return response()->json($data);
    }

}
