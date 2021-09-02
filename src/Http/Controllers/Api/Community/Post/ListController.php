<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

use Jawabapp\Community\Models\Account;
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
        $accountId = intval($request->get('account_id'));
        $parentPostId = intval($request->get('parent_post_id'));

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

        if ($parentPostId) {
            PostInteraction::assignInteractionToAccount('viewed', $parentPostId);
        }

        return response()->json($query->paginate(20));
    }
}
