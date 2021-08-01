@extends('community::layouts.app')

@section('content')
    test
{{--    {{ \Jawabapp\Community\Models\Post::first()->id }}--}}
    {{ app('community')->getAccountClass() }}
@endsection
