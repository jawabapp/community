<?php


namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;


use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Models\PostInteraction;
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

    public function index($id)
    {

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
