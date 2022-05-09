<?php
/**
 * @var $data Illuminate\Pagination\LengthAwarePaginator
 * @var $item \Jawabapp\Community\Models\Post
 */
?>
@extends('community::layouts.app')

@section('content')

    <div class="card mb-3">
        <div class="card-body">
            <form>
                <div class="form-group">
                    <label for="slug">User Slug</label>
                    <input type="text" class="form-control mb-2" name="slug" value="{{ request('slug') }}" placeholder="@Account">
                </div>

                <div class="form-group">
                    <label for="hash">Post Hash</label>
                    <input type="text" class="form-control" id="hash" name="hash" value="{{ request('hash') }}">
                </div>

                <div class="form-group">
                    <label for="hash_tag">Hash tag</label>
                    <input type="text" class="form-control" id="hash_tag" name="hash_tag" value="{{ request('hash_tag') }}">
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-row align-items-center">
                            <div class="col-auto my-1">
                                <label class="mr-sm-2 sr-only" for="report">Reports</label>
                                <select class="custom-select mr-sm-2" name="report" id="report">
                                    <option value="">No Reports ...</option>
                                    @foreach(\Jawabapp\Community\Models\PostReport::REPORT_TYPES as $type => $report)
                                        <option value="{{ $type }}" {{ request('report') == $type ? 'selected' : '' }}>{{ $report }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto my-1">
                                <div class="custom-control custom-checkbox mr-sm-2">
                                    <input type="checkbox" class="custom-control-input" id="allReports" name="all_reports" {{ request('all_reports') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="allReports">All Reports</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col text-right">
                        <a href="{{route('community.posts.index')}}" class="btn btn-default">Cancel</a>
                        <button class="btn btn-info"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request('parent_post_id'))

        <div class="card mb-3">
            <div class="card-body">
                @php($post = Jawabapp\Community\Models\Post::find(request('parent_post_id')))

                <div class="row mb-3">
                    <div class="col-6">{!! $post->draw() !!}</div>
                    <div class="col-6">
                        <ul>
                            <li>created at : <strong>{{ $post->created_at }}</strong></li>
                            @foreach($post->interactions as $interaction => $value)
                                <li>{{ $interaction }} : <strong>{{ $value }}</strong></li>
                            @endforeach
                            @foreach($post->getReports() as $report => $value)
                                <li>{{ Jawabapp\Community\Models\PostReport::REPORT_TYPES[$report] }} : <strong>{{ $value }}</strong></li>
                            @endforeach
                        </ul>
                    </div>

                </div>
                <a href="{{route('community.posts.index', ['parent_post_id' => $post->parent_post_id])}}" class="btn btn-outline-primary">Back</a>
                <a href="{{route('community.posts.create', ['parent_post_id' => request()->parent_post_id])}}" class="btn btn-primary">Add New Comment</a>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($data->items())
                <table class="table table-striped table-bordered">
                    <tbody>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Content</th>
                        <th scope="col">Interactions</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                    @foreach($data->items() as $item)
                    <tr>
                        <th scope="row">{{$item->id}}</th>
                        <td>{!! $item->draw() !!}</td>
                        <td>
                            <ul>
                                <li>created at : <strong>{{ $item->created_at }}</strong></li>
                                <li>owner : <strong>{{ $item->account['slug'] ?? '' }}</strong></li>
                                <li>deep-link : <strong>{{ $item->deep_link }}</strong></li>
                                <li>hash : <strong>{{ $item->hash }}</strong></li>
                                @foreach($item->interactions as $interaction => $value)
                                    <li>{{ $interaction }} : <strong>{{ $value }}</strong></li>
                                @endforeach
                                @foreach($item->getReports() as $report => $value)
                                    <li>{{ Jawabapp\Community\Models\PostReport::REPORT_TYPES[$report] }} : <strong>{{ $value }}</strong></li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('community.posts.destroy', $item->id) }}" method="POST">
                                <input type="hidden" name="_method" value="DELETE">
                                {{ csrf_field() }}
                                <a href="{{ $item->deep_link }}" target="_blank" class='btn btn-warning btn-sm'><i class="fa fa-eye"></i> View</a>
                                <a href="{!! route('community.posts.edit', [$item->id]) !!}" class='btn btn-primary btn-sm'><i class="fa fa-edit"></i> Edit</a>
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');"><i class="fa fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-danger" role="alert">There are no Data</div>
            @endif
        </div>
        <div class="card-footer">
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
