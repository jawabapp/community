<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

//use Jawabapp\Community\Http\Resources\Api\PostResource;
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

            $query = Post::whereNull('related_post_id')->with(Post::withPost());

            if (empty($accountId) && empty($parentPostId)) {
                // Get user's filtered home data
                //Post::getUserFilteredData($query);
            }

            if ($parentPostId) {
                $query->whereParentPostId($parentPostId);
                //$query->orderBy('children_count', 'desc');
                $query->oldest();
            } else {
                $query->whereNull('parent_post_id');
                $query->orderBy('weight', 'desc');
                $query->latest();
            }

            if ($accountId) {
                $query->whereAccountId($accountId);
            }

            if ($parentPostId) {
                PostInteraction::assignInteractionToAccount('viewed', $parentPostId);
            }

            //\DB::enableQueryLog();
            $data = $query->paginate(config('community.per_page', 10));
            //dd(\DB::getQueryLog());

            Cache::tags(['posts'])->put($cacheKey, $data, 600); // 60 * 10 = 600 seconds
        }

        return response()->json($data);

    }
}
