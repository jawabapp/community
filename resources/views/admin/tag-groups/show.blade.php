<?php
/**
 * @var $item \App\Models\TagGroup
 */
?>
@extends('community::layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">Tag Group #{{ $item->id }}</div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-2">Name</div>
                <div class="col-md-8">
                    {{ $item->name[config('app.locale')] ?? '-' }}
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-2">Image</div>
                <div class="col-md-8">
                    <img src="{{ $item->image }}" class="img-thumbnail" style="max-height:200px; max-width: 200px; vertical-align: middle;">
                </div>
            </div>
        </div>
    </div>
@endsection
