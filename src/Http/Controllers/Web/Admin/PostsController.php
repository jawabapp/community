<?php

namespace Jawabapp\Community\Http\Controllers\Web\Admin;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Models\Account;
use Illuminate\Http\Request;
use Jawabapp\Community\Models\Post;
use Illuminate\Support\Facades\Session;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Post\CreateRequest;
use Jawabapp\Community\Http\Requests\Post\UpdateRequest;

class PostsController extends Controller
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $repository;

    public function __construct(Post $repository)
    {
        $this->middleware(['auth']);

        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->repository->with('account')->select('posts.*')->whereNull('related_post_id');

        if ($request->has('parent_post_id')) {
            $query->whereParentPostId($request->get('parent_post_id'));
            $query->orderBy('children_count', 'desc');
        } else {
            $query->whereNull('parent_post_id');
        }

        if ($request->get('slug')) {
            $account = CommunityFacade::getUserClass()::where('slug', 'like', "%{$request->get('slug')}%")->first();
            if ($account) {
                $query->where('posts.account_id', $account->id);
            }
        }

        $join = false;

        if ($request->has('all_reports')) {
            $join = true;
        }

        if ($request->get('report')) {
            $query->where('post_reports.report', $request->get('report'));

            $join = true;
        }

        if ($join) {
            $query->join('post_reports', 'posts.id', '=', 'post_reports.post_id');
        }

        $data = $query->latest()->paginate(10);

        return view('community::admin.posts.index')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('community::admin.posts.create');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = $this->repository->find($id);

        if (!$item->id) {
            Session::flash('flash_message', ['type' => 'error', 'message' => 'Invalid Resource']);
            return redirect(route('community.posts.index'));
        }

        return view('community::admin.posts.show')->with('item', $item);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = $this->repository->find($id);

        if (!$item->id) {
            Session::flash('flash_message', ['type' => 'error', 'message' => 'Invalid Resource']);
            return redirect(route('community.posts.index'));
        }

        return view('community::admin.posts.edit')->with('item', $item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function destroy($id)
    {
        $item = $this->repository->find($id);

        if (!$item->id) {
            Session::flash('flash_message', ['type' => 'error', 'message' => 'Invalid Resource']);
            return redirect(route('community.posts.index'));
        }

        $item->delete($id);

        Session::flash('flash_message', ['type' => 'warning', 'message' => 'Deleted Successfully']);
        return redirect(route('community.posts.index', ['parent_post_id' => $item->parent_post_id]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function store(CreateRequest $request)
    {
        //        $this->repository->create($request->all());
        //
        //        Session::flash('flash_message', ['type' => 'notice', 'message' => 'Saved Successfully']);
        //        return redirect(route('posts.index'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function update(UpdateRequest $request, $id)
    {
        $item = $this->repository->find($id);

        if (!$item->id) {
            Session::flash('flash_message', ['type' => 'error', 'message' => 'Invalid Resource']);
            return redirect(route('community.posts.index'));
        }

        $item->tags()->sync($request->get('hashtags'));

        Session::flash('flash_message', ['type' => 'notice', 'message' => 'Updated Successfully']);
        return redirect(route('community.posts.index'));
    }
}
