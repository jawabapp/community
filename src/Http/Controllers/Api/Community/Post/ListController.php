<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

use Illuminate\Support\Facades\Cache;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Models\Account;
use Jawabapp\Community\Models\PostInteraction;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Community\Post\ListRequest;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class ListController extends Controller
{
    public function __construct()
    {
        if (request()->server('HTTP_AUTHORIZATION')) {
            $this->middleware('auth:api');
        } else {
            $this->middleware('guest');
        }
    }

    public function index(ListRequest $request)
    {

        //\DB::enableQueryLog();

        $page = intval($request->get('page'));
        $accountId = intval($request->get('account_id'));
        $parentPostId = intval($request->get('parent_post_id'));

        $cacheKey = "{$page}_{$accountId}_{$parentPostId}";

        if($activeAccountId = intval(CommunityFacade::getUserClass()::getActiveAccountId())) {
            $cacheKey .= "_{$activeAccountId}";
        }

        if (Cache::tags(['posts'])->has($cacheKey)) {
            $data = Cache::tags(['posts'])->get($cacheKey);
        } else {

            $query = Post::query()->with(Post::withPost());

            if (empty($accountId) && empty($parentPostId)) {
                // Get user's filtered home data
                Post::getUserTimelineFilter($query);
                $query->orderBy('weight', 'desc');
            } else {

                $query->whereNull('related_post_id');

                if ($accountId) {
                    $query->whereAccountId($accountId);
                }

                if ($parentPostId) {
                    $query->whereParentPostId($parentPostId);
                    $query->oldest();
                } else {
                    $query->whereNull('parent_post_id');
                    $query->orderBy('weight', 'desc');
                }

                if ($parentPostId) {
                    PostInteraction::assignInteractionToAccount('viewed', $parentPostId);
                }
            }

            if ($parentPostId || $accountId) {
                $data = $query->paginate(config('community.per_page', 10));
            } else {
                $data = $this->simplePaginate($query);
            }

            Cache::tags(['posts'])->put($cacheKey, $data, 10); // 10 minutes
        }

        //dd(\DB::getQueryLog());

        return response()->json($data);

    }

    private function simplePaginate($query) {

        $per_page = config('community.per_page', 10);

        $data = $query->simplePaginate($per_page);
        $data = $data->toArray();

        $last_page = empty($data['next_page_url']) ? $data['current_page'] : ($data['current_page'] + 1);

        $data['total'] = $per_page * $last_page;
        $data['last_page'] = $last_page;
        $data['last_page_url'] = url("/api/community/post/list?page={$last_page}");

        return $data;
    }
}
