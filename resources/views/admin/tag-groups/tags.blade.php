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
            <span class="card-title">Tags</span>
            <a href="{{ route('community.tag-groups.index') }}" class="btn btn-sm btn-warning pull-right flip">Tag Groups</a>
        </div>

        <div class="card-body pb-0">
            <form>
                {{ csrf_field() }}
                <div class="jumbotron m-0 p-4">
                    <input type="text" class="form-control mb-2" name="hash_tag" value="{{ request('hash_tag') }}" placeholder="Name">
                    <div class="text-right mt-2">
                        <a href="{{route('community.tag-groups.index')}}" class="btn btn-default">Cancel</a>
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
                        <th scope="col">Hash Tag</th>
                        <th scope="col">Post Count</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                    @foreach($data->items() as $item)
                    <tr>
                        <th scope="row">{{$item->id}}</th>
                        <td>{{ $item->hash_tag }}</td>
                        <td>{{ $item->posts_count }}</td>
                        <td class="text-center">
                            <community-assign-tag-group
                                tag-id="{{$item->id}}"
                                tag-group-id="{{$item->tag_group_id}}"
                                tag-groups="{{$tagGroups}}"
                                lang="{{config('app.locale')}}"
                            ></community-assign-tag-group>
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
