<?php

namespace Jawabapp\Community\Http\Controllers\Api\Account;


use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Http\Controllers\Controller;

/**
 * @group  Account management
 *
 * APIs for managing user accounts
 */
class ShowController extends Controller
{
    public function __construct()
    {
        if (request()->server('HTTP_AUTHORIZATION')) {
            $this->middleware('auth:api');
        } else {
            $this->middleware('guest');
        }
    }

    public function index($slug, Request $request) {

        $slug = str_replace('@', '', $slug);
        $account = CommunityFacade::getUserClass()::where('slug', "@{$slug}")->first();

        if (!$account) {
            throw ValidationException::withMessages([
                'slug' => [trans('The account is not valid!')],
            ]);
        }

        return response()->json([
            'result' => $account->setAppends([
                'account_is_subscribed',
                'account_is_followed',
                'account_is_blocked',
                'account_is_liked',
            ])
        ]);

    }
}
