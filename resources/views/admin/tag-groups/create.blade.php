@extends('community::layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            Add / Tag Group
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('community.tag-groups.store') }}" class="form-horizontal" enctype="multipart/form-data">
                {{ csrf_field() }}
                @include('community::admin.tag-groups.fields')
            </form>
        </div>
    </div>
@endsection
