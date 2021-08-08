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
     * target-audience
     *
     * @param Request $request
     * @return User|\Illuminate\Support\Collection|int
     */
    public function targetAudience(Request $request) {

        $target = $request->get('target');

        $apps = $target['app'] ?? [];
        $phone = $target['phone'] ?? '';

        if($apps || $phone) {
            return config('community.user_class')::getTargetAudience($target, true);
        }

        return 0;
    }

    /**
     * countries
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function countries(Request $request) {

        $mobile_os = $request->get('os');

        return config('community.user_class')::select(['phone_country'])->distinct()->whereNotNull('phone_country')->where('mobile_os', $mobile_os)->get()->map(function ($item) {
            return [
                'value' => $item->phone_country,
                'text' => $item->phone_country,
            ];
        });
    }

    /**
     * languages
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function languages(Request $request) {

        $mobile_os = $request->get('os');

        return config('community.user_class')::select(['language'])->distinct()->whereNotNull('language')->where('mobile_os', $mobile_os)->get()->map(function ($item) {
            return [
                'value' => $item->language,
                'text' => $item->language,
            ];
        });
    }

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

    /**
     * services
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function services(Request $request) {

        return Service::all()->map(function ($item) {
            return [
                'value' => $item->id,
                'text' => $item->name,
            ];
        });

//        $mobile_os = $request->get('os');
//
//        return Service::select('services.*')->distinct()
//            ->join('service_users', 'service_users.service_id', '=', 'services.id')
//            ->join('users', 'service_users.user_id', '=', 'users.id')
//            ->where('users.mobile_os', $mobile_os)
//            ->get()->map(function ($item) {
//                return [
//                    'value' => $item->id,
//                    'text' => $item->name,
//                ];
//            });
    }

    /**
     * registers
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function registers(Request $request) {

        // $mobile_os = $request->get('os');

        $latestUser = config('community.user_class')::latest()->first();

        $data = collect();
        for($i = 0; $i < 24; $i++) {

            $value = $latestUser->created_at->subMonths($i)->format('Y-m');

            $data->push([
                'value' => $value,
                'text' => $value,
            ]);
        }

        return $data;
    }

    /**
     * parse
     *
     * @param Request $request
     * @return array
     */
    public function parse(Request $request) {

        $parse_url = parse_url($request->get('url'));

        if($parse_url['host'] === 'trends.jawab.app') {
            preg_match('/\/(.*)\/(post)\/(.*)/', $parse_url['path'], $matches);
            if($matches) {
                $item = Post::where('hash', $matches[3])->first();
                if($item) {
                    return [
                        'type' => 'post',
                        'id' => $item->id,
                        'deep_link' => $item->deep_link
                    ];
                }
            }

            preg_match('/\/(hashtag)\/(.*)/', $parse_url['path'], $matches);
            if($matches) {
                $item = Tag::where('hash_tag', "#{$matches[2]}")->first();
                if($item) {
                    return [
                        'type' => 'tag',
                        'id' => $item->id,
                        'deep_link' => $item->deep_link
                    ];
                }
            }

            preg_match('/\/(.*)/', $parse_url['path'], $matches);
            if($matches) {
                $item = Account::where('slug', "@{$matches[1]}")->first();
                if($item) {
                    return [
                        'type' => 'account',
                        'id' => $item->id,
                        'deep_link' => $item->deep_link
                    ];
                }
            }
        }

        return [
            'type' => 'invalid',
            'id' => null,
            'deep_link' => null
        ];
    }

    public function searchTags(Request $request) {

        $limit = 20;

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

    public function searchServices(Request $request) {

        $limit = 20;

        $searchQuery = $request->get('query');

        $query = Service::orderBy('id', 'desc');

        if($searchQuery) {
            $query->where('name', 'LIKE', "%{$searchQuery}%");
        }

        $data = $query->paginate($limit);

//        $data->getCollection()->transform(function ($item) {
//            $item->id = $item->id;
//            $item->name = $item->name;
//            return $item;
//        });

        return $data;
    }

    public function selectedServices(Request $request) {

        $items = $request->get('items');

        return [
            'data' => Service::whereIn('id', $items)->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            })
        ];
    }

    public function image(Request $request) {

        $this->validate($request, [
            'image' => 'required|image|mimetypes:' . config('mimetypes.image') . '|max:' . (env('MAX_FILE_SIZE_IMAGE') * 1024)
        ]);

        $path = 'images/' . date('Y/m/d');

        $src = $request->file('image');

        $image = Image::make($src);

        $original = $src->store($path);

//        ImagePlugin::resize($src, $path, 100, 100, $original);
//        ImagePlugin::resize($src, $path, 800, 800, $original);

        return response()->json([
            'result' => [
                'image_url' => Storage::url($original),
                'height' => $image->getHeight(),
                'width' => $image->getWidth(),
            ]
        ]);
    }
}
