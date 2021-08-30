<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

use Jawabapp\Community\Models\Account;
use Jawabapp\Community\Services\Caching;
use Jawabapp\Community\Models\Post;
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
        // if (request()->server('HTTP_AUTHORIZATION')) {
        //     $this->middleware('auth:api');
        // } else {
        //     $this->middleware('guest');
        // }
    }

    public function index(ListRequest $request)
    {
        $page = intval($request->get('page'));
        $accountId = intval($request->get('account_id'));
        $parentPostId = intval($request->get('parent_post_id'));
        $activeAccountId = intval(config('community.user_class')::getActiveAccountId());

        $ttl = now()->addDay();

        $cacheKey = "{$page}_{$accountId}_{$parentPostId}_{$activeAccountId}";

        $cacheTags = ['posts'];
        if ($activeAccountId) {
            $cacheTags[] = "posts-{$activeAccountId}";
        }

        $data = Caching::doCache($cacheTags, $cacheKey, function () use ($accountId, $parentPostId) {

            $query = Post::whereNull('related_post_id')
                ->with(['related', 'account']);

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
                $query->latest();
            }

            if ($accountId) {
                $query->whereAccountId($accountId);
            }

            if ($parentPostId) {
                PostInteraction::assignInteractionToAccount('viewed', $parentPostId);
            }

            return $query->paginate(20);
        }, $ttl);

        return response()->json($data);
    }
}
