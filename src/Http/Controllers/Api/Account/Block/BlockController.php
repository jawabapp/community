<?php

namespace Jawabapp\Community\Http\Controllers\Api\Account\Block;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Account\Block\BlockRequest;

use Illuminate\Validation\ValidationException;

/**
 * @group Account management
 *
 * APIs for managing user accounts
 */
class BlockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index($accountId, BlockRequest $request)
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

        $blockAccount = CommunityFacade::getUserClass()::find($request->get('block_account_id'));

        if(!$blockAccount) {
            throw ValidationException::withMessages([
                'block_account_id' => [trans('Account id is not valid!')],
            ]);
        }

        if($account->id === $blockAccount->id) {
            throw ValidationException::withMessages([
                'block_account_id' => [trans('Account id is not valid!')],
            ]);
        }

        $account->blocks()->firstOrCreate([
            'block_account_id' => $blockAccount->id
        ]);

        return response([
            'status' => 'OK'
        ], 200);
    }
}
