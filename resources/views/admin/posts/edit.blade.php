@extends('community::layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            Edit / Static Page
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('community.posts.update', $item->id) }}" class="form-horizontal">
                <input type="hidden" name="_method" value="put" />
                <input type="hidden" name="id" value="{{ $item->id }}" />
                @csrf
                @include('community::admin.posts.fields')
            </form>
        </div>
    </div>
@endsection
