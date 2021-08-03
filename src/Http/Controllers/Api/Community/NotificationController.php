<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community;

use Jawabapp\Community\Models\Account;
use Illuminate\Http\Request;
use Jawabapp\Community\Models\Tag;
use Jawabapp\Community\Models\Post;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\Http\Controllers\Controller;

/**
 * @group  Community management
 *
 * APIs for managing community and the notifications
 */
class NotificationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Subscribe to a notification
     *
     * [Api to subscribe to a notification]
     * Notification type will be as the following:
     * post: to subscribe the user to get a notification for the post updates (currently un-available),
     * comments addition on post level or a certain comment level.
     *
     * @urlParam  type required the notification type can be one of ['post', 'account', 'hashtag'].
     * @urlParam  id required The object id. post_id, account_id, hashtag_id.
     * @urlParam  account_id required The active account_id of the user.
     *
     * @responseFile  responses/community/notification/subscribe.json
     *
     * @param $type
     * @param $id
     * @param $account_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function subscribe($type, $id, $account_id, Request $request)
    {
        // POST, Account, HashTag
        $user = $request->user();

        $account = $user->getAccount($account_id);

        if (!$account) {
            throw ValidationException::withMessages([
                'account_id' => [trans("Invalid account id")],
            ]);
        }

        switch ($type) {
            case 'post':
                $notifiable = Post::find($id);
                if (!$notifiable) {
                    throw ValidationException::withMessages([
                        'id' => [trans("Invalid {$type} id")],
                    ]);
                }
                $account->subscribePosts()->syncWithoutDetaching($notifiable);
                break;

            case 'account':
                $notifiable = Account::find($id);
                if (!$notifiable) {
                    throw ValidationException::withMessages([
                        'id' => [trans("Invalid {$type} id")],
                    ]);
                }
                $account->subscribeAccounts()->syncWithoutDetaching($notifiable);

                break;

            case 'hashtag':
                $notifiable = Tag::find($id);
                if (!$notifiable) {
                    throw ValidationException::withMessages([
                        'id' => [trans("Invalid {$type} id")],
                    ]);
                }
                $account->subscribeTags()->syncWithoutDetaching($notifiable);
                break;

            default:
                throw ValidationException::withMessages([
                    'type' => [trans("Invalid type")],
                ]);
                break;
        }

        if (!$notifiable->topic) {
            $notifiable->save();
        }

        return response()->json([
            'result' => [
                'topic' => $notifiable->topic
            ]
        ]);
    }

    /**
     * Un-Subscribe to a notification
     *
     * [Api to Un-subscribe a notification]
     * Notification type will be as the following:
     * post: to un-subscribe the user from getting a notification for the post updates (currently un-available),
     * comments addition on post level or a certain comment level.
     *
     * @urlParam  type required the notification type can be one of ['post', 'account', 'hashtag'].
     * @urlParam  id required The object id. post_id, account_id, hashtag_id.
     * @urlParam  account_id required The active account_id of the user.
     *
     * @responseFile  responses/community/notification/unsubscribe.json
     *
     * @param $type
     * @param $id
     * @param $account_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function unSubscribe($type, $id, $account_id, Request $request)
    {
        // POST, Account, HashTag
        $user = $request->user();

        $account = $user->getAccount($account_id);

        if (!$account) {
            throw ValidationException::withMessages([
                'account_id' => [trans("Invalid account id")],
            ]);
        }

        switch ($type) {
            case 'post':
                $notifiable = Post::find($id);
                if (!$notifiable) {
                    throw ValidationException::withMessages([
                        'id' => [trans("Invalid {$type} id")],
                    ]);
                }
                $account->subscribePosts()->detach($notifiable);
                break;

            case 'account':
                $notifiable = Account::find($id);
                if (!$notifiable) {
                    throw ValidationException::withMessages([
                        'id' => [trans("Invalid {$type} id")],
                    ]);
                }
                $account->subscribeAccounts()->detach($notifiable);
                break;

            case 'hashtag':
                $notifiable = Tag::find($id);
                if (!$notifiable) {
                    throw ValidationException::withMessages([
                        'id' => [trans("Invalid {$type} id")],
                    ]);
                }
                $account->subscribeTags()->detach($notifiable);
                break;

            default:
                throw ValidationException::withMessages([
                    'type' => [trans("Invalid type")],
                ]);
                break;
        }

        if (!$notifiable->topic) {
            $notifiable->save();
        }

        return response()->json([
            'result' => [
                'topic' => $notifiable->topic
            ]
        ]);
    }
}
