@extends('community::layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            Add / Static Page
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('posts.store') }}" class="form-horizontal">
                @csrf
                @include('community::admin.posts.fields')
            </form>
        </div>
    </div>
@endsection
