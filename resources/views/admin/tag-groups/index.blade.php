<?php
/**
 * @var $data Illuminate\Pagination\LengthAwarePaginator
 * @var $item \Jawabapp\Community\Models\TagGroup
 */
?>
@extends('community::layouts.app')

@section('content')
    <div class="card">
        <div class="card-header clearfix">
            <span class="card-title">Tag Groups</span>
            <a href="{{ route('tag-groups.create') }}" class="btn btn-sm btn-success pull-right flip"><i class="fa fa-plus"></i> Add</a>
            <a href="{{ route('tag-groups.tags') }}" class="btn btn-sm btn-info pull-right flip">Tags</a>
        </div>

        <div class="card-body pb-0">
            <form>
                <div class="jumbotron m-0 p-4">
                    <input type="text" class="form-control mb-2" name="name" value="{{ request('name') }}" placeholder="Name">
                    <div class="text-right mt-2">
                        <a href="{{route('tag-groups.index')}}" class="btn btn-default">Cancel</a>
                        <button class="btn btn-info"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            @if($data->items())
                <table class="table table-striped table-bordered">
                    <tbody>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Parent</th>
                        <th scope="col">Name</th>
                        <th scope="col">Image</th>
                        <th scope="col">Order</th>
                        <th scope="col">Tags count</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                    @foreach($data->items() as $item)
                    <tr>
                        <th scope="row">{{$item->id}}</th>
                        <td>{{ $item->parent->name[config('app.locale')] ?? '-' }}</td>
                        <td>{{ $item->name[config('app.locale')] ?? '-' }}</td>
                        <td>
                            @if($item->image)
                                <img src="{{ $item->image }}" class="img-thumbnail" style="max-height:100px; max-width: 100px; vertical-align: middle;">
                            @endif
                        </td>
                        <td>{{ $item->order }}</td>
                        <td>{{ $item->tags->count() }}</td>
                        <td class="text-center">
                            <form action="{{ route('tag-groups.destroy', $item->id) }}" method="POST">
                                <input type="hidden" name="_method" value="DELETE">
                                @csrf
                                <a href="{!! route('tag-groups.show', [$item->id]) !!}" class='btn btn-warning btn-sm'><i class="fa fa-eye"></i> View</a>
                                <a href="{!! route('tag-groups.edit', [$item->id]) !!}" class='btn btn-primary btn-sm'><i class="fa fa-edit"></i> Edit</a>
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');"><i class="fa fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    @if($item->children->count())
                        @foreach($item->children as $child)
                            <tr>
                                <th scope="row">{{$child->id}}</th>
                                <td>{{ $child->parent->name[config('app.locale')] ?? '-' }}</td>
                                <td>{{ $child->name[config('app.locale')] ?? '-' }}</td>
                                <td>
                                    @if($child->image)
                                        <img src="{{ $child->image }}" class="img-thumbnail" style="max-height:100px; max-width: 100px; vertical-align: middle;">
                                    @endif
                                </td>
                                <td>{{ $child->order }}</td>
                                <td>{{ $child->tags->count() }}</td>
                                <td class="text-center">
                                    <form action="{{ route('tag-groups.destroy', $child->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="DELETE">
                                        @csrf
                                        <a href="{!! route('tag-groups.show', [$child->id]) !!}" class='btn btn-warning btn-sm'><i class="fa fa-eye"></i> View</a>
                                        <a href="{!! route('tag-groups.edit', [$child->id]) !!}" class='btn btn-primary btn-sm'><i class="fa fa-edit"></i> Edit</a>
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');"><i class="fa fa-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif
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
