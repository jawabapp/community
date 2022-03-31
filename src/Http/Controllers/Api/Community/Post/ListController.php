<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

//use Jawabapp\Community\Http\Resources\Api\PostResource;
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

        $with = ['related', 'account'];

        $activeAccountId = CommunityFacade::getUserClass()::getActiveAccountId();
        if ($activeAccountId) {
            $with = array_merge($with, [
                'myInteractions',
                'mySubscribes',
                'tags.myFollowers',
                'tags.mySubscribes',
            ]);
        }

        $accountId = intval($request->get('account_id'));
        $parentPostId = intval($request->get('parent_post_id'));

        $query = Post::whereNull('related_post_id')->with($with);

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
            $query->orderBy('weight', 'desc');
            $query->latest();
        }

        if ($accountId) {
            $query->whereAccountId($accountId);
        }

        if ($parentPostId) {
            PostInteraction::assignInteractionToAccount('viewed', $parentPostId);
        }

//        \DB::enableQueryLog();
//        response()->json($query->paginate(20));
//        dd(\DB::getQueryLog());

//        $data = PostResource::collection(
//            $query->paginate(10)
//        )->resource;
//
//        return response()->json($data);

        return response()->json($query->paginate(20));
    }
}
