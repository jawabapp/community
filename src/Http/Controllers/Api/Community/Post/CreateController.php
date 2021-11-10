<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Plugins\CommonPlugin;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Post\CreateRequest;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class CreateController extends Controller
{
    private $post = null;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function create(CreateRequest $request): JsonResponse
    {
        $post = CommunityFacade::createPost(new Request([
            'account_id' => $request->get('account_id'),
            'parent_post_id' => $request->get('parent_post_id'),
            'post' => $request->get('post'),
            'attachment_type' => $request->get('attachment_type'),
            'attachments' => $request->attachments,
        ]));

        return response()->json([
//            'result' => PostResource::make($post),
            'result' => $post,
        ]);
    }
}
