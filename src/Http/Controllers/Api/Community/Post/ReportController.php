<?php


namespace App\Http\Controllers\Api\Community\Post;


use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Post\ReportRequest;
use App\Models\Post;
use App\Models\PostReport;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * @group  Community management
 *
 * APIs for managing community
 */
class ReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index() {
        return response()->json(collect(PostReport::REPORT_TYPES)->map(function ($name, $id){
            return [
                'id' => $id,
                'name' => $name
            ];
        })->values());
    }

    public function report($id, ReportRequest $request) {

        /** @var \App\Models\User $user */
        $user = $request->user();

//        if($user->is_anonymous) {
//            throw ValidationException::withMessages([
//                'id' => [trans('User is anonymous')],
//            ]);
//        }

        if(Carbon::parse($user->block_until)->isFuture()) {
            throw ValidationException::withMessages([
                'account_id' => [trans('Account is blocked until') . ' ' . $user->block_until],
            ]);
        }

        $post = Post::find($id);

        if (!$post) {
            throw ValidationException::withMessages([
                'id' => [trans('The post is not valid!')],
            ]);
        }

        $account = $user->getAccount($request->get('account_id'));

        if(!$account) {
            throw ValidationException::withMessages([
                'id' => [trans("You don't have permission to do any Interaction with this post!")],
            ]);
        }

        if($post->account_id === $request->get('account_id')) {
            throw ValidationException::withMessages([
                'id' => [trans("You don't have permission to report your post!")],
            ]);
        }

        $postReport = PostReport::wherePostId($post->id)
            ->whereAccountId($request->get('account_id'))
            ->first();

        if($postReport) {
            $postReport->update([
                'report' => $request->get('report')
            ]);
        } else {
            PostReport::create([
                'post_id' => $post->id,
                'account_id' => $request->get('account_id'),
                'report' => $request->get('report')
            ]);
        }

        return response()->json([
            'status' => 'OK'
        ]);
    }

}