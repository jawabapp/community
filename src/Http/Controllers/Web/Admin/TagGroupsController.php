<?php


namespace JawabApp\Community\Http\Controllers\Web\Admin;


use JawabApp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\TagGroup\CreateRequest;
use Jawabapp\Community\Http\Requests\TagGroup\UpdateRequest;
use JawabApp\Community\Models\Tag;
use JawabApp\Community\Models\TagGroup;
use App\Plugins\ImagePlugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class TagGroupsController extends Controller
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $repository;

    public function __construct(TagGroup $repository)
    {
        $this->middleware(['auth']);

        TagGroup::$enableGlobalScope = false;

        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = $this->repository->select()->whereNull('parent_id');

        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        $data = $query->oldest('order')->paginate(10);

        return view('admin.tag-groups.index')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.tag-groups.create');
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
            return redirect(route('tag-groups.index'));
        }

        return view('admin.tag-groups.show')->with('item', $item);
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
            return redirect(route('tag-groups.index'));
        }

        return view('admin.tag-groups.edit')->with('item', $item);
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
            return redirect(route('tag-groups.index'));
        }

        $item->delete($id);

        Session::flash('flash_message', ['type' => 'warning', 'message' => 'Deleted Successfully']);
        return redirect(route('tag-groups.index'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function store(CreateRequest $request)
    {
        $item = $this->repository->create($request->all());

        if ($request->hasFile('image_file')) {
            $path = 'tag_groups' . DIRECTORY_SEPARATOR . $item->id;

            $original = $request->file('image_file')->store($path);

            $item->update([
                'image' => Storage::url($original)
            ]);
        }

        Session::flash('flash_message', ['type' => 'notice', 'message' => 'Saved Successfully']);
        return redirect(route('tag-groups.index'));
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
            return redirect(route('tag-groups.index'));
        }

        $item->update($request->all());

        if ($request->hasFile('image_file')) {
            $path = 'tag_groups' . DIRECTORY_SEPARATOR . $item->id;

            ImagePlugin::deleteOldFiles($path, $item->image);

            $original = $request->file('image_file')->store($path);

            $item->update([
                'image' => Storage::url($original)
            ]);
        }

        if (!$request->get('services')) {
            $item->update([
                'services' => null
            ]);
        }

        Session::flash('flash_message', ['type' => 'notice', 'message' => 'Updated Successfully']);
        return redirect(route('tag-groups.index'));
    }

    public function tags()
    {
        $sub = 'SELECT `tag_id`, COUNT(*) AS `post_counts` FROM `post_tags` GROUP BY `tag_id`';

        $query = Tag::selectRaw('tags.*')->joinSub($sub, 'tpc', function ($join) {
            $join->on('tags.id', '=', 'tpc.tag_id');
        })->latest('post_counts');

        if (request('hash_tag')) {
            $query->where('hash_tag', 'like', '%' . request('hash_tag') . '%');
        }

        $data = $query->latest()->paginate(10);

        return view('admin.tag-groups.tags')->with('data', $data)->with('tagGroups', TagGroup::get());
    }

    public function assign(Request $request)
    {
        $tag_id = $request->get('tag_id');
        $tag_group_id = $request->get('tag_group_id');

        $tag = Tag::find($tag_id);

        if ($tag) {
            $tag->tag_group_id = $tag_group_id;
            $tag->save();
        }

        return true;
    }
}
