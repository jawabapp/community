<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Community\SearchRequest;
use Jawabapp\Community\Models\Account;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Models\Tag;
use Illuminate\Http\Request;

/**
 * @group Community management
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

    public function index(SearchRequest $request, $type = null)
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
        return $this->index($request, 'posts');
    }

    public function accounts(SearchRequest $request)
    {
        return $this->index($request, 'accounts');
    }

    public function tags(SearchRequest $request)
    {
        return $this->index($request, 'tags');
    }

    private function getKeyword($query) {
        $keyword = str_replace(['-', '_', '&', '"', '\'', ',', ';', '^', '!', '\\', '/', '(', ')', '[', ']', '%', '$', '#', '@', '~', '+', '*', '|'], ' ', trim($query));
        $keyword = preg_replace('/\s+/', ' ', $keyword);
        return trim($keyword);
    }

    private function post(SearchRequest $request)
    {

        $keyword = $this->getKeyword($request->get('query'));
        $keywords = '+' . preg_replace('#[\s]+#i', '+', $keyword);

        return Post::whereRaw("MATCH (`content`) AGAINST(? IN BOOLEAN MODE)", $keywords)
            ->whereClassType(Post\Text::class)
            ->whereNull('related_post_id')
            ->whereNull('parent_post_id')
            ->orderBy('children_count', 'desc')
            ->with(Post::withPost())
            ->paginate(config('community.per_page', 10));
    }

    private function account(SearchRequest $request)
    {

        $q = CommunityFacade::getUserClass()::query();

        $keyword = $this->getKeyword($request->get('query'));

        if(config('community.search_fields') && $keyword) {
            $keywords = preg_replace('#[\s]+#i', '%', $keyword);
            foreach (config('community.search_fields') as $column) {
                $q->orWhere($column, 'LIKE', "%{$keywords}%");
            }
        }

//        if(config('community.search_fields')) {
//            $search_fields = "`" .implode("`, ' ',`", config('community.search_fields')) . "`";
//            $q->orWhereRaw("CONCAT({$search_fields}) LIKE '%{$keyword}%'");
//        }

        return $q->paginate(config('community.per_page', 10));
    }

    private function hashTag(SearchRequest $request)
    {

        $keyword = $this->getKeyword($request->get('query'));

        return Tag::selectRaw('`tag_id`, `hash_tag`, count(*) as `hash_tag_count`')
            ->join('post_tags', 'tag_id', '=', 'tags.id')
            ->where('hash_tag', 'LIKE', "%{$keyword}%")
            ->where('posts_count', '>', 0)
            ->groupBy('tag_id')
            ->orderBy('hash_tag_count', 'desc')
            ->paginate(config('community.per_page', 10));
    }
}
