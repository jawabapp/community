<?php

namespace JawabApp\Community\Http\Controllers\Api\Community;

use JawabApp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Community\SearchRequest;
use App\Models\Account;
use JawabApp\Community\Models\Post;
use JawabApp\Community\Models\Tag;
use Illuminate\Http\Request;

/**
 * @group  Community management
 *
 * APIs for managing community and the notifications
 */
class SearchController extends Controller
{
    public function __construct()
    {
        if (request()->server('HTTP_AUTHORIZATION')) {
            $this->middleware('auth:api');
        } else {
            $this->middleware('guest');
        }
    }

    public function index(SearchRequest $request)
    {

        $query = trim($request->get('query'));

        $keyword = str_replace(['-', '_', '&', '"', '\'', ',', ';', '^', '!', '\\', '/', '(', ')', '[', ']', '%', '$', '#', '@', '~', '+', '*', '|'], ' ', $query);
        $keyword = preg_replace('/\s+/', ' ', $keyword);
        $keyword = trim($keyword);

        $keywords = '+' . preg_replace('#[\s]+#i', '+', $keyword);

        $limit = 10;

        $posts = Post::whereRaw("MATCH (`content`) AGAINST(? IN BOOLEAN MODE)", $keywords)
            ->whereClassType(Post\Text::class)
            ->whereNull('related_post_id')
            ->whereNull('parent_post_id')
            ->orderBy('children_count', 'desc')
            ->limit($limit)
            ->with(['related', 'account'])
            ->get();

        $accounts = Account::where('slug', 'LIKE', "%{$query}%")
            ->orWhere('nickname', 'LIKE', "%{$keyword}%")
            ->orWhere('status', 'LIKE', "%{$keyword}%")
            ->limit($limit)
            ->get();

        $tags = Tag::selectRaw('`tag_id`, `hash_tag`, count(*) as `hash_tag_count`')
            ->join('post_tags', 'tag_id', '=', 'tags.id')
            ->where('hash_tag', 'LIKE', "%{$query}%")
            ->groupBy('tag_id')
            ->orderBy('hash_tag_count', 'desc')
            ->limit($limit)
            ->get();

        //        $tags = Tag::where('hash_tag', 'LIKE', "%{$query}%")
        //            ->limit($limit)
        //            ->get();

        return response()->json([
            'posts' => $posts,
            'accounts' => $accounts,
            'tags' => $tags,
        ]);
    }

    public function v2(SearchRequest $request, $type = null)
    {

        if (is_null($type)) {
            $posts = $this->post($request);
            $accounts = $this->account($request);
            $hashTags = $this->hashTag($request);

            $collect = collect([
                [
                    'type' => 'posts',
                    'last_page' => $posts->lastPage(),
                ],
                [
                    'type' => 'accounts',
                    'last_page' => $accounts->lastPage(),
                ],
                [
                    'type' => 'hashTags',
                    'last_page' => $hashTags->lastPage(),
                ]
            ]);

            $max = $collect->where('last_page', $collect->max('last_page'))->first();

            switch ($max['type']) {
                case 'posts':
                    $paginator = $posts;
                    break;
                case 'accounts':
                    $paginator = $accounts;
                    break;
                case 'hashTags':
                    $paginator = $hashTags;
                    break;
            }

            $data = [
                'posts' => $posts->items(),
                'accounts' => $accounts->items(),
                'tags' => $hashTags->items(),
            ];
        } else {
            switch ($type) {
                case 'posts':
                    $paginator = $this->post($request);
                    break;
                case 'accounts':
                    $paginator = $this->account($request);
                    break;
                case 'tags':
                    $paginator = $this->hashTag($request);
                    break;
            }

            $data = [
                $type => $paginator->items(),
            ];
        }

        return response()->json([
            "current_page" => $paginator->currentPage(),
            'data' => $data,
            "from" => $paginator->lastItem(),
            "last_page" => $paginator->lastPage(),
            "per_page" => $paginator->perPage(),
            "to" => $paginator->firstItem(),
            "total" => $paginator->total()
        ]);
    }

    public function posts(SearchRequest $request)
    {
        return $this->v2($request, 'posts');
    }

    public function accounts(SearchRequest $request)
    {
        return $this->v2($request, 'accounts');
    }

    public function tags(SearchRequest $request)
    {
        return $this->v2($request, 'tags');
    }

    private function post(SearchRequest $request)
    {

        $query = trim($request->get('query'));

        $keyword = str_replace(['-', '_', '&', '"', '\'', ',', ';', '^', '!', '\\', '/', '(', ')', '[', ']', '%', '$', '#', '@', '~', '+', '*', '|'], ' ', $query);
        $keyword = preg_replace('/\s+/', ' ', $keyword);
        $keyword = trim($keyword);

        $keywords = '+' . preg_replace('#[\s]+#i', '+', $keyword);

        return Post::whereRaw("MATCH (`content`) AGAINST(? IN BOOLEAN MODE)", $keywords)
            ->whereClassType(Post\Text::class)
            ->whereNull('related_post_id')
            ->whereNull('parent_post_id')
            ->orderBy('children_count', 'desc')
            ->with(['related', 'account'])
            ->paginate(10);
    }

    private function account(SearchRequest $request)
    {

        $query = trim($request->get('query'));

        $keyword = str_replace(['-', '_', '&', '"', '\'', ',', ';', '^', '!', '\\', '/', '(', ')', '[', ']', '%', '$', '#', '@', '~', '+', '*', '|'], ' ', $query);
        $keyword = preg_replace('/\s+/', ' ', $keyword);
        $keyword = trim($keyword);

        return Account::where('slug', 'LIKE', "%{$query}%")
            ->orWhere('nickname', 'LIKE', "%{$keyword}%")
            ->orWhere('status', 'LIKE', "%{$keyword}%")
            ->paginate(10);
    }

    private function hashTag(SearchRequest $request)
    {

        $query = trim($request->get('query'));

        return Tag::selectRaw('`tag_id`, `hash_tag`, count(*) as `hash_tag_count`')
            ->join('post_tags', 'tag_id', '=', 'tags.id')
            ->where('hash_tag', 'LIKE', "%{$query}%")
            ->groupBy('tag_id')
            ->orderBy('hash_tag_count', 'desc')
            ->paginate(10);
    }
}
