<?php


namespace App\Http\Controllers\Api\Community\Post;


use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostInteraction;
use Illuminate\Validation\ValidationException;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class InteractionListController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index($id) {

        $post = Post::find($id);

        if (!$post) {
            throw ValidationException::withMessages([
                'id' => [trans('The post is not valid!')],
            ]);
        }

        $query = PostInteraction::wherePostId($post->id)
            ->whereIn('type', PostInteraction::SINGLE_TYPES)
            ->with(['account']);

        return response()->json(
            $query->paginate(10)
        );
    }
}
