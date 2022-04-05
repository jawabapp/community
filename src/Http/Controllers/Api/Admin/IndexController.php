<?php
/**
 * Created by PhpStorm.
 * User: qanah
 * Date: 9/20/18
 * Time: 1:13 PM
 */

namespace Jawabapp\Community\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Jawabapp\Community\Models\Account;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Models\Service;
use Jawabapp\Community\Models\Tag;
use Jawabapp\Community\Models\TagGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * @group Admin
 */
class IndexController extends Controller
{

    /**
     * tags
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function tags(Request $request) {

        return Tag::latest(100)->get()->map(function ($item) {
            return [
                'value' => $item->id,
                'text' => str_replace('#', '', $item->hash_tag),
            ];
        });

//        $mobile_os = $request->get('os');
//
//        return Tag::select('tags.*')->distinct()
//            ->join('tag_followers', 'tag_followers.tag_id', '=', 'tags.id')
//            ->join('accounts', 'tag_followers.account_id', '=', 'accounts.id')
//            ->join('users', 'users.id', '=', 'accounts.user_id')
//            ->where('users.mobile_os', $mobile_os)
//            ->get()->map(function ($item) {
//                return [
//                    'value' => $item->id,
//                    'text' => str_replace('#', '', $item->hash_tag),
//                ];
//            });
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function tagGroups(Request $request) {

        return TagGroup::all()->map(function ($item) {
            return [
                'value' => $item->id,
                'text' => $item->name[config('app.locale')] ?? '-',
            ];
        });

//        $mobile_os = $request->get('os');
//
//        return TagGroup::select('tag_groups.*')->distinct()
//            ->join('tag_group_followers', 'tag_group_followers.tag_group_id', '=', 'tag_groups.id')
//            ->join('accounts', 'tag_group_followers.account_id', '=', 'accounts.id')
//            ->join('users', 'users.id', '=', 'accounts.user_id')
//            ->where('users.mobile_os', $mobile_os)
//            ->get()->map(function ($item) {
//                return [
//                    'value' => $item->id,
//                    'text' => $item->name[config('app.locale')] ?? '-',
//                ];
//            });
    }

    public function searchTags(Request $request) {

        $limit = config('community.per_page', 10);

        $searchQuery = $request->get('query');

        $query = Tag::selectRaw('`tag_id`, `hash_tag`, count(*) as `hash_tag_count`')
            ->join('post_tags', 'tag_id', '=', 'tags.id')
            ->groupBy('tag_id')
            ->orderBy('hash_tag_count', 'desc');

        if($searchQuery) {
            $query->where('hash_tag', 'LIKE', "%{$searchQuery}%");
        }

        $data = $query->paginate($limit);

        $data->getCollection()->transform(function ($item) {
            $item->id = $item->tag_id;
            $item->name = $item->hash_tag;
            return $item;
        });

        return $data;
    }

    public function selectedTags(Request $request) {

        $items = $request->get('items');

        return [
            'data' => Tag::whereIn('id', $items)->get()->map(function ($item) {
                return [
                  'id' => $item->id,
                  'name' => $item->hash_tag,
                ];
            })
        ];
    }

}
