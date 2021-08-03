<?php
/**
 * @var $item \Jawabapp\Community\Models\StaticPage
 */
?>
@extends('community::layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">Country #{{ $item->id }}</div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-2">Name</div>
                <div class="col-md-8">
                    {{ $item->name  }}
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">Slug</div>
                <div class="col-md-8">
                    {{ $item->slug }}
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">Language</div>
                <div class="col-md-8">
                    {{ config('app.locales')[$item->language_code] ?? '' }}
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">HTML</div>
                <div class="col-md-8">
                    <code class="bg-light">
                        {{ $item->html }}
                    </code>
                </div>
            </div>
        </div>
    </div>
@endsection
