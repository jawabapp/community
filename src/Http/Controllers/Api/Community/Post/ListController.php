<?php
namespace App\Http\Controllers\Api\Community\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Post\ListRequest;
use App\Models\Account;
use App\Models\Post;
use App\Models\PostInteraction;
use App\Services\Caching;

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
        $activeAccountId = intval(Account::getActiveAccountId());

        $ttl = now()->addDay();

        $cacheKey = "{$page}_{$accountId}_{$parentPostId}_{$activeAccountId}";

        $cacheTags = ['posts'];
        if($activeAccountId) {
            $cacheTags[] = "posts-{$activeAccountId}";
        }

        $data = Caching::doCache($cacheTags, $cacheKey, function () use ($accountId, $parentPostId) {

            $query = Post::whereNull('related_post_id')
                ->with(['related', 'account']);

            if (empty($accountId) && empty($parentPostId)) {
                // Get user's filtered home data
                Post::getUserFilteredData($query);
            }

            if ($parentPostId) {
                $query->whereParentPostId($parentPostId);
                //$query->orderBy('children_count', 'desc');
                $query->oldest();
            } else {
                $query->whereNull('parent_post_id');
                $query->latest();
            }

            if ($accountId) {
                $query->whereAccountId($accountId);
            }

            if($parentPostId) {
                PostInteraction::assignInteractionToAccount('viewed', $parentPostId);
            }

            return $query->paginate(20);

        }, $ttl);

        return response()->json($data);
    }
}
