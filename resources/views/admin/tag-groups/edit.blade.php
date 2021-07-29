@extends('community::layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            Edit / Tag Group
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('tag-groups.update', $item->id) }}" class="form-horizontal" enctype="multipart/form-data">
                <input type="hidden" name="_method" value="put" />
                <input type="hidden" name="id" value="{{ $item->id }}" />
                @csrf
                @include('community::admin.tag-groups.fields')
            </form>
        </div>
    </div>
@endsection
