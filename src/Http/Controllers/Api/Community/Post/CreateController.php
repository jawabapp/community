<?php

namespace Jawabapp\Community\Http\Controllers\Api\Community\Post;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Jawabapp\Community\Models\Post;
use Jawabapp\Community\Plugins\CommonPlugin;
use Illuminate\Validation\ValidationException;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Post\CreateRequest;

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
        $post = CommunityFacade::createPost($request);

        return response()->json([
            'result' => $post
        ]);
    }
}
