<?php

namespace JawabApp\Community\Http\Controllers\Api\Community\HashTag\Group;

use JawabApp\Community\Http\Controllers\Controller;
use JawabApp\Community\Http\Requests\Community\TagGroup\Follow\FollowRequest;
use JawabApp\Community\Models\TagGroup;
use JawabApp\Community\Models\TagGroupFollower;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class ListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * List Tag Groups
     *
     * [Api to get on boarding Tag Groups]
     *
     * @queryParam  service_id int The service_id is the selected service on deep-link.
     * @queryParam  active_account int The active_account is the selected account on app.
     *
     * @responseFile  responses/community/hash-tag/group/list.json
     */
    public function index(): JsonResponse
    {

        \Log::info('List Tag Groups', request()->all());

        $query = TagGroup::with('children')
            ->whereNull('parent_id')
            ->oldest('order');

        return response()->json([
            'result' => [
                'data' => $query->get()
            ]
        ]);
    }

    /**
     * Follow Tag Groups
     *
     * [Api to submit on boarding Tag Groups]
     *
     * @urlParam  accountId required The accountId of the user.
     * @bodyParam  tag_group_ids.* integer required The tag_group_ids that the user selects.
     *
     * @responseFile  responses/community/hash-tag/group/follow.json
     */
    public function follow($accountId, FollowRequest $request)
    {

        \Log::info('Follow Tag Groups', $request->all());

        $user = $request->user();
        /** @var \App\Models\User $user */

        $account = $user->getAccount($accountId);

        if (!$account) {
            throw ValidationException::withMessages([
                'account_id' => [trans('Account id is not valid!')],
            ]);
        }

        $toDeletes = TagGroupFollower::where('account_id', $account->id)->get()->pluck('tag_group_id')
            ->diff($request->get('tag_group_ids'))->all();

        $tagGroups = TagGroup::whereIn('id', $request->get('tag_group_ids'))->get();

        if (empty($tagGroups)) {
            throw ValidationException::withMessages([
                'tag_group_ids' => [trans('The selected tag groups were invalid!')],
            ]);
        }

        foreach ($tagGroups as $tagGroup) {
            if (!TagGroupFollower::where('account_id', $account->id)->where('tag_group_id', $tagGroup->id)->exists()) {
                TagGroupFollower::create([
                    'tag_group_id' => $tagGroup->id,
                    'account_id' => $account->id,
                ]);
            }
        }

        if ($toDeletes) {
            TagGroupFollower::where('account_id', $account->id)->whereIn('tag_group_id', $toDeletes)->delete();
        }

        return response()->json([
            'result' => 'OK'
        ]);
    }
}
