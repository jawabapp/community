<?php


namespace Jawabapp\Community\Http\Controllers\Api\Account\Follow;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Http\Requests\Account\Follow\MutualRequest;
use Jawabapp\Community\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group  Account management
 *
 * APIs for managing user accounts
 */
class MutualController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Mutual Followers
     *
     * [Api to get Mutual Followers]
     *
     * @urlParam  accountId required The accountId of the user.
     * @queryParam  follower_account_id required integer The follower_account_id is the account you wont to get mutual.
     *
     * @responseFile  responses/account/follow/mutual.json
     */
    public function index($accountId, MutualRequest $request): JsonResponse
    {
        $user = CommunityFacade::getLoggedInUser();

        if ($user->is_anonymous) {
            throw ValidationException::withMessages([
                'id' => [trans('User is anonymous')],
            ]);
        }

        $account = $user->getAccount($accountId);

        if (!$account) {
            throw ValidationException::withMessages([
                'account_id' => [trans('Account id is not valid!')],
            ]);
        }

        $follower_account = CommunityFacade::getUserClass()::find($request->get('follower_account_id'));

        if (!$follower_account || $follower_account->id == $account->id) {
            throw ValidationException::withMessages([
                'follower_account_id' => [trans('follower id is not valid or your own account!')],
            ]);
        }

        $query = $follower_account->getMutualFollower($account->id)->with(['follower']);

        return response()->json(
            $query->paginate(10)
        );
    }
}
